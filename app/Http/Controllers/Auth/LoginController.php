<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /** login page */
    public function login()
    {
        return view('auth.login');
    }

    /** sign the user in; views read the user from auth()->user(), not the session */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->with('error', 'Incorrect email or password.');
        }

        if ($request->user()->status !== UserStatus::Active) {
            Auth::logout();

            return back()->withInput($request->only('email'))->with('error', 'Your account is inactive. Please contact the school office.');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'Welcome back!');
    }

    /** sign the user out and drop the whole session */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', "You've been logged out.");
    }
}
