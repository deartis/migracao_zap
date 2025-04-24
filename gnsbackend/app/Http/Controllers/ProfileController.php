<?php

namespace App\Http\Controllers;

use App\Models\User;

class ProfileController extends Controller
{
    public function index()
    {
        $user = User::where('id', auth()->id())->first();
        //dd($usuario);
        return view('pages.profile', compact("user"));
    }
}
