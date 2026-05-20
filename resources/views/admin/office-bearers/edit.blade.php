@extends('admin.admin_dashboard')

@section('title', 'Edit Office Bearer')
@section('page-title', 'Edit Office Bearer')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.office-bearers.update', $officeBearer->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Position *</label>
                    <input type="text" name="position" class="form-control" required value="{{ old('position', $officeBearer->position) }}">
                    @error('position')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $officeBearer->name) }}">
                    @error('name')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $officeBearer->phone) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="{{ old('display_order', $officeBearer->display_order) }}">
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.office-bearers.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
