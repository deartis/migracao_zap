<?php

namespace App\Http\Controllers;

use App\Models\Historic;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(){
        $historico = Historic::orderBy('created_at', 'desc')->paginate(10);
        return view('pages.home', compact('historico'));
    }
}
