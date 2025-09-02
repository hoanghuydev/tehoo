<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    //
    public function index()
    {
        return response()->json(['message' => 'Users']);
    }
    public function create(Request $request) 
    {
        return response()->json(['message' => 'Create User', 'data' => $request->all()]);
    }
    public function show(User $user)
    {
        return response()->json(['message' => 'Show User', 'user' => $user]);
    }
}
