<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller {

    public function login(Request $request) {
        $this->validate($request, [
            'name' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('name', $request->name)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            $user->refreshApiToken();
            return $user;
        }
        return ['error' => 'wrong_password'];
    }

    public function register(Request $request) {
        $this->validate($request, [
            'name' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        $request->merge(['password' => Hash::make($request->password)]);
        $user = User::create($request->all());
        $user->refreshApiToken();
        return $user;
    }

}