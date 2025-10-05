<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class CheckPanelRole
{
    public function handle(Request $request, Closure $next, ...$args)
    {
        $admin = auth('admin')->user();

        if (!$admin || !$admin->role) {
            return back()->with('error', 'Unauthorized');
        }

        // Handle simple role strings (like 'superAdmin')
        if (is_string($admin->role) && !$this->isEncryptedJson($admin->role)) {
            // Simple role check
            if ($admin->role === 'superAdmin') {
                return $next($request); // SuperAdmin has access to everything
            }
            
            // Check if the simple role matches any of the required roles
            $rawChecks = implode(',', $args);
            if (strpos($rawChecks, $admin->role) !== false || strpos($rawChecks, 'superAdmin') !== false) {
                return $next($request);
            }
            
            return back()->with('error', 'Access denied.');
        }

        // Handle encrypted JSON roles (original logic)
        try {
            $roles = json_decode(Crypt::decryptString($admin->role), true);
        } catch (\Exception $e) {
            return back()->with('error', 'Invalid role structure');
        }

        $rawChecks = implode(',', $args);
        $orConditions = explode('|', $rawChecks);

        foreach ($orConditions as $orCheck) {
            $andConditions = explode('+', $orCheck);
            $allPassed = true;

            foreach ($andConditions as $cond) {
                [$key, $subkey] = array_pad(explode(',', trim($cond)), 2, null);

                if ($subkey) {
                    if (empty($roles[$key][$subkey])) {
                        $allPassed = false;
                        break;
                    }
                } else {
                    if (empty($roles[$key])) {
                        $allPassed = false;
                        break;
                    }
                }
            }

            if ($allPassed) {
                return $next($request);
            }
        }

        return back()->with('error', 'Access denied.');
    }

    private function isEncryptedJson($string)
    {
        try {
            $decrypted = Crypt::decryptString($string);
            json_decode($decrypted);
            return json_last_error() === JSON_ERROR_NONE;
        } catch (\Exception $e) {
            return false;
        }
    }
}