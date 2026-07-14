<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.edit')
            ->with('success', 'Perfil atualizado com sucesso.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        // o cast "hashed" do model User cuida do hash
        $request->user()->update(['password' => $request->validated('password')]);

        return redirect()->route('profile.edit')
            ->with('success', 'Senha alterada com sucesso.');
    }
}
