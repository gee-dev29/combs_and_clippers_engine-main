<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class AdminController extends Controller
{
    public function admin()
    {
        $access = collect(DB::table('menus')->get());
        return view('admin.index', compact('access'));
    }

    public function saveAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:admins'
        ]);

        $password = generatePassword();

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'accounttype' => $request->accountType,
            'role' => json_encode($request->access),
            'password' => bcrypt($password),
        ]);
        if ($admin) {
            $this->Mailer->sendAdminPasswordEmail($admin, $password);
            return redirect()->back()->with('success', 'Admin user created successfully. An email containing the password has been sent.');
        } else {
            return redirect()->back()->with('error', 'Admin user creation failed. Please try again.');
        }
    }

    public function getAdmins()
    {
        $admins = Admin::all();
        return view('admin.admins', compact('admins'));
    }

    public function password()
    {
        return view('admin.password');
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|min:4|confirmed|string',
            'password_confirmation' => 'required|string|min:4',
        ]);

        if (!(Hash::check($request->current_password, Auth::guard('admin')->user()->password))) {
            //current password is incorrect
            return back()->with("error", "Your current password is incorrect");
        }

        if (strcmp($request->current_password, $request->password) == 0) {
            // Current password and new password same
            return back()->with("error", "New Password cannot be same as your current password.");
        }

        //Change Password
        /**@var Admin */
        $user = Auth::guard('admin')->user();
        $user->password = bcrypt($request->password);
        $user->save();
        return back()->with("success", "Password successfully changed!");
    }

    public function show($id)
    {
        $admin = DB::table('admins')->where('id', $id)->get()->first();
        return view('admin.details', ['admin' => collect($admin)]);
    }

    public function delete($id)
    {
        DB::table('admins')->delete($id);
        return back()->with('delete', 'Delete successful');
    }


    public function index(Request $request)
    {
        // Query builder for admins
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

        // Role filtering is more complex as roles are stored as encrypted JSON
        if ($request->filled('role_filter')) {
            // This is a simplification - in production, you would need a more sophisticated approach
            // Maybe use a separate roles table or add special indexing
            $adminsQuery->where('role', 'like', '%' . $request->role_filter . '%');
        }

        // Get paginated results
        $admins = $adminsQuery->latest()->paginate(10);

        // Get statistics
        $totalAdmins = Admin::count();
        $recentLogins = 0; // Authentication logging removed

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
                $roleData = json_decode(Crypt::decryptString($admin->role), true);
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


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8',
            'accounttype' => 'required|string',
        ]);

        // Get available roles from database
        $availableRoles = Role::getAvailableRoles();

        // Default role structure with no permissions
        $defaultRole = [];
        foreach ($availableRoles as $roleName => $subroles) {
            if (!empty($subroles)) {
                $defaultRole[$roleName] = [];
                foreach ($subroles as $subroleName) {
                    $defaultRole[$roleName][$subroleName] = false;
                }
            } else {
                $defaultRole[$roleName] = false;
            }
        }

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'accounttype' => $request->accounttype,
            'role' => Crypt::encryptString(json_encode($defaultRole)),
        ]);

        return redirect()->route('admin.management')
            ->with('success', 'Admin created successfully.');
    }


    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

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
        }

        $admin->update($data);

        return redirect()->route('admin.management')
            ->with('success', 'Admin updated successfully.');
    }

    public function updateRoles(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $roles = $request->roles ?? [];

        // Process roles to ensure proper structure
        $processedRoles = [];
        foreach ($roles as $role => $value) {
            if (is_array($value)) {
                // This is a role with subroles
                $processedRoles[$role] = $value;
            } else {
                // This is a simple role
                $processedRoles[$role] = true;
            }
        }

        // Get all available roles to ensure the admin has an entry for each role
        $availableRoles = Role::getAvailableRoles();

        // Ensure each role exists in the processed roles (set to false if not selected)
        foreach ($availableRoles as $roleName => $subroles) {
            if (!isset($processedRoles[$roleName])) {
                if (!empty($subroles)) {
                    $processedRoles[$roleName] = [];
                    foreach ($subroles as $subroleName) {
                        $processedRoles[$roleName][$subroleName] = false;
                    }
                } else {
                    $processedRoles[$roleName] = false;
                }
            }
        }

        // Update admin's roles
        $admin->update([
            'role' => Crypt::encryptString(json_encode($processedRoles)),
        ]);

        return redirect()->route('admin.management')
            ->with('success', 'Admin roles updated successfully.');
    }


    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return redirect()->route('admin.management')
            ->with('success', 'Admin deleted successfully.');
    }
}