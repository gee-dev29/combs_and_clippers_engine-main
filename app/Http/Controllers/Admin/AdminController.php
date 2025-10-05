<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\Admin;
use App\Repositories\Mailer;
use Illuminate\Http\Request;
use App\Mail\AdminAccountUpdate;
use App\Mail\AdminPasswordEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

class AdminController extends Controller
{


    /**
     * Display the admin management page with filtering and statistics
     */
    public function index(Request $request)
    {

        $adminsQuery = Admin::query();

        // Apply filters
        if ($request->filled('admin_name')) {
            $adminsQuery->where('name', 'like', '%' . $request->admin_name . '%');
        }

        if ($request->filled('admin_email')) {
            $adminsQuery->where('email', 'like', '%' . $request->admin_email . '%');
        }

        if ($request->filled('account_type')) {
            $adminsQuery->where('accounttype', $request->account_type);
        }

        // Get paginated results
        $admins = $adminsQuery->latest()->get();

        // Get statistics
        $totalAdmins = Admin::count();
        $recentLogins = DB::table('authentication_log')
            ->where('authenticatable_type', 'App\\Models\\Admin')
            ->where('login_at', '>=', Carbon::now()->subWeek())
            ->count();

        // Account types
        $accountTypes = Admin::distinct('accounttype')->pluck('accounttype')->toArray();
        $adminTypes = collect($accountTypes);

        // Get role distribution
        $roleDistribution = [];
        $allRoles = [];

        // Get all available roles from the database
        $availableRoles = Role::getAvailableRoles();

        foreach (Admin::all() as $admin) {
            try {
                $roleData = json_decode($this->decryptRole($admin->role), true);
                foreach ($roleData as $role => $value) {
                    if (!isset($roleDistribution[$role])) {
                        $roleDistribution[$role] = 0;
                        $allRoles[] = $role;
                    }
                    $roleDistribution[$role]++;
                }
            } catch (\Exception $e) {
                // Handle decryption error
                continue;
            }
        }

        // Count total unique roles
        $totalRoles = count($allRoles);

        return view('comb_and_clippers_admin.admin.admin', compact(
            'admins',
            'totalAdmins',
            'recentLogins',
            'adminTypes',
            'roleDistribution',
            'accountTypes',
            'totalRoles',
            'allRoles',
            'availableRoles'
        ));
    }


