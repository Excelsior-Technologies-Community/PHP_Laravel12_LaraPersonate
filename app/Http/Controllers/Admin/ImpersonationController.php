<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    // Start impersonation
    public function impersonate($id)
    {
        $currentUser = Auth::user();

        // Only allow admin to impersonate
        if (!$currentUser || !$currentUser->is_admin) {
            abort(403, 'Unauthorized');
        }

        $user = User::findOrFail($id);

        // Prevent impersonating self
        if ($currentUser->id === $user->id) {
            return back()->with('error', 'You cannot impersonate yourself');
        }

        // Store original user
        session(['impersonate_original_id' => $currentUser->id]);

        // Login as target user
        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Now impersonating ' . $user->name);
    }

    // Stop impersonation
    public function leave()
    {
        if (session()->has('impersonate_original_id')) {

            $originalId = session('impersonate_original_id');

            Auth::loginUsingId($originalId);

            session()->forget('impersonate_original_id');

            return redirect('/dashboard')->with('success', 'Back to your account');
        }

        return redirect('/dashboard');
    }
}