<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController
{

    public function index()
    {
        return view('pages.adm.user');
    }

    public function canSend(Request $request)
    {

        try {
            $user = $request->user();

            return response()->json([
                'send' => true, // Temporário para testes
                'test_debug' => [
                    'user_id' => $user->id,
                    'auth' => $user ? 'authenticated' : 'not authenticated'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     *  |=================================================
     *  |Função para página de registro de usuário
     *  |=================================================
     */
    public function registerUser()
    {
        return view('pages.adm.create-user');
    }

    public function store(Request $request){

        $inpt = $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:nu,admin',
            'msgLimit' => 'required|integer',
            'number' => 'required|string|min:8|max:15',

        ]);

        User::create([
            'name' => $inpt['name'],
            'email' => $inpt['email'],
            'password' => bcrypt($inpt['password']),
            'role' => $inpt['role'],
            'msgLimit' => $inpt['msgLimit'],
            'number' => $inpt['number'],
            'sendedMsg' => 0,
            'enabled' => false,
            'rightNumber' => false,
            'lastMessage' => null,
        ]);
        return redirect()->route('adm.user')->with('success', 'Usuário criado com sucesso!');
    }

}
