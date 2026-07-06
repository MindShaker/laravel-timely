<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('admin.users', compact('users'));
    }

    public function create()
    {
        return view('admin.user-form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'password'      => 'required|min:8',
            'tipo'          => 'required|in:user,admin',
            'hora_entrada'  => 'required',
            'inicio_almoco' => 'required',
            'hora_saida'    => 'required',
            'birthdate'     => 'nullable|date',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->route('admin.users')->with('success', 'Utilizador criado com sucesso.');
    }

    public function edit(User $user)
    {
        return view('admin.user-form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => "required|email|unique:users,email,{$user->id}",
            'tipo'          => 'required|in:user,admin',
            'hora_entrada'  => 'required',
            'inicio_almoco' => 'required',
            'hora_saida'    => 'required',
            'birthdate'     => 'nullable|date',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()->route('admin.users')->with('success', 'Utilizador atualizado com sucesso.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'Utilizador eliminado.');
    }
}
