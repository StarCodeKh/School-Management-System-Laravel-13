<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\Role;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    /** All invoices tab */
    public function invoiceList(Request $request)
    {
        return $this->listing($request, 'all');
    }

    /** Paid invoices tab */
    public function invoicePaid(Request $request)
    {
        return $this->listing($request, 'paid');
    }

    /** Overdue invoices tab */
    public function invoiceOverdue(Request $request)
    {
        return $this->listing($request, 'overdue');
    }

    /** Draft invoices tab */
    public function invoiceDraft(Request $request)
    {
        return $this->listing($request, 'draft');
    }

    /** Recurring invoices tab */
    public function invoiceRecurring(Request $request)
    {
        return $this->listing($request, 'recurring');
    }

    /** Cancelled invoices tab */
    public function invoiceCancelled(Request $request)
    {
        return $this->listing($request, 'cancelled');
    }

    /** invoice grid */
    public function invoiceGrid()
    {
        $invoiceList = Invoice::with('customer')->latest()->paginate(12);

        $tabCounts = Invoice::tabCounts();

        return view('invoices.grid_invoice', compact('invoiceList', 'tabCounts'));
    }

    /** change an invoice's status (mark as sent / paid / cancelled / draft) */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(InvoiceStatus::class)],
        ]);

        $invoice->update($validated);

        return redirect()->back()->with('success', 'Invoice '.$invoice->invoice_number.' marked as '.$invoice->status->label().'.');
    }

    /** invoice add page */
    public function invoiceAdd()
    {
        $users = $this->customers();

        return view('invoices.invoice_add', compact('users'));
    }

    /** save record incoice */
    public function saveRecord(Request $request)
    {
        $validated = $this->validateInvoice($request);

        DB::beginTransaction();
        try {
            $invoice = Invoice::create($this->invoiceAttributes($request, $validated) + [
                'signature_path' => $this->storeSignature($request),
            ]);
            $invoice->items()->createMany($this->itemRows($validated));

            DB::commit();

            return redirect()->back()->with('success', 'Invoice created.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Invoice creation failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', "Couldn't create the invoice. Please try again.");
        }
    }

    /** invoice edit */
    public function invoiceEdit(Invoice $invoice)
    {
        $invoice->load('items');
        $users = $this->customers();

        return view('invoices.invoice_edit', compact('invoice', 'users'));
    }

    /** Update Record */
    public function updateRecord(Request $request)
    {
        $invoice = Invoice::where('invoice_number', $request->invoice_number)->firstOrFail();
        $validated = $this->validateInvoice($request);

        DB::beginTransaction();
        try {
            $attributes = $this->invoiceAttributes($request, $validated);

            if ($request->hasFile('signature')) {
                $this->deleteSignature($invoice->signature_path);
                $attributes['signature_path'] = $this->storeSignature($request);
            }

            $invoice->update($attributes);

            // Replace the line items so added and removed rows are both saved
            $invoice->items()->delete();
            $invoice->items()->createMany($this->itemRows($validated));

            DB::commit();

            return redirect()->back()->with('success', 'Invoice updated.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Invoice update failed', ['error' => $e->getMessage(), 'invoice' => $invoice->invoice_number]);

            return redirect()->back()->with('error', "Couldn't update the invoice. Please try again.");
        }
    }

    /** Delete Record */
    public function deleteRecord(Request $request)
    {
        $invoice = Invoice::where('invoice_number', $request->invoice_number)->firstOrFail();

        try {
            $signaturePath = $invoice->signature_path;
            $invoice->delete(); // items are removed by the foreign key cascade
            $this->deleteSignature($signaturePath);

            return redirect()->route('invoice/list/page')->with('success', 'Invoice deleted.');
        } catch (\Exception $e) {
            Log::error('Invoice delete failed', ['error' => $e->getMessage(), 'invoice' => $invoice->invoice_number]);

            return redirect()->back()->with('error', "Couldn't delete the invoice. Please try again.");
        }
    }

    /** invoice view */
    public function invoiceView(Invoice $invoice)
    {
        $invoice->load('items');

        return view('invoices.invoice_view', compact('invoice'));
    }

    /** one shared, filterable list view for every invoice tab */
    private function listing(Request $request, string $tab)
    {
        $filters = $request->validate([
            'customers' => 'nullable|array',
            'customers.*' => 'integer',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:255',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $invoiceList = Invoice::with(['items', 'customer'])
            ->forTab($tab)
            ->when($filters['customers'] ?? null, fn ($q, $ids) => $q->whereIn('customer_id', $ids))
            ->when($filters['categories'] ?? null, fn ($q, $cats) => $q->whereHas('items', fn ($i) => $i->whereIn('category', $cats)))
            ->when($filters['from'] ?? null, fn ($q, $d) => $q->whereDate('invoice_date', '>=', $d))
            ->when($filters['to'] ?? null, fn ($q, $d) => $q->whereDate('invoice_date', '<=', $d))
            ->sortable([
                'invoice' => 'invoice_number',
                'created' => 'invoice_date',
                'customer' => fn ($q, $d) => $q->orderBy(User::select('name')->whereColumn('users.id', 'invoices.customer_id'), $d),
                'amount' => 'total_amount',
                'due_date' => 'due_date',
                'status' => 'status',
                'newest' => 'created_at',
            ], 'newest', 'desc')
            ->paginateFromRequest(15);

        $summary = fn ($query) => ['count' => $query->count(), 'amount' => (float) $query->sum('total_amount')];
        $stats = [
            'all' => $summary(Invoice::query()),
            'paid' => $summary(Invoice::where('status', InvoiceStatus::Paid)),
            'unpaid' => $summary(Invoice::whereIn('status', [InvoiceStatus::Draft, InvoiceStatus::Sent])),
            'cancelled' => $summary(Invoice::where('status', InvoiceStatus::Cancelled)),
        ];

        $customers = $this->customers();
        $categories = InvoiceItem::query()->distinct()->orderBy('category')->pluck('category');
        $tabCounts = Invoice::tabCounts();

        return view('invoices.list_invoices', compact('invoiceList', 'tab', 'stats', 'customers', 'categories', 'filters', 'tabCounts'));
    }

    /** users who can be billed */
    private function customers()
    {
        return User::whereIn('role', [Role::Student, Role::Client])->orderBy('name')->get();
    }

    private function validateInvoice(Request $request): array
    {
        return $request->validate([
            'customer_id' => 'required|exists:users,id',
            'po_number' => 'required|string|max:50',
            'invoice_date' => 'nullable|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'recurring_by_month' => 'nullable|required_if_accepted:is_recurring|string|max:20',
            'recurring_month' => 'nullable|string|max:50',
            'invoice_from' => 'nullable|string|max:1000',
            'invoice_to' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|string|max:255',
            'items.*.category' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'charges' => 'nullable|array',
            'charges.*' => 'numeric|min:0',
            'discounts' => 'nullable|array',
            'discounts.*' => 'numeric|min:0|max:100',
            'round_off' => 'nullable|numeric|between:-1000,1000',
            'account_holder_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:20',
            'account_number' => 'nullable|string|max:50',
            'terms_and_conditions' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
            'signature' => 'nullable|image|max:2048',
            'signatory_name' => 'required|string|max:255',
        ]);
    }

    /**
     * Map validated form input to invoice columns.
     * Totals are calculated here, never taken from the browser.
     */
    private function invoiceAttributes(Request $request, array $validated): array
    {
        $subtotal = collect($this->itemRows($validated))->sum('amount');
        $enableTax = $request->boolean('enable_tax');
        $taxRate = $enableTax ? (float) setting('tax_rate', 0) : 0;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $charges = array_values(array_map('floatval', $validated['charges'] ?? []));
        $discounts = array_values(array_map('floatval', $validated['discounts'] ?? []));
        $discountAmount = round(array_sum(array_map(fn ($percent) => $subtotal * $percent / 100, $discounts)), 2);
        $roundOff = (float) ($validated['round_off'] ?? 0);
        $isRecurring = $request->boolean('is_recurring');

        return [
            'customer_id' => $validated['customer_id'],
            'customer_name' => User::findOrFail($validated['customer_id'])->name,
            'po_number' => $validated['po_number'],
            'invoice_date' => $validated['invoice_date'] ?? now(),
            'due_date' => $validated['due_date'],
            'enable_tax' => $enableTax,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'is_recurring' => $isRecurring,
            'recurring_by_month' => $isRecurring ? ($validated['recurring_by_month'] ?? null) : null,
            'recurring_month' => $isRecurring ? ($validated['recurring_month'] ?? null) : null,
            'invoice_from' => $validated['invoice_from'] ?? null,
            'invoice_to' => $validated['invoice_to'] ?? null,
            'taxable_amount' => $subtotal,
            'round_off' => $roundOff,
            'additional_charges' => $charges,
            'discounts' => $discounts,
            'total_amount' => max(0, round($subtotal + $taxAmount + array_sum($charges) - $discountAmount + $roundOff, 2)),
            'account_holder_name' => $validated['account_holder_name'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'ifsc_code' => $validated['ifsc_code'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'signatory_name' => $validated['signatory_name'],
        ];
    }

    /** invoice_items rows; amount = quantity x price minus the line discount (%) */
    private function itemRows(array $validated): array
    {
        return collect($validated['items'])->map(function (array $row) {
            $gross = (float) $row['quantity'] * (float) $row['price'];
            $discount = (float) ($row['discount'] ?? 0);

            return [
                'item' => $row['item'],
                'category' => $row['category'],
                'quantity' => $row['quantity'],
                'price' => $row['price'],
                'discount' => $discount,
                'amount' => round($gross - $gross * $discount / 100, 2),
            ];
        })->values()->all();
    }

    private function storeSignature(Request $request): ?string
    {
        if (! $request->hasFile('signature')) {
            return null;
        }

        $file = $request->file('signature');
        $filename = uniqid('sign_', true).'.'.$file->extension();
        $file->move(public_path('upload_sign'), $filename);

        return 'upload_sign/'.$filename;
    }

    private function deleteSignature(?string $path): void
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}
