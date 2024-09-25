<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function login(Request $request){
        $user = User::all()->first();


        if(Auth::attempt(['username' => 'dcasipong', 'password' => 'dan123'])){
            dd('working');
        }
        dd($user);
    }
}
