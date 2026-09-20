<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        $emailChanged = $request->user()->isDirty('email');
        if ($emailChanged) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if ($emailChanged) {
            $this->revokeApiTokens($request->user()->id);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::route('login')->with('status', 'Correo actualizado. Inicie sesión nuevamente.');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->role === 'admin') {
            return Redirect::route('profile.edit')->withErrors([
                'userDeletion' => 'Una cuenta administrativa debe ser retirada por otro administrador.',
            ]);
        }

        $hasOrders = Schema::hasTable('pedidos')
            && DB::table('pedidos')->where('user_id', $user->id)->exists();
        $this->revokeApiTokens($user->id);

        Auth::logout();

        if ($hasOrders) {
            if (Schema::hasTable('direcciones_usuario')) {
                DB::table('direcciones_usuario')->where('user_id', $user->id)->delete();
            }
            $user->forceFill([
                'name' => 'Cliente eliminado',
                'email' => "deleted+{$user->id}+".now()->timestamp.'@example.invalid',
                'password' => bcrypt(str()->random(64)),
                'email_verified_at' => null,
                'remember_token' => null,
            ])->save();
        } else {
            $user->delete();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function revokeApiTokens(int $userId): void
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return;
        }
        DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $userId)
            ->delete();
    }
}
