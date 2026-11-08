<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

/** School-wide settings, grouped into tabs: general, invoice, tax and bank. */
class SettingController extends Controller
{
    /** validation rules per settings tab; the keys are also the saved setting keys */
    private const GROUPS = [
        'general' => [
            'school_name' => 'required|string|max:255',
            'school_email' => 'nullable|email|max:255',
            'school_phone' => 'nullable|string|max:50',
            'school_address' => 'nullable|string|max:500',
            'currency_symbol' => 'required|string|max:5',
        ],
        'invoice' => [
            'invoice_due_days' => 'required|integer|min:0|max:365',
            'invoice_terms' => 'nullable|string|max:2000',
            'invoice_notes' => 'nullable|string|max:2000',
        ],
        'tax' => [
            'tax_name' => 'required|string|max:50',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ],
        'library' => [
            'library_loan_days' => 'required|integer|min:1|max:365',
            'library_fine_per_day' => 'required|numeric|min:0|max:1000',
        ],
        'bank' => [
            'bank_account_holder' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_ifsc_code' => 'nullable|string|max:20',
            'bank_account_number' => 'nullable|string|max:50',
        ],
    ];

    /** general settings page */
    public function index()
    {
        return $this->show('general');
    }

    public function invoice()
    {
        return $this->show('invoice');
    }

    public function tax()
    {
        return $this->show('tax');
    }

    public function bank()
    {
        return $this->show('bank');
    }

    public function library()
    {
        return $this->show('library');
    }

    /** save one tab */
    public function update(Request $request, string $group)
    {
        abort_unless(isset(self::GROUPS[$group]), 404);

        Setting::store($request->validate(self::GROUPS[$group]));

        return redirect()->back()->with('success', 'Settings saved successfully!');
    }

    private function show(string $group)
    {
        $settings = Setting::allValues();

        return view('setting.settings', compact('group', 'settings'));
    }
}
