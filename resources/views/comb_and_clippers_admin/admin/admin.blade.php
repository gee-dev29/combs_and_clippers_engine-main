@php
function safeDecryptRole($encryptedRole) {
    if (is_null($encryptedRole)) {
        return null;
    }
    
    try {
        return json_decode(Crypt::decryptString($encryptedRole), true);
    } catch (\Exception $e) {
        return null;
    }
}
@endphp

@extends('layouts.app')

@section('content')
<div class="cnc:container mx-auto p-6">
    <h4 class="cnc:text-lg cnc:font-semibold cnc:mb-4">Admin Management</h4>

    <!-- Summary Statistics -->
    <div class="cnc:grid cnc:lg:grid-cols-4 cnc:md:grid-cols-3 cnc:gap-4 cnc:mb-6">
        <!-- Total Admins -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-green-50 cnc:text-green-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-User cnc:text-3xl"></i> Total Admins
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">{{ number_format($totalAdmins) }}</h3>
            </div>
        </div>

        <!-- Role Distribution -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-blue-50 cnc:text-blue-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Security-Check cnc:text-3xl"></i> Total Roles
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">{{ number_format($totalRoles) }}</h3>
            </div>
        </div>

        <!-- Admin Types -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-yellow-50 cnc:text-yellow-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Folder-With-Document cnc:text-3xl"></i> Admin Account Types
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">{{ $adminTypes->count() }}</h3>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-red-50 cnc:text-red-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Clock cnc:text-3xl"></i> Recent Logins
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">{{ number_format($recentLogins) }}</h3>
            </div>
        </div>
    </div>

    <!-- Tabs for Admin and Role Management -->
    <ul class="nav nav-tabs mb-4" id="managementTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="admin-tab" data-toggle="tab" href="#admin-management" role="tab">
                <i class="i-User cnc:mr-1"></i> Admin Management
            </a>
        </li>
        @panelcan('roles,view_roles|superAdmin')
        <li class="nav-item">
            <a class="nav-link" id="role-tab" data-toggle="tab" href="#role-management" role="tab">
                <i class="i-Security-Check cnc:mr-1"></i> Role Management
            </a>
        </li>
        @endpanelcan
    </ul>

    <div class="tab-content" id="managementTabsContent">
        <!-- Admin Management Tab -->
        <div class="tab-pane fade show active" id="admin-management" role="tabpanel">
            <!-- Role Distribution Section -->
            <div class="cnc:grid cnc:gap-4 cnc:mb-6">
                <div class="card w-full">
                    <div class="cnc:card-header cnc:bg-purple-50 cnc:text-purple-700 cnc:px-6 cnc:py-4">
                        <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                            <i class="i-Administrator cnc:text-3xl"></i> Role Distribution
                        </h5>
                    </div>
                    <div class="cnc:card-body cnc:px-6 cnc:py-5">
                        <ul class="cnc:space-y-3">
                            @forelse($roleDistribution as $role => $count)
                            <li
                                class="cnc:flex cnc:items-center cnc:justify-between cnc:bg-purple-50 cnc:px-4 cnc:py-2 cnc:rounded-md hover:cnc:bg-purple-200 transition">
                                <div class="cnc:flex cnc:items-center cnc:gap-3">
                                    <i class="i-Right-3 cnc:text-purple-500"></i>
                                    <span class="cnc:font-semibold cnc:text-black">{{ ucwords($role) }}</span>
                                </div>
                                <span class="btn btn-primary">
                                    {{ $count }} admins
                                </span>
                            </li>
                            @empty
                            <li class="cnc:text-gray-500">No roles assigned yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('admin.management') }}" class="cnc:mb-6">
                <div class="cnc:grid cnc:grid-cols-4 cnc:gap-4 cnc:mb-4">
                    <input type="text" name="admin_name" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
                        placeholder="Admin Name" value="{{ request('admin_name') }}">

                    <input type="email" name="admin_email" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
                        placeholder="Admin Email" value="{{ request('admin_email') }}">

                    <select name="account_type" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md">
                        <option value="">Select Account Type</option>
                        @foreach($accountTypes as $type)
                        <option value="{{ $type }}" {{ request('account_type')==$type ? 'selected' : '' }}>
                            {{ ucwords($type) }}
                        </option>
                        @endforeach
                    </select>

                    <select name="role_filter" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md">
                        <option value="">Select Role</option>
                        @foreach($allRoles as $role)
                        <option value="{{ $role }}" {{ request('role_filter')==$role ? 'selected' : '' }}>
                            {{ ucwords($role) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary mt-3 mr-3">
                    Apply Filters
                </button>
                <a href="{{ route('admin.management') }}" class="btn btn-secondary mt-3">Clear Filters</a>
            </form>

            <div class="cnc:flex cnc:justify-end cnc:mb-4">
                @panelcan('roles,create_roles')
                <button data-toggle="modal" data-target="#createRoleModal" class="btn btn-info mr-3">
                    <i class="i-Add cnc:mr-1"></i> Create New Role
                </button>
                @endpanelcan
                <button data-toggle="modal" data-target="#createAdminModal" class="btn btn-success">
                    <i class="i-Add cnc:mr-1"></i> Create New Admin
                </button>
            </div>

            <!-- Admins Table -->
            <div class="cnc:overflow-x-auto cnc:mt-6">
                <table class="display table table-striped table-bordered" id="zero_configuration_table"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Account Type</th>
                            <th>Roles</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                        <tr>
                            <td>{{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>{{ ucwords($admin->accounttype) }}</td>
                            @php
                            $roleData = safeDecryptRole($admin->role);
                            @endphp
                            @if($roleData)
                            <td class="cnc:whitespace-nowrap">
                                <span class="cnc:inline-block cnc:max-w-[140px] group cnc:cursor-pointer">
                                    @php
                                    $mainRoles = array_keys($roleData);
                                    $mainRoleCount = count($mainRoles);
                                    @endphp
                                    {{ $mainRoleCount }} main role(s)
                                    <span class="cnc:text-gray-400">...</span>
                                    <div
                                        class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content cnc:z-10">
                                        @foreach($mainRoles as $role)
                                        <div class="cnc:font-semibold">{{ ucwords(str_replace('_', ' ', $role)) }}</div>
                                        @if(is_array($roleData[$role]))
                                        <ul class="cnc:pl-4 cnc:text-xs">
                                            @foreach(array_keys($roleData[$role]) as $subRole)
                                            <li>- {{ ucwords(str_replace('_', ' ', $subRole)) }}</li>
                                            @endforeach
                                        </ul>
                                        @endif
                                        @endforeach
                                    </div>
                                </span>
                            </td>
                            @else
                            <td class="cnc:whitespace-nowrap">
                                <span class="cnc:text-gray-500">No roles assigned</span>
                            </td>
                            @endif

                            <td>{{ $admin->created_at->format('M d, Y') }}</td>
                            <td class="cnc:flex cnc:gap-2">
                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                    data-target="#editAdminModal-{{$admin->id}}">
                                    <i class="i-Pen-2"></i> Edit
                                </button>

                                <button type="button" class="btn btn-info" data-toggle="modal"
                                    data-target="#manageRolesModal-{{$admin->id}}">
                                    <i class="i-Security-Settings"></i> Roles
                                </button>

                                <form id="delete-admin-form-{{$admin->id}}"
                                    action="{{ route('admin.admin.destroy', $admin->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger admin-delete-btn"
                                        data-id="{{$admin->id}}">
                                        <i class="i-Close-Window"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Admin Modal -->
                        <div class="modal fade" id="editAdminModal-{{$admin->id}}" tabindex="-1" role="dialog"
                            aria-labelledby="editAdminModalTitle-{{$admin->id}}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered cnc:max-w-4xl! cnc:w-full!" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="i-Edit cnc:text-blue-500 mr-2"></i> Edit Admin
                                        </h5>
                                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <form action="{{ route('admin.admin.update', $admin->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="card mb-5">
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>Admin Name</label>
                                                        <input class="form-control" type="text" name="name"
                                                            value="{{ old('name', $admin->name) }}"
                                                            placeholder="Enter Admin Name" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Admin Email</label>
                                                        <input class="form-control" type="email" name="email"
                                                            value="{{ old('email', $admin->email) }}"
                                                            placeholder="Enter Admin Email" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label>New Password (leave blank to keep current)</label>
                                                        <input class="form-control" type="password" name="password"
                                                            placeholder="Enter New Password" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Account Type</label>
                                                        <select class="form-control" name="accounttype">
                                                            @foreach($accountTypes as $type)
                                                            <option value="{{ $type }}" {{ $admin->accounttype == $type
                                                                ?
                                                                'selected' : '' }}>
                                                                {{ ucwords($type) }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <button class="btn btn-primary" type="submit">Update Admin</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" type="button"
                                            data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Manage Roles Modal -->
                        <div class="modal fade" id="manageRolesModal-{{$admin->id}}" tabindex="-1" role="dialog"
                            aria-labelledby="manageRolesModalTitle-{{$admin->id}}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered cnc:max-w-5xl! cnc:w-full!" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="i-Security-Settings cnc:text-blue-500 mr-2"></i> Manage Roles for
                                            {{
                                            $admin->name }}
                                        </h5>
                                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <form action="{{ route('admin.admin.updateRoles', $admin->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="cnc:grid cnc:grid-cols-2 cnc:gap-6">
                                                @php
                                                $roleData = safeDecryptRole($admin->role);
                                                @endphp

                                                <!-- Main Roles -->
                                                @foreach($availableRoles as $mainRole => $subRoles)
                                                <div class="card mb-3">
                                                    <div
                                                        class="card-header cnc:flex cnc:items-center cnc:justify-between cnc:bg-gray-100">
                                                        <h6 class="mb-0">{{ ucwords(str_replace('_', ' ', $mainRole)) }}
                                                        </h6>
                                                        <div class="cnc:flex cnc:items-center">
                                                            <input type="checkbox"
                                                                id="role_{{$mainRole}}_{{$admin->id}}"
                                                                name="roles[{{$mainRole}}]" value="true"
                                                                class="main-role-checkbox" data-role="{{$mainRole}}"
                                                                data-admin="{{$admin->id}}" {{
                                                                ($roleData && isset($roleData[$mainRole])) ? 'checked' : '' }}>
                                                            <label for="role_{{$mainRole}}_{{$admin->id}}"
                                                                class="ml-2">Enable</label>
                                                        </div>
                                                    </div>

                                                    <!-- Sub Roles if applicable -->
                                                    @if(count($subRoles) > 0)
                                                    <div class="card-body subrole-container"
                                                        id="subroles_{{$mainRole}}_{{$admin->id}}"
                                                        style="{{ ($roleData && isset($roleData[$mainRole]) && is_array($roleData[$mainRole])) ? '' : 'display: none;' }}">
                                                        <h6 class="mb-3 text-muted">Sub Permissions:</h6>
                                                        <div class="cnc:grid cnc:grid-cols-2 cnc:gap-4">
                                                            @foreach($subRoles as $subRole)
                                                            <div class="cnc:flex cnc:items-center cnc:mb-2">
                                                                <input type="checkbox"
                                                                    id="subrole_{{$mainRole}}_{{$subRole}}_{{$admin->id}}"
                                                                    name="roles[{{$mainRole}}][{{$subRole}}]"
                                                                    value="true" {{ 
                                                                    ($roleData && isset($roleData[$mainRole]) && 
                                                                    is_array($roleData[$mainRole]) && 
                                                                    isset($roleData[$mainRole][$subRole])) ? 'checked' : '' }}>
                                                                <label
                                                                    for="subrole_{{$mainRole}}_{{$subRole}}_{{$admin->id}}"
                                                                    class="ml-2">
                                                                    {{ ucwords(str_replace('_', ' ', $subRole)) }}
                                                                </label>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                                @endforeach
                                            </div>
                                            <button class="btn btn-primary mt-4" type="submit">Update Roles</button>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" type="button"
                                            data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="role-management" role="tabpanel">
            <div class="cnc:flex cnc:justify-end cnc:mb-4">
                <button data-toggle="modal" data-target="#createRoleModal" class="btn btn-success">
                    <i class="i-Add cnc:mr-1"></i> Create New Role
                </button>
            </div>

            <!-- Roles Table -->
            <div class="cnc:overflow-x-auto cnc:mt-6">
                <table class="display table table-striped table-bordered" id="roles_table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Role Name</th>
                            <th>Display Name</th>
                            <th>Description</th>
                            <th>Sub-Roles</th>
                            <th>Admins Using</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($availableRoles as $roleName => $subRoles)
                        @php
                        $role = App\Models\Role::where('name', $roleName)->first();
                        if (!$role) continue;

                        // Count admins using this role
                        $adminsUsingRole = 0;
                        foreach($admins as $admin) {
                            $roleData = safeDecryptRole($admin->role);
                            if($roleData && isset($roleData[$roleName])) {
                                $adminsUsingRole++;
                            }
                        }
                        @endphp
                        <tr>
                            <td>{{ $roleName }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $roleName)) }}</td>
                            <td>{{ $role->description ?? 'Access to ' . ucwords(str_replace('_', ' ', $roleName)) }}
                            </td>
                            <td>
                                @if(count($subRoles) > 0)
                                <span class="cnc:inline-block cnc:max-w-[140px] group cnc:cursor-pointer">
                                    {{ count($subRoles) }} sub-role(s)
                                    <span class="cnc:text-gray-400">...</span>
                                    <div
                                        class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content cnc:z-10">
                                        <ul class="cnc:pl-4 cnc:text-xs">
                                            @foreach($subRoles as $subRole)
                                            <li>- {{ ucwords(str_replace('_', ' ', $subRole)) }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </span>
                                @else
                                <span class="cnc:text-gray-500">No sub-roles</span>
                                @endif
                            </td>
                            <td>{{ $adminsUsingRole }}</td>
                            <td class="cnc:flex cnc:gap-2">
                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                    data-target="#editRoleModal-{{$roleName}}">
                                    <i class="i-Pen-2"></i> Edit
                                </button>

                                <form id="delete-form--{{$roleName}}"
                                    action="{{ route('admin.role.destroy', $roleName) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger role-delete-btn"
                                        data-role="{{$roleName}}">
                                        <i class="i-Close-Window"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
@foreach($availableRoles as $roleName => $subRoles)
@php
$role = App\Models\Role::where('name', $roleName)->first();
if (!$role) continue;

// Count admins using this role
$adminsUsingRole = 0;
foreach($admins as $admin) {
    $roleData = safeDecryptRole($admin->role);
    if($roleData && isset($roleData[$roleName])) {
        $adminsUsingRole++;
    }
}
@endphp
<div class="modal fade" id="editRoleModal-{{$roleName}}" tabindex="-1" role="dialog"
    aria-labelledby="editRoleModalTitle-{{$roleName}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered cnc:max-w-5xl! cnc:w-full!" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="i-Edit cnc:text-blue-500 mr-2"></i> Edit Role
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.role.update', $roleName) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Role Name</label>
                                <input class="form-control" type="text" name="role_name"
                                    value="{{ old('role_name', $roleName) }}"
                                    placeholder="Enter Role Name (e.g. reports, analytics)" required />
                                <small class="form-text text-muted">
                                    Use lowercase with underscore for spaces (e.g.
                                    user_management)
                                </small>
                            </div>

                            <div class="form-group mt-4">
                                <div class="cnc:flex cnc:items-center cnc:justify-between">
                                    <label>Has Sub-Roles?</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="hasSubRoles-{{$roleName}}"
                                            name="has_sub_roles" {{ count($subRoles)> 0 ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            <div id="subRolesContainer-{{$roleName}}" class="mt-4"
                                style="{{ count($subRoles) > 0 ? '' : 'display: none;' }}">
                                <h6 class="mb-3">Sub-Roles</h6>

                                <div id="subRolesList-{{$roleName}}">
                                    @if(count($subRoles) > 0)
                                    @foreach($subRoles as $subRole)
                                    <div class="cnc:flex cnc:items-center cnc:gap-2 cnc:mb-3 subrole-item">
                                        <input type="text" name="sub_roles[]" class="form-control"
                                            value="{{ $subRole }}"
                                            placeholder="Enter Sub-Role Name (e.g. view_reports)">
                                        <button type="button" class="btn btn-danger remove-subrole">
                                            <i class="i-Close-Window"></i>
                                        </button>
                                    </div>
                                    @endforeach
                                    @else
                                    <div class="cnc:flex cnc:items-center cnc:gap-2 cnc:mb-3 subrole-item">
                                        <input type="text" name="sub_roles[]" class="form-control"
                                            placeholder="Enter Sub-Role Name (e.g. view_reports)">
                                        <button type="button" class="btn btn-danger remove-subrole">
                                            <i class="i-Close-Window"></i>
                                        </button>
                                    </div>
                                    @endif
                                </div>

                                <button type="button" class="btn btn-info mt-2 add-more-subrole"
                                    data-role="{{$roleName}}">
                                    <i class="i-Add"></i> Add Another Sub-Role
                                </button>
                            </div>

                            <button class="btn btn-primary mt-4" type="submit">Update Role</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Create Admin Modal -->
<div class="modal fade" id="createAdminModal" tabindex="-1" role="dialog" aria-labelledby="createAdminModalTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered cnc:max-w-4xl! cnc:w-full!" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createAdminModalTitle">
                    <i class="i-Add-User cnc:text-green-500 mr-2"></i> Create New Admin
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.admin.create') }}" method="POST">
                    @csrf
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Admin Name</label>
                                <input class="form-control" type="text" name="name" value="{{ old('name') }}"
                                    placeholder="Enter Admin Name" required />
                            </div>

                            <div class="form-group">
                                <label>Admin Email</label>
                                <input class="form-control" type="email" name="email" value="{{ old('email') }}"
                                    placeholder="Enter Admin Email" required />
                            </div>

                            <div class="form-group">
                                <label>Password</label>
                                <input class="form-control" type="password" name="password" placeholder="Enter Password"
                                    required />
                            </div>

                            <div class="form-group">
                                <label>Account Type</label>
                                <select class="form-control" name="accounttype" required>
                                    <option value="">Select Account Type</option>
                                    @foreach($accountTypes as $type)
                                    <option value="{{ $type }}">{{ ucwords($type) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <h6 class="mt-4 mb-3 text-muted">Assign Roles:</h6>
                            <div class="cnc:grid cnc:grid-cols-2 cnc:gap-6">
                                @foreach($availableRoles as $mainRole => $subRoles)
                                <div class="card mb-3">
                                    <div
                                        class="card-header cnc:flex cnc:items-center cnc:justify-between cnc:bg-gray-100">
                                        <h6 class="mb-0">{{ ucwords(str_replace('_', ' ', $mainRole)) }}</h6>
                                        <div class="cnc:flex cnc:items-center">
                                            <input type="checkbox" id="create_role_{{$mainRole}}"
                                                name="roles[{{$mainRole}}]" value="true"
                                                class="create-main-role-checkbox" data-role="{{$mainRole}}">
                                            <label for="create_role_{{$mainRole}}" class="ml-2">Enable</label>
                                        </div>
                                    </div>
                                    @if(count($subRoles) > 0)
                                    <div class="card-body create-subrole-container" id="create_subroles_{{$mainRole}}"
                                        style="display: none;">
                                        <h6 class="mb-3 text-muted">Sub Permissions:</h6>
                                        <div class="cnc:grid cnc:grid-cols-2 cnc:gap-4">
                                            @foreach($subRoles as $subRole)
                                            <div class="cnc:flex cnc:items-center cnc:mb-2">
                                                <input type="checkbox" id="create_subrole_{{$mainRole}}_{{$subRole}}"
                                                    name="roles[{{$mainRole}}][{{$subRole}}]" value="true">
                                                <label for="create_subrole_{{$mainRole}}_{{$subRole}}" class="ml-2">
                                                    {{ ucwords(str_replace('_', ' ', $subRole)) }}
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>

                            <button class="btn btn-success mt-4" type="submit">Create Admin</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1" role="dialog" aria-labelledby="createRoleModalTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered cnc:max-w-5xl! cnc:w-full!" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRoleModalTitle">
                    <i class="i-Security-Check cnc:text-blue-500 mr-2"></i> Create New Role
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.role.create') }}" method="POST">
                    @csrf
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Role Name</label>
                                <input class="form-control" type="text" name="role_name" value="{{ old('role_name') }}"
                                    placeholder="Enter Role Name (e.g. reports, analytics)" required />
                                <small class="form-text text-muted">
                                    Use lowercase with underscore for spaces (e.g. user_management)
                                </small>
                            </div>

                            <div class="form-group mt-4">
                                <div class="cnc:flex cnc:items-center cnc:justify-between">
                                    <label>Has Sub-Roles?</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="hasSubRoles"
                                            name="has_sub_roles">
                                    </div>
                                </div>
                            </div>

                            <div id="subRolesContainer" class="mt-4" style="display: none;">
                                <h6 class="mb-3">Sub-Roles</h6>

                                <div id="subRolesList">
                                    <div class="cnc:flex cnc:items-center cnc:gap-2 cnc:mb-3 subrole-item">
                                        <input type="text" name="sub_roles[]" class="form-control"
                                            placeholder="Enter Sub-Role Name (e.g. view_reports)">
                                        <button type="button" class="btn btn-danger remove-subrole">
                                            <i class="i-Close-Window"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-info mt-2" id="addMoreSubRole">
                                    <i class="i-Add"></i> Add Another Sub-Role
                                </button>
                            </div>

                            <button class="btn btn-success mt-4" type="submit">Create Role</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // CREATE ROLE FUNCTIONALITY
document.addEventListener('DOMContentLoaded', function() {
// Create Role Modal
const hasSubRolesCheckbox = document.querySelector('#createRoleModal #hasSubRoles');
const subRolesContainer = document.querySelector('#createRoleModal #subRolesContainer');
const subRolesList = document.querySelector('#createRoleModal #subRolesList');
const addMoreSubRoleButton = document.querySelector('#createRoleModal #addMoreSubRole');

// Toggle sub-roles container visibility
if (hasSubRolesCheckbox && subRolesContainer) {
hasSubRolesCheckbox.addEventListener('change', function() {
subRolesContainer.style.display = this.checked ? 'block' : 'none';
});
}

// Add more sub-roles in create form
if (addMoreSubRoleButton && subRolesList) {
addMoreSubRoleButton.addEventListener('click', function() {
const newSubRole = document.createElement('div');
newSubRole.className = 'cnc:flex cnc:items-center cnc:gap-2 cnc:mb-3 subrole-item';
newSubRole.innerHTML = `
<input type="text" name="sub_roles[]" class="form-control" placeholder="Enter Sub-Role Name (e.g. view_reports)">
<button type="button" class="btn btn-danger remove-subrole">
    <i class="i-Close-Window"></i>
</button>
`;
subRolesList.appendChild(newSubRole);

// Add remove event listener
const removeButton = newSubRole.querySelector('.remove-subrole');
if (removeButton) {
removeButton.addEventListener('click', function() {
subRolesList.removeChild(newSubRole);
});
}
});
}

// Remove sub-role buttons in create form
if (subRolesList) {
const removeButtons = subRolesList.querySelectorAll('.remove-subrole');
removeButtons.forEach(function(button) {
button.addEventListener('click', function() {
const subRoleItem = this.closest('.subrole-item');
if (subRoleItem && subRolesList.contains(subRoleItem)) {
subRolesList.removeChild(subRoleItem);
}
});
});
}

// EDIT ROLE FUNCTIONALITY
// Handle each edit role modal separately
const editRoleModals = document.querySelectorAll('[id^="editRoleModal-"]');

editRoleModals.forEach(function(modal) {
// Get role name from modal ID
const roleNameParts = modal.id.split('-');
const roleName = roleNameParts.slice(1).join('-');

// Get elements within this specific modal
const hasSubRolesCheckbox = modal.querySelector(`#hasSubRoles-${roleName}`);
const subRolesContainer = modal.querySelector(`#subRolesContainer-${roleName}`);
const subRolesList = modal.querySelector(`#subRolesList-${roleName}`);
const addMoreSubRoleButton = modal.querySelector(`.add-more-subrole[data-role="${roleName}"]`);

// Toggle sub-roles container visibility
if (hasSubRolesCheckbox && subRolesContainer) {
hasSubRolesCheckbox.addEventListener('change', function() {
subRolesContainer.style.display = this.checked ? 'block' : 'none';
});
}

// Add more sub-roles in edit form
if (addMoreSubRoleButton && subRolesList) {
addMoreSubRoleButton.addEventListener('click', function() {
const newSubRole = document.createElement('div');
newSubRole.className = 'cnc:flex cnc:items-center cnc:gap-2 cnc:mb-3 subrole-item';
newSubRole.innerHTML = `
<input type="text" name="sub_roles[]" class="form-control" placeholder="Enter Sub-Role Name (e.g. view_reports)">
<button type="button" class="btn btn-danger remove-subrole">
    <i class="i-Close-Window"></i>
</button>
`;
subRolesList.appendChild(newSubRole);

// Add remove event listener
const removeButton = newSubRole.querySelector('.remove-subrole');
if (removeButton) {
removeButton.addEventListener('click', function() {
subRolesList.removeChild(newSubRole);
});
}
});
}

// Remove sub-role buttons in edit form
if (subRolesList) {
const removeButtons = subRolesList.querySelectorAll('.remove-subrole');
removeButtons.forEach(function(button) {
button.addEventListener('click', function() {
const subRoleItem = this.closest('.subrole-item');
if (subRoleItem && subRolesList.contains(subRoleItem)) {
subRolesList.removeChild(subRoleItem);
}
});
});
}
});

// ADMIN ROLE MANAGEMENT
// Main role checkbox toggle sub-roles visibility
document.querySelectorAll('.main-role-checkbox').forEach(function(checkbox) {
checkbox.addEventListener('change', function() {
const roleId = this.getAttribute('data-role');
const adminId = this.getAttribute('data-admin');
const subRolesContainer = document.getElementById(`subroles_${roleId}_${adminId}`);

if (subRolesContainer) {
subRolesContainer.style.display = this.checked ? 'block' : 'none';
}
});
});

// DELETE CONFIRMATIONS
// Delete admin confirmation
document.querySelectorAll('.admin-delete-btn').forEach(function(button) {
button.addEventListener('click', function(e) {
e.preventDefault();
const adminId = this.getAttribute('data-id');
const form = document.getElementById(`delete-admin-form-${adminId}`);

Swal.fire({
title: 'Are you sure?',
text: "This Admin will be deleted permanently!",
icon: 'warning',
showCancelButton: true,
confirmButtonText: 'Yes, delete it!',
cancelButtonText: 'Cancel',
reverseButtons: true
}).then((result) => {
if (result.isConfirmed) {
form.submit();
}
});
});
});

// Delete role confirmation
document.querySelectorAll('.role-delete-btn').forEach(function(button) {
button.addEventListener('click', function(e) {
e.preventDefault();
const form = this.closest('form');

Swal.fire({
title: 'Are you sure?',
text: "This Role will be deleted permanently!",
icon: 'warning',
showCancelButton: true,
confirmButtonText: 'Yes, delete it!',
cancelButtonText: 'Cancel',
reverseButtons: true
}).then((result) => {
if (result.isConfirmed) {
form.submit();
}
});
});
});

// TOOLTIPS
// Tooltip functionality for roles display
document.querySelectorAll('.group').forEach(function(item) {
item.addEventListener('click', function(e) {
const tooltip = e.currentTarget.querySelector('.tooltip-content');
if (tooltip) {
tooltip.classList.toggle('cnc:hidden');
}
});

item.addEventListener('mouseover', function(e) {
const tooltip = e.currentTarget.querySelector('.tooltip-content');
if (tooltip) {
tooltip.classList.remove('cnc:hidden');
}
});

item.addEventListener('mouseleave', function(e) {
const tooltip = e.currentTarget.querySelector('.tooltip-content');
if (tooltip) {
tooltip.classList.add('cnc:hidden');
}
});
});
});

document.addEventListener('DOMContentLoaded', function() {
    const mainRoleCheckboxes = document.querySelectorAll('.create-main-role-checkbox');
    
    mainRoleCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
    const mainRole = this.dataset.role;
    const subroleContainer = document.getElementById(`create_subroles_${mainRole}`);
    if (subroleContainer) {
    subroleContainer.style.display = this.checked ? 'block' : 'none';
    // Optionally, uncheck all sub-roles when the main role is unchecked
    if (!this.checked) {
    const subroleCheckboxes = subroleContainer.querySelectorAll('input[type="checkbox"]');
    subroleCheckboxes.forEach(subCheckbox => {
    subCheckbox.checked = false;
    });
    }
    }
    });
    });
    });
</script>

@endsection