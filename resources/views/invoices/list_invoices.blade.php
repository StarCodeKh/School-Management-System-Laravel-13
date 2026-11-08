@extends('layouts.master')
@section('content')
    @php
        $tabs = [
            'all' => ['label' => 'All Invoice', 'route' => 'invoice/list/page'],
            'paid' => ['label' => 'Paid', 'route' => 'invoice/paid/page'],
            'overdue' => ['label' => 'Overdue', 'route' => 'invoice/overdue/page'],
            'draft' => ['label' => 'Draft', 'route' => 'invoice/draft/page'],
            'recurring' => ['label' => 'Recurring', 'route' => 'invoice/recurring/page'],
            'cancelled' => ['label' => 'Cancelled', 'route' => 'invoice/cancelled/page'],
        ];
        $currentRoute = $tabs[$tab]['route'];
        $selectedCustomers = array_map('intval', $filters['customers'] ?? []);
        $selectedCategories = $filters['categories'] ?? [];
        $dateRanges = [
            'Today' => [today(), today()],
            'Yesterday' => [today()->subDay(), today()->subDay()],
            'Last 7 days' => [today()->subDays(6), today()],
            'This month' => [today()->startOfMonth(), today()],
            'Last month' => [today()->subMonthNoOverflow()->startOfMonth(), today()->subMonthNoOverflow()->endOfMonth()],
        ];
        $cards = [
            ['key' => 'all', 'label' => 'All Invoices', 'icon' => 'invoices-icon1.svg'],
            ['key' => 'paid', 'label' => 'Paid Invoices', 'icon' => 'invoices-icon2.svg'],
            ['key' => 'unpaid', 'label' => 'Unpaid Invoices', 'icon' => 'invoices-icon3.svg'],
            ['key' => 'cancelled', 'label' => 'Cancelled Invoices', 'icon' => 'invoices-icon4.svg'],
        ];
    @endphp

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Invoices</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            @if ($tab === 'all')
                                <li class="breadcrumb-item active">Invoices</li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ route('invoice/list/page') }}">Invoices</a></li>
                                <li class="breadcrumb-item active">{{ $tabs[$tab]['label'] }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col"></div>
                    <div class="col-auto">
                        <a href="{{ route('invoice/list/page') }}" class="invoices-links active">
                            <i class="fa fa-list" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('invoice/grid/page') }}" class="invoices-links">
                            <i class="fa fa-th" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Filters: one GET form so every filter is kept when another one is applied --}}
            <div class="card report-card">
                <div class="card-body pb-0">
                    <div class="row">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route($currentRoute) }}">
                                <ul class="app-listing">
                                    <li>
                                        <div class="multipleSelection">
                                            <div class="selectBox">
                                                <p class="mb-0"><i class="fas fa-user-plus me-1 select-icon"></i> Select User</p>
                                                <span class="down-icon"><i class="fas fa-chevron-down"></i></span>
                                            </div>
                                            <div id="checkBoxes">
                                                <p class="checkbox-title">Customer Search</p>
                                                <div class="selectBox-cont">
                                                    @forelse ($customers as $customer)
                                                        <label class="custom_check w-100">
                                                            <input type="checkbox" name="customers[]" value="{{ $customer->id }}" @checked(in_array($customer->id, $selectedCustomers, true))>
                                                            <span class="checkmark"></span> {{ $customer->name }}
                                                        </label>
                                                    @empty
                                                        <p class="text-muted mb-0">No customers yet.</p>
                                                    @endforelse
                                                </div>
                                                <button type="submit" class="btn w-100 btn-primary">Apply</button>
                                                <a href="{{ route($currentRoute) }}" class="btn w-100 btn-grey">Reset</a>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="multipleSelection">
                                            <div class="selectBox">
                                                <p class="mb-0"><i class="fas fa-calendar me-1 select-icon"></i> Select Date</p>
                                                <span class="down-icon"><i class="fas fa-chevron-down"></i></span>
                                            </div>
                                            <div id="checkBoxes">
                                                <p class="checkbox-title">Date Filter</p>
                                                <div class="selectBox-cont selectBox-cont-one h-auto">
                                                    <div class="date-picker">
                                                        <div class="form-custom cal-icon">
                                                            <input class="form-control datetimepicker" type="text" name="from" value="{{ $filters['from'] ?? '' }}" placeholder="From">
                                                        </div>
                                                    </div>
                                                    <div class="date-picker pe-0">
                                                        <div class="form-custom cal-icon">
                                                            <input class="form-control datetimepicker" type="text" name="to" value="{{ $filters['to'] ?? '' }}" placeholder="To">
                                                        </div>
                                                    </div>
                                                    <div class="date-list">
                                                        <ul>
                                                            @foreach ($dateRanges as $label => [$from, $to])
                                                                <li>
                                                                    <a href="{{ route($currentRoute, array_merge($filters, ['from' => $from->format('d-m-Y'), 'to' => $to->format('d-m-Y')])) }}" class="btn date-btn">{{ $label }}</a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn w-100 btn-primary">Apply</button>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="multipleSelection">
                                            <div class="selectBox">
                                                <p class="mb-0"><i class="fas fa-bookmark me-1 select-icon"></i> By Category</p>
                                                <span class="down-icon"><i class="fas fa-chevron-down"></i></span>
                                            </div>
                                            <div id="checkBoxes">
                                                <p class="checkbox-title">Category</p>
                                                <div class="selectBox-cont">
                                                    @forelse ($categories as $category)
                                                        <label class="custom_check w-100">
                                                            <input type="checkbox" name="categories[]" value="{{ $category }}" @checked(in_array($category, $selectedCategories, true))>
                                                            <span class="checkmark"></span> {{ $category }}
                                                        </label>
                                                    @empty
                                                        <p class="text-muted mb-0">No categories yet.</p>
                                                    @endforelse
                                                </div>
                                                <button type="submit" class="btn w-100 btn-primary">Apply</button>
                                                <a href="{{ route($currentRoute) }}" class="btn w-100 btn-grey">Reset</a>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card invoices-tabs-card border-0">
                <div class="card-body card-body pt-0 pb-0">
                    <div class="invoices-main-tabs">
                        <div class="row align-items-center">
                            <div class="col-lg-8 col-md-8">
                                <div class="invoices-tabs">
                                    <ul>
                                        @foreach ($tabs as $key => $item)
                                            <li><a class="{{ $tab === $key ? 'active' : '' }}" href="{{ route($item['route']) }}">{{ $item['label'] }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="invoices-settings-btn">
                                    <a href="{{ route('invoice/settings/page') }}" class="invoices-settings-icon">
                                        <i class="feather feather-settings"></i>
                                    </a>
                                    <a href="{{ route('invoice/add/page') }}" class="btn">
                                        <i class="feather feather-plus-circle"></i> New Invoice
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                @foreach ($cards as $card)
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card inovices-card">
                            <div class="card-body">
                                <div class="inovices-widget-header">
                                    <span class="inovices-widget-icon">
                                        <img src="{{ asset('assets/img/icons/'.$card['icon']) }}" alt="">
                                    </span>
                                    <div class="inovices-dash-count">
                                        <div class="inovices-amount">${{ number_format($stats[$card['key']]['amount'], 2) }}</div>
                                    </div>
                                </div>
                                <p class="inovices-all">{{ $card['label'] }} <span>{{ $stats[$card['key']]['count'] }}</span></p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-stripped table-hover datatable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Invoice ID</th>
                                            <th>Category</th>
                                            <th>Created on</th>
                                            <th>Invoice to</th>
                                            <th>Amount</th>
                                            <th>Due date</th>
                                            <th>Status</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoiceList as $invoice)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('invoice/view/page', $invoice) }}" class="invoice-link">{{ $invoice->invoice_number }}</a>
                                                </td>
                                                <td>{{ $invoice->items->first()?->category }}</td>
                                                <td>{{ $invoice->created_at->format('d M Y') }}</td>
                                                <td>
                                                    <h2 class="table-avatar">
                                                        <img class="avatar avatar-sm me-2 avatar-img rounded-circle" src="{{ $invoice->customer?->avatar_url ?? asset('images/photo_defaults.jpg') }}" alt="">
                                                        {{ $invoice->customer_name }}
                                                    </h2>
                                                </td>
                                                <td class="text-primary">${{ number_format($invoice->total_amount, 2) }}</td>
                                                <td>{{ $invoice->due_date->format('d M Y') }}</td>
                                                <td>
                                                    <span class="badge {{ $invoice->statusBadgeClass() }}">{{ $invoice->statusLabel() }}</span>
                                                    @if ($invoice->is_recurring)
                                                        <span class="badge bg-info-light">Recurring</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="dropdown dropdown-action">
                                                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item" href="{{ route('invoice/edit/page', $invoice) }}">
                                                                <i class="far fa-edit me-2"></i>Edit
                                                            </a>
                                                            <a class="dropdown-item" href="{{ route('invoice/view/page', $invoice) }}">
                                                                <i class="far fa-eye me-2"></i>View Detail
                                                            </a>
                                                            @foreach ($statuses as $status)
                                                                @continue($invoice->status === $status)
                                                                <form method="POST" action="{{ route('invoice/status', $invoice) }}">
                                                                    @csrf
                                                                    <input type="hidden" name="status" value="{{ $status->value }}">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="far fa-check-circle me-2"></i>Mark as {{ $status->label() }}
                                                                    </button>
                                                                </form>
                                                            @endforeach
                                                            <form method="POST" action="{{ route('invoice/delete') }}" onsubmit="return confirm('Delete invoice {{ $invoice->invoice_number }}?');">
                                                                @csrf
                                                                <input type="hidden" name="invoice_number" value="{{ $invoice->invoice_number }}">
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="far fa-trash-alt me-2"></i>Delete
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
