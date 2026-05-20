@extends('admin.admin_dashboard')

@section('title', 'Edit Membership Type')
@section('page-title', 'Edit Membership Type')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.membership-types.update', $membershipType->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" data-uppercase required value="{{ $membershipType->name }}">
                    @error('name')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fee Amount *</label>
                    <input type="number" name="fee_amount" class="form-control" step="0.01" min="0" required value="{{ $membershipType->fee_amount }}">
                    @error('fee_amount')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ $membershipType->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.membership-types.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
