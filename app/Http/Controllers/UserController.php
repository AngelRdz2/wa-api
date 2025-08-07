<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller{

    public function index(){
        $users = User::with('roles')->paginate(10);
        $roles = Role::all();
        return view('auth.users.users', compact('users',  'roles'));
    }
}
