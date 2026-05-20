@extends('admin.admin_dashboard')

@section('title', 'Add Event')
@section('page-title', 'Add Event')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.events.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
                    @error('title')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Event Type *</label>
                    <select name="event_type_id" class="form-select" required>
                        <option value="">Select Event Type</option>
                        @foreach($eventTypes as $type)
                        <option value="{{ $type->id }}" {{ old('event_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('event_type_id')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="start_date" class="form-control" required value="{{ old('start_date') }}">
                    @error('start_date')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">End Date *</label>
                    <input type="date" name="end_date" class="form-control" required value="{{ old('end_date') }}">
                    @error('end_date')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Time</label>
                    <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">End Time</label>
                    <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="{{ old('location') }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Price *</label>
                    <input type="number" name="price" class="form-control" required min="0" step="0.01" value="{{ old('price', 0) }}">
                    @error('price')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Capacity</label>
                    <input type="number" name="capacity" class="form-control" min="0" value="{{ old('capacity') }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="SCHEDULED" {{ old('status') == 'SCHEDULED' ? 'selected' : '' }}>Scheduled</option>
                        <option value="ACTIVE" {{ old('status') == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="RESCHEDULED" {{ old('status') == 'RESCHEDULED' ? 'selected' : '' }}>Rescheduled</option>
                        <option value="CANCELLED" {{ old('status') == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                        <option value="COMPLETED" {{ old('status') == 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirmation Deadline</label>
                    <input type="date" name="confirmation_deadline" class="form-control" value="{{ old('confirmation_deadline') }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Minimum Attendees</label>
                    <input type="number" name="min_attendees" class="form-control" min="0" value="{{ old('min_attendees') }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Attendance Type *</label>
                    <select name="attendance_type" class="form-select" required>
                        <option value="MEMBERS_ONLY" {{ old('attendance_type') == 'MEMBERS_ONLY' ? 'selected' : '' }}>Members Only</option>
                        <option value="MEMBERS_WITH_GUESTS" {{ old('attendance_type') == 'MEMBERS_WITH_GUESTS' ? 'selected' : '' }}>Members with Guests</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Max Guests per Member</label>
                    <input type="number" name="max_guests_per_member" class="form-control" min="0" value="{{ old('max_guests_per_member') }}">
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
