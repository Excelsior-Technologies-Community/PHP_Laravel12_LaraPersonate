<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $type = $request->type;

        $users = User::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($type, function ($query) use ($type) {
                if ($type == 'admin') {
                    $query->where('is_admin', 1);
                }
                if ($type == 'user') {
                    $query->where('is_admin', 0);
                }
            })
            ->oldest()
            ->paginate(3)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.partials.user-rows', compact('users'))->render(),
                'pagination' => view('pagination::tailwind', ['paginator' => $users])->render()
            ]);
        }

        return view('admin.users', compact('users'));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() == $user->id) {
            return back()->with(
                'error',
                'You cannot delete yourself'
            );
        }

        $user->delete();

        return back()->with(
            'success',
            'User deleted successfully'
        );
    }
}