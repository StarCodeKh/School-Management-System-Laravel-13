@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Classes</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Classes</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- add class --}}
            <div class="row">
                <div class="col-sm-12">
                    <div class="card comman-shadow">
                        <div class="card-body">
                            <form action="{{ route('class/save') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="form-title"><span>Add Class</span></h5>
                                    </div>
                                    <div class="col-12 col-sm-3">
                                        <div class="form-group local-forms">
                                            <label>Class Name <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="e.g. 10" value="{{ old('name') }}">
                                            @error('name')
                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-5">
                                        <div class="form-group local-forms">
                                            <label>Sections <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control @error('sections') is-invalid @enderror" name="sections" placeholder="Comma separated, e.g. A, B, C" value="{{ old('sections') }}">
                                            @error('sections')
                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-2">
                                        <div class="form-group local-forms">
                                            <label>Sort Order</label>
                                            <input type="number" min="0" class="form-control" name="sort_order" value="{{ old('sort_order', 0) }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-2">
                                        <div class="student-submit">
                                            <button type="submit" class="btn btn-primary">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- class list; each row is its own update form --}}
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table border-0 star-student table-hover table-center mb-0 table-striped">
                                    <thead class="student-thread">
                                        <tr>
                                            <th>Class</th>
                                            <th>Sections</th>
                                            <th>Sort Order</th>
                                            <th>Students</th>
                                            <th>Subjects</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($classes as $class)
                                            <tr>
                                                <td>
                                                    <input form="class-{{ $class->id }}" type="text" class="form-control" name="name" value="{{ $class->name }}">
                                                </td>
                                                <td>
                                                    <input form="class-{{ $class->id }}" type="text" class="form-control" name="sections" value="{{ $class->sections->pluck('name')->implode(', ') }}">
                                                </td>
                                                <td style="max-width: 110px">
                                                    <input form="class-{{ $class->id }}" type="number" min="0" class="form-control" name="sort_order" value="{{ $class->sort_order }}">
                                                </td>
                                                <td>{{ $class->students_count }}</td>
                                                <td>{{ $class->subjects_count }}</td>
                                                <td class="text-end">
                                                    <div class="actions">
                                                        <form id="class-{{ $class->id }}" action="{{ route('class/update', $class) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm bg-success-light me-2" title="Save">
                                                                <i class="far fa-save"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('class/delete') }}" method="POST" class="d-inline" onsubmit="return confirm('Delete class {{ $class->name }}?');">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $class->id }}">
                                                            <button type="submit" class="btn btn-sm bg-danger-light" title="Delete">
                                                                <i class="far fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No classes yet. Add one above.</td>
                                            </tr>
                                        @endforelse
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
