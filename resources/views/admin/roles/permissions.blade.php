@extends('admin.admin_dashboard')

@section('title', 'Assign Permissions to Roles')
@section('page-title', 'Assign Permissions to Roles')

@section('content')
<div class="card">
    <div class="card-body">
        @if(isset($selectedRole))
        <form action="{{ route('admin.roles.permissions.update', $selectedRole->id) }}" method="POST">
            @csrf
            @method('PUT')
        @endif
            
            <div class="mb-3">
                <label class="form-label">Select Role *</label>
                <select class="form-select" id="roleSelect" onchange="window.location.href='/roles-permissions/' + this.value">
                    <option value="">-- Select Role --</option>
                    @foreach($roles as $r)
                    <option value="{{ $r->id }}" {{ isset($selectedRole) && $selectedRole->id == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>

            @if(isset($selectedRole))
            <div class="mb-3">
                <h5>Permissions for: {{ $selectedRole->name }}</h5>
                <hr>
                <div class="row">
                    @foreach($allPermissions->groupBy(function($p) { return explode('.', $p->name)[0]; }) as $group => $groupPermissions)
                    <div class="col-md-3 mb-3">
                        <h6 class="text-uppercase text-muted">{{ $group }}</h6>
                        @foreach($groupPermissions as $permission)
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" class="form-check-input" id="perm_{{ $permission->id }}" 
                                value="{{ $permission->name }}" 
                                {{ $selectedRole->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">Update Permissions</button>
            </div>
            @else
            <div class="alert alert-info">
                Please select a role to manage its permissions.
            </div>
            @endif
        @if(isset($selectedRole))
        </form>
        @endif
    </div>
</div>
@endsection