    /**
     * Store a newly created admin in storage with default roles
     */

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'accounttype' => 'required|string',
            'roles' => 'nullable|array', // Add validation for roles
        ]);

        // Generate a random password or use the provided one
        $password = $request->filled('password') ? $request->password : $this->generatePassword();

        // Process the submitted roles
        $processedRoles = [];
        $availableRoles = Role::getAvailableRoles();

        if ($request->has('roles')) {
            foreach ($availableRoles as $roleName => $subroles) {
                if (!empty($subroles)) {
                    $subroleChecked = [];
                    if (isset($request->roles[$roleName]) && is_array($request->roles[$roleName])) {
                        foreach ($subroles as $subroleName) {
                            if (isset($request->roles[$roleName][$subroleName]) && filter_var($request->roles[$roleName][$subroleName], FILTER_VALIDATE_BOOLEAN)) {
                                $subroleChecked[$subroleName] = true;
                            }
                        }
                    }
                    if (!empty($subroleChecked)) {
                        $processedRoles[$roleName] = $subroleChecked;
                    }
                } else {
                    if (isset($request->roles[$roleName]) && filter_var($request->roles[$roleName], FILTER_VALIDATE_BOOLEAN)) {
                        $processedRoles[$roleName] = true;
                    }
                }
            }
        }

        // Create the admin account with assigned roles
        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'accounttype' => $request->accounttype,
            'role' => $this->encryptRole($processedRoles), // Encrypt the roles
        ]);

        // Send password email
        $this->sendPasswordEmail($admin, $password);

        return redirect()->route('admin.management')
            ->with('success', 'Admin created successfully with assigned roles. An email containing the password has been sent.');
    }


    /**
     * Update the specified admin's basic info
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $oldData = $admin->toArray();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $id,
            'accounttype' => 'required|string',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'accounttype' => $request->accounttype,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $passwordChanged = true;
        } else {
            $passwordChanged = false;
        }

        $admin->update($data);

        // Prepare change summary for email notification
        $changes = $this->getChanges($oldData, $admin->toArray(), $passwordChanged);



        // Send email notification about account update
        if (!empty($changes)) {

            $this->sendUpdateNotification($admin, $changes);
        }

        return redirect()->route('admin.management')
            ->with('success', 'Admin updated successfully.');
    }

    /**
     * Update the specified admin's roles
     */
    public function updateRoles(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $roles = $request->roles ?? [];


        $availableRoles = Role::getAvailableRoles();


        try {
            $currentRoles = json_decode($this->decryptRole($admin->role), true);
        } catch (\Exception $e) {
            $currentRoles = [];
        }


        // Process roles to ensure proper structure
        $processedRoles = [];
        foreach ($availableRoles as $roleName => $subroles) {
            if (!empty($subroles)) {
                $subroleChecked = [];

                foreach ($subroles as $subroleName) {
                    if (
                        isset($roles[$roleName][$subroleName]) &&
                        filter_var($roles[$roleName][$subroleName], FILTER_VALIDATE_BOOLEAN)
                    ) {
                        $subroleChecked[$subroleName] = true;
                    }
                }

                // Only include this role if at least one subrole is checked
                if (!empty($subroleChecked)) {
                    $processedRoles[$roleName] = $subroleChecked;
                }

            } else {
                if (
                    isset($roles[$roleName]) &&
                    filter_var($roles[$roleName], FILTER_VALIDATE_BOOLEAN)
                ) {
                    $processedRoles[$roleName] = true;
                }
            }
        }


        // dd($currentRoles, $roles, $processedRoles);

        // Update admin's roles
        $encryptedRoles = $this->encryptRole($processedRoles);
        $admin->update([
            'role' => $encryptedRoles,
        ]);

        // Find what roles changed for notification
        $roleChanges = $this->getRoleChanges($currentRoles, $processedRoles);

        // dd($roleChanges);
        // Send email notification about role updates
        if (!empty($roleChanges)) {
            $this->sendRoleUpdateNotification($admin, $roleChanges);
        }

        return redirect()->route('admin.management')
            ->with('success', 'Admin roles updated successfully.');
    }

    /**
     * Remove the specified admin from storage
     */
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return redirect()->route('admin.management')
            ->with('success', 'Admin deleted successfully.');
    }




    /**
     * Update the authenticated admin's password
     */
    public function passwordUpdate(Request $request)
    {
        $this->validate($request, [
            'current_password' => 'required|string',
            'password' => 'required|min:8|confirmed|string',
            'password_confirmation' => 'required|string|min:8',
        ]);

        if (!(Hash::check($request->current_password, Auth::guard('admin')->user()->password))) {
            return back()->with("error", "Your current password is incorrect");
        }

        if (strcmp($request->current_password, $request->password) == 0) {
            return back()->with("error", "New Password cannot be same as your current password.");
        }

        // Change Password
        $user = Auth::guard('admin')->user();
        $user->password = Hash::make($request->password);
        $user->save();

        // Send password change notification
        $this->sendPasswordChangeNotification($user);

        return back()->with("success", "Password successfully changed!");
    }

    /**
     * Generate a random password
     */
    private function generatePassword($length = 12)
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= substr($chars, rand(0, strlen($chars) - 1), 1);
        }

        return $password;
    }

    /**
     * Encrypt role data
     */
    private function encryptRole($roleData)
    {
        return Crypt::encryptString(json_encode($roleData));
    }

    /**
     * Decrypt role data
     */
    private function decryptRole($encryptedRole)
    {
        return Crypt::decryptString($encryptedRole);
    }

    /**
     * Send password email to new admin
     */
    private function sendPasswordEmail($admin, $password)
    {
        $mailer = new Mailer();
        $mailer->sendAdminPasswordEmail($admin, $password);
    }

    /**
     * Send account update notification
     */
    private function sendUpdateNotification($admin, $changes)
    {
        $mailer = new Mailer();
        $mailer->sendAdminAccountUpdateEmail($admin, $changes, 'account_update');

    }

    /**
     * Send role update notification
     */
    private function sendRoleUpdateNotification($admin, $roleChanges)
    {
        // Log::info('Role changes: ', $roleChanges); 
        $mailer = new Mailer();
        $mailer->sendAdminAccountUpdateEmail($admin, $roleChanges, 'role_update');

    }

    /**
     * Send password change notification
     */
    private function sendPasswordChangeNotification($admin)
    {
        $mailer = new Mailer();
        $mailer->sendAdminAccountUpdateEmail($admin, ['Password was changed'], 'password_change');

    }

    /**
     * Get changes between old and new admin data
     */
    private function getChanges($oldData, $newData, $passwordChanged = false)
    {
        $changes = [];

        // Check basic fields
        $fieldsToCheck = ['name', 'email', 'accounttype'];

        foreach ($fieldsToCheck as $field) {
            if ($oldData[$field] !== $newData[$field]) {
                $changes[] = ucfirst($field) . ' changed from "' . $oldData[$field] . '" to "' . $newData[$field] . '"';
            }
        }

        // Add password change if applicable
        if ($passwordChanged) {
            $changes[] = 'Password was changed';
        }

        return $changes;
    }

    /**
     * Get role changes between old and new roles
     */
    private function getRoleChanges($oldRoles, $newRoles)
    {
        $changes = [];

        // Check for additions or updates
        foreach ($newRoles as $roleName => $value) {
            if (is_array($value)) {
                foreach ($value as $subroleName => $subvalue) {
                    $oldValue = $oldRoles[$roleName][$subroleName] ?? false;

                    if ($oldValue !== $subvalue) {
                        $status = $subvalue ? 'granted' : 'revoked';
                        $changes[] = "Permission for '$roleName > $subroleName' was $status";
                    }
                }
            } else {
                $oldValue = $oldRoles[$roleName] ?? false;

                if ($oldValue !== $value) {
                    $status = $value ? 'granted' : 'revoked';
                    $changes[] = "Permission for '$roleName' was $status";
                }
            }
        }

        // Check for removed roles or subroles
        foreach ($oldRoles as $roleName => $value) {
            if (!isset($newRoles[$roleName])) {
                // Entire role was removed
                if (is_array($value)) {
                    foreach ($value as $subroleName => $_) {
                        $changes[] = "Permission for '$roleName > $subroleName' was revoked";
                    }
                } else {
                    $changes[] = "Permission for '$roleName' was revoked";
                }
            } elseif (is_array($value)) {
                foreach ($value as $subroleName => $_) {
                    if (!isset($newRoles[$roleName][$subroleName])) {
                        $changes[] = "Permission for '$roleName > $subroleName' was revoked";
                    }
                }
            }
        }

        return $changes;
    }

}