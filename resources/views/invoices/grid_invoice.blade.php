@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Invoice Grid</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Invoice Grid</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col"></div>
                    <div class="col-auto">
                        <a href="{{ route('invoice/list/page') }}" class="invoices-links">
                            <i class="fa fa-list" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('invoice/grid/page') }}" class="invoices-links active">
                            <i class="fa fa-th" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card invoices-tabs-card border-0">
                <div class="card-body card-body pt-0 pb-0">
                    <div class="invoices-main-tabs border-0 pb-0">
                        <div class="row align-items-center">
                            <div class="col-lg-12 col-md-12">
                                <div class="invoices-settings-btn invoices-settings-btn-one">
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

                @forelse ($invoiceList as $invoice)
                    <div class="col-sm-6 col-lg-4 col-xl-3 d-flex">
                        <div class="card invoices-grid-card w-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <a href="{{ route('invoice/view/page', $invoice) }}" class="invoice-grid-link">{{ $invoice->invoice_number }}</a>
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
                                        <form method="POST" action="{{ route('invoice/delete') }}" onsubmit="return confirm('Delete invoice {{ $invoice->invoice_number }}?');">
                                            @csrf
                                            <input type="hidden" name="invoice_number" value="{{ $invoice->invoice_number }}">
                                            <button type="submit" class="dropdown-item">
                                                <i class="far fa-trash-alt me-2"></i>Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="card-middle">
                                <h2 class="card-middle-avatar">
                                    <img class="avatar avatar-sm me-2 avatar-img rounded-circle" src="{{ $invoice->customer?->avatar_url ?? asset('images/photo_defaults.jpg') }}" alt="">
                                    {{ $invoice->customer_name }}
                                </h2>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <span><i class="far fa-money-bill-alt"></i> Amount</span>
                                        <h6 class="mb-0">${{ number_format($invoice->total_amount, 2) }}</h6>
                                    </div>
                                    <div class="col-auto">
                                        <span><i class="far fa-calendar-alt"></i> Due Date</span>
                                        <h6 class="mb-0">{{ $invoice->due_date->format('d M Y') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="badge {{ $invoice->statusBadgeClass() }}">{{ $invoice->statusLabel() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-center text-muted">No invoices yet.</p>
                    </div>
                @endforelse

                <div class="col-lg-12">
                    {{ $invoiceList->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </div>
@endsection
