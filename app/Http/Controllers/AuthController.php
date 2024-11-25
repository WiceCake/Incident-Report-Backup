<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validate = $request->validate([
            "username" => "required",
            "password" => "required",
        ]);

        $username = $request->username;
        $password = $request->password;


        if (Auth::attempt(['username' => $username, 'password' => $password])) {
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        return redirect()->back()->withErrors([
            'not_found' => 'No credentials found in our database!'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
