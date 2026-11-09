<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function register()
    {
        $roles = Role::registrable();

        return view('auth.register', compact('roles'));
    }

    public function storeUser(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'role' => ['required', Rule::enum(Role::class)->only(Role::registrable())],
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required',
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'join_date' => now(),
                'role' => $request->role,
                'password' => Hash::make($request->password),
            ]);

            return redirect()->route('login')->with('success', 'Account created. You can now log in.');
        } catch (QueryException $e) {
            Log::error('Creating user: '.$e->getMessage());

            return back()->with('error', "Couldn't create your account. Please try again.");
        }
    }
}
