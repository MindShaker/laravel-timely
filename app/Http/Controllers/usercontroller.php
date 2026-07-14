<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get()->map(fn ($u) => [
            'id'            => $u->id,
            'name'          => $u->name,
            'email'         => $u->email,
            'tipo'          => $u->tipo,
            'hora_entrada'  => $u->hora_entrada,
            'hora_saida'    => $u->hora_saida,
            'inicio_almoco' => $u->inicio_almoco,
            'birthdate_formatted' => $u->birthdate ? $u->birthdate->format('d/m') : null,
        ]);

        return Inertia::render('Admin/Users/Index', compact('users'));
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Form');
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
        return Inertia::render('Admin/Users/Form', [
            'user' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'tipo'          => $user->tipo,
                'hora_entrada'  => $user->hora_entrada,
                'hora_saida'    => $user->hora_saida,
                'inicio_almoco' => $user->inicio_almoco,
                'birthdate_raw' => $user->birthdate ? $user->birthdate->format('Y-m-d') : '',
            ],
        ]);
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
