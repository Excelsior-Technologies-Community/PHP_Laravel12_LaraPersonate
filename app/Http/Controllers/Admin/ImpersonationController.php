<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ImpersonationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function impersonate($id, Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser || !$currentUser->is_admin) {
            abort(403, 'Unauthorized');
        }

        $user = User::findOrFail($id);

        if ($currentUser->id === $user->id) {
            return back()->with('error', 'You cannot impersonate yourself');
        }

        ImpersonationLog::create([
            'admin_id' => $currentUser->id,
            'user_id' => $user->id,
            'action' => 'started',
            'ip_address' => $request->ip(),
        ]);

        session([
            'impersonate_original_id' => $currentUser->id,
            'impersonated_user_id' => $user->id,
            'impersonation_started_at' => time()
        ]);

        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Now impersonating ' . $user->name);
    }

    public function leave(Request $request)
    {
        if (session()->has('impersonate_original_id')) {
            $originalId = session('impersonate_original_id');
            $userId = Auth::id();

            ImpersonationLog::create([
                'admin_id' => $originalId,
                'user_id' => $userId,
                'action' => 'stopped',
                'ip_address' => $request->ip(),
            ]);

            Auth::loginUsingId($originalId);

            session()->forget(['impersonate_original_id', 'impersonated_user_id', 'impersonation_started_at']);

            return redirect('/users')->with('success', 'Back to your admin account');
        }

        return redirect('/dashboard');
    }
}