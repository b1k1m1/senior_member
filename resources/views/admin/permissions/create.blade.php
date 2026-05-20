@extends('admin.admin_dashboard')

@section('title', 'Add Permission')
@section('page-title', 'Add Permission')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.permissions.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Permission Name *</label>
                    <input type="text" name="name" class="form-control" data-uppercase required value="{{ old('name') }}">
                    <small class="text-muted">Example: members.view, members.create, etc.</small>
                    @error('name')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Permission</button>
            </div>
        </form>
    </div>
</div>
@endsection
