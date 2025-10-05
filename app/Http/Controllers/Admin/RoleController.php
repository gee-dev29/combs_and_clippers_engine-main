<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Admin;
use App\Models\Role;
use App\Models\SubRole;

class RoleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:255',
            'has_sub_roles' => 'sometimes',
            'sub_roles' => 'required_if:has_sub_roles,on|array',
        ]);

        $roleName = strtolower(str_replace(' ', '_', $request->role_name));
        $displayName = $request->role_name;

        // Check if the role already exists
        if (Role::where('name', $roleName)->exists()) {
            return redirect()->route('admin.management')
                ->with('error', 'Role already exists.');
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create the new role
            $role = Role::create([
                'name' => $roleName,
                'display_name' => $displayName,
                'description' => 'Access to ' . $displayName,
            ]);

            // If it has subroles, create them too
            if ($request->has('has_sub_roles') && !empty($request->sub_roles)) {
                $subroles = [];
                foreach ($request->sub_roles as $subRoleName) {
                    if (!empty($subRoleName)) {
                        $subRoleSlug = strtolower(str_replace(' ', '_', $subRoleName));
                        SubRole::create([
                            'role_id' => $role->id,
                            'name' => $subRoleSlug,
                            'display_name' => $subRoleName,
                            'description' => 'Access to ' . $subRoleName,
                        ]);
                        $subroles[] = $subRoleSlug;
                    }
                }
            }

            // Update all existing admins to include the new role (set to false by default)
            // $admins = Admin::all();
            // foreach ($admins as $admin) {
            //     try {
            //         $roleData = json_decode(Crypt::decryptString($admin->role), true);

            //         // Add the new role to the admin's role data
            //         if ($request->has('has_sub_roles') && !empty($request->sub_roles)) {
            //             $subroleValues = [];
            //             foreach ($subroles as $subRole) {
            //                 $subroleValues[$subRole] = false;
            //             }
            //             $roleData[$roleName] = $subroleValues;
            //         } else {
            //             $roleData[$roleName] = false;
            //         }

            //         // Update the admin's role
            //         $admin->update([
            //             'role' => Crypt::encryptString(json_encode($roleData)),
            //         ]);
            //     } catch (\Exception $e) {
            //         // Handle decryption error
            //         continue;
            //     }
            // }

            DB::commit();

            return redirect()->route('admin.management')
                ->with('success', 'Role created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.management')
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $roleName)
    {
        $request->validate([
            'role_name' => 'required|string|max:255',
            'has_sub_roles' => 'sometimes',
            'sub_roles' => 'required_if:has_sub_roles,on|array',
        ]);

        // Find the role
        $role = Role::where('name', $roleName)->first();


        if (!$role) {
            return redirect()->route('admin.management')
                ->with('error', 'Role does not exist.');
        }

        $newRoleName = strtolower(str_replace(' ', '_', $request->role_name));
        $displayName = $request->role_name;

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the role
            $role->update([
                'name' => $newRoleName,
                'display_name' => $displayName,
                'description' => 'Access to ' . $displayName,
            ]);

            // Handle subroles
            // First, get the current subroles with their names and display names
            $currentSubrolesData = $role->subRoles->mapWithKeys(function ($item) {
                return [$item->name => $item->display_name];
            })->toArray();
            $currentSubroles = array_keys($currentSubrolesData);

            // New subroles mapping from the request
            $newSubrolesData = [];
            $newSubroles = [];

            if ($request->has('has_sub_roles') && !empty($request->sub_roles)) {
                foreach ($request->sub_roles as $subRoleName) {
                    if (!empty($subRoleName)) {
                        $subRoleSlug = strtolower(str_replace(' ', '_', $subRoleName));
                        $newSubroles[] = $subRoleSlug;
                        $newSubrolesData[$subRoleSlug] = $subRoleName;

                        // Check if this subrole already exists
                        $existingSubrole = SubRole::where('role_id', $role->id)
                            ->where('name', $subRoleSlug)
                            ->first();

                        if (!$existingSubrole) {
                            // Create new subrole
                            SubRole::create([
                                'role_id' => $role->id,
                                'name' => $subRoleSlug,
                                'display_name' => $subRoleName,
                                'description' => 'Access to ' . $subRoleName,
                            ]);
                        } else {
                            // Update display name if it changed
                            if ($existingSubrole->display_name !== $subRoleName) {
                                $existingSubrole->update([
                                    'display_name' => $subRoleName,
                                    'description' => 'Access to ' . $subRoleName,
                                ]);
                            }
                        }
                    }
                }
            }

            // Delete removed subroles
            $subroleToDelete = array_diff($currentSubroles, $newSubroles);
            if (!empty($subroleToDelete)) {
                SubRole::where('role_id', $role->id)
                    ->whereIn('name', $subroleToDelete)
                    ->delete();
            }

            // Create mapping of old subrole names to new ones (for renamed subroles)
            $subroleNameMap = [];
            foreach ($newSubrolesData as $newName => $newDisplayName) {
                foreach ($currentSubrolesData as $oldName => $oldDisplayName) {
                    // If display names match but slugs differ, it's a rename
                    if ($newDisplayName === $oldDisplayName && $newName !== $oldName) {
                        $subroleNameMap[$oldName] = $newName;
                    }
                }
            }

            // Update all admins if the role name has changed or subroles changed
            $admins = Admin::all();
            foreach ($admins as $admin) {
                try {
                    $roleData = json_decode(Crypt::decryptString($admin->role), true);
                    $updated = false;

                    // If the old role exists in admin's permissions
                    if (isset($roleData[$roleName])) {
                        $oldRoleData = $roleData[$roleName];

                        // If role name changed
                        if ($roleName !== $newRoleName) {
                            // For roles with subroles
                            if (is_array($oldRoleData)) {
                                $newRoleData = [];

                                // Update any renamed subroles and keep only existing ones
                                foreach ($oldRoleData as $subrole => $value) {
                                    if (in_array($subrole, $subroleToDelete)) {
                                        // Skip deleted subroles
                                        continue;
                                    }

                                    // If subrole was renamed, use new name
                                    if (isset($subroleNameMap[$subrole])) {
                                        $newRoleData[$subroleNameMap[$subrole]] = $value;
                                    } else if (in_array($subrole, $newSubroles)) {
                                        $newRoleData[$subrole] = $value;
                                    }
                                }

                                // Add any new subroles with default false value
                                foreach ($newSubroles as $newSubrole) {
                                    if (!isset($newRoleData[$newSubrole])) {
                                        $newRoleData[$newSubrole] = true;
                                    }
                                }

                                $roleData[$newRoleName] = $newRoleData;
                            } else {
                                // For roles without subroles
                                $roleData[$newRoleName] = $oldRoleData;
                            }

                            // Remove the old role
                            unset($roleData[$roleName]);
                            $updated = true;
                        }
                        // If role name didn't change but subroles did
                        else if (is_array($oldRoleData)) {
                            $newRoleData = [];

                            // Update any renamed subroles and keep only existing ones
                            foreach ($oldRoleData as $subrole => $value) {
                                if (in_array($subrole, $subroleToDelete)) {
                                    // Skip deleted subroles
                                    continue;
                                }

                                // If subrole was renamed, use new name
                                if (isset($subroleNameMap[$subrole])) {
                                    $newRoleData[$subroleNameMap[$subrole]] = $value;
                                    $updated = true;
                                } else if (in_array($subrole, $newSubroles)) {
                                    $newRoleData[$subrole] = $value;
                                }
                            }

                            // Add any new subroles with default false value
                            foreach ($newSubroles as $newSubrole) {
                                if (!isset($newRoleData[$newSubrole])) {
                                    $newRoleData[$newSubrole] = true;
                                    $updated = true;
                                }
                            }

                            if ($updated) {
                                $roleData[$roleName] = $newRoleData;
                            }
                        }

                        // Update admin's permissions if needed
                        if ($updated) {
                            $admin->update([
                                'role' => Crypt::encryptString(json_encode($roleData)),
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    // Handle decryption error
                    continue;
                }
            }

            DB::commit();

            return redirect()->route('admin.management')
                ->with('success', 'Role updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.management')
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function destroy($roleName)
    {
        // Find the role
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return redirect()->route('admin.management')
                ->with('error', 'Role does not exist.');
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Delete the role (subroles will be cascaded due to foreign key constraint)
            $role->delete();

            // Now update all admins to remove this role from their permissions
            $admins = Admin::all();
            foreach ($admins as $admin) {
                try {
                    $roleData = json_decode(Crypt::decryptString($admin->role), true);

                    // Remove the role if it exists
                    if (isset($roleData[$roleName])) {
                        unset($roleData[$roleName]);

                        // Update the admin's role
                        $admin->update([
                            'role' => Crypt::encryptString(json_encode($roleData)),
                        ]);
                    }
                } catch (\Exception $e) {
                    // Handle decryption error
                    continue;
                }
            }

            DB::commit();

            return redirect()->route('admin.management')
                ->with('success', 'Role deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.management')
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}