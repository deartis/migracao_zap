<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController
{

    public function index()
    {
        $users = User::latest()->paginate(10); // Paginação opcional
        return view('pages.adm.user', compact('users'));
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

// Exibe um usuário específico (GET /users/{user})
    public function show(User $user)
    {
        return view('pages.adm.user.show', compact('user'));
    }

    // Exibe o formulário de edição (GET /users/{user}/edit)
    public function edit(User $user)
    {
        return view('pages.adm.user.edit', compact('user'));
    }

    // Atualiza um usuário (PUT/PATCH /users/{user})
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'number' => 'nullable|string',
            'msgLimit' => 'nullable|integer',
            'role' => 'required|in:nu,admin',
            'enabled' => 'boolean',
        ]);

        // Atualiza o usuário
        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Usuário atualizado!');
    }

    // Remove um usuário (DELETE /users/{user})
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuário excluído!');
    }

    // Rota adicional: Ativar/Desativar usuário (PATCH /users/{user}/toggle-status)
    public function toggleStatus(User $user)
    {
        $user->update(['enabled' => !$user->enabled]);
        return back()->with('success', 'Status alterado!');
    }

}
