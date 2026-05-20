@extends('admin.admin_dashboard')

@section('title', 'Edit Organization')
@section('page-title', 'Edit Organization')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.organizations.update', $organization->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Organization Name *</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $organization->name) }}">
                    @error('name')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $organization->phone) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $organization->email) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Website</label>
                    <input type="text" name="website" class="form-control" value="{{ old('website', $organization->website) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tax ID</label>
                    <input type="text" name="tax_id" class="form-control" value="{{ old('tax_id', $organization->tax_id) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Registration No</label>
                    <input type="text" name="registration_no" class="form-control" value="{{ old('registration_no', $organization->registration_no) }}">
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $organization->address) }}</textarea>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Founder Name</label>
                    <input type="text" name="founder_name" class="form-control" value="{{ old('founder_name', $organization->founder_name) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Founder Title</label>
                    <input type="text" name="founder_title" class="form-control" value="{{ old('founder_title', $organization->founder_title) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Founder Photo</label>
                    @if($organization->founder_photo)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $organization->founder_photo) }}" alt="Founder" width="80">
                    </div>
                    @endif
                    <input type="file" name="founder_photo" class="form-control" accept="image/*">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Logo</label>
                    @if($organization->logo)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $organization->logo) }}" alt="Logo" width="80">
                    </div>
                    @endif
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Welcome Message</label>
                    <textarea name="welcome_message" class="form-control" rows="3">{{ old('welcome_message', $organization->welcome_message) }}</textarea>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.organizations.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
