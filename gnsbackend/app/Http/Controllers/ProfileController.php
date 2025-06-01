<?php

namespace App\Http\Controllers;

use App\Models\Historic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index()
    {
        $user = User::where('id', auth()->id())->first();
        // Conta os erros do Historic do usuário
        $counttotalErros = Historic::where('status', 'error')->where('user_id', auth()->id())->count();

        // dd($usuario);
        return view('pages.profile',
            [
                'user' => $user,
                'counttotalErros' => $counttotalErros
            ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'number' => 'nullable|string',
        ]);

        $user->update($validated);

        return redirect()->route('page.profile')->with('success', 'Usuário atualizado!');
    }

    public function updatePassword(Request $request)
    {
        // $user = auth()->user();
        // dd(Hash::check($request->current_password, $user->password), $user->password, $request->all());
        $data = $request->all();

        if (!Hash::check($data['current_password'], auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Senha atual incorreta.']);
        }

        $validator = \Validator::make($data, [
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        auth()->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('page.profile')->with('success', 'Senha alterada com sucesso!');

        $user = auth()->user();

        // Verifica se a senha atual está correto
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'A senha atual está incorreta!'
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('page.profile')->with('success', 'Senha atualizada com sucesso!');
    }
}
