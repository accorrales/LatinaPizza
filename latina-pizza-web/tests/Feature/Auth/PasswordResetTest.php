<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('recovery screen offers a code and accessible email field', function () {
    $this->get('/forgot-password')->assertOk()->assertSee('Enviar código de recuperación')->assertSee('email-error');
});

test('request delegates normalized email to API and opens code screen', function () {
    Http::fake(['*/api/forgot-password' => Http::response(['status' => 'accepted'])]);
    $this->post('/forgot-password', ['email' => 'CLIENTE@EXAMPLE.COM'])
        ->assertRedirect(route('password.reset'))->assertSessionHas('recovery_email', 'cliente@example.com');
    Http::assertSent(fn ($request) => $request['email'] === 'cliente@example.com');
    $this->get('/reset-password')->assertOk()->assertSee('one-time-code')->assertSee('Reenviar código');
});

test('known and unknown addresses get the same web response', function () {
    Http::fake(['*/api/forgot-password' => Http::response(['status' => 'accepted'])]);
    $user = User::factory()->create();
    $this->post('/forgot-password', ['email' => $user->email])->assertRedirect(route('password.reset'));
    $status = session('status');
    $this->post('/forgot-password', ['email' => 'missing@example.com'])
        ->assertRedirect(route('password.reset'))->assertSessionHas('status', $status);
});

test('reset delegates to API and clears recovery session without logging in', function () {
    Http::fake(['*/api/reset-password' => Http::response(['status' => 'updated'])]);
    $this->withSession(['recovery_email' => 'client@example.com'])
        ->post('/reset-password', [
            'email' => 'client@example.com', 'code' => '012345',
            'password' => 'Mi nueva frase segura!', 'password_confirmation' => 'Mi nueva frase segura!',
        ])->assertRedirect(route('login'))->assertSessionMissing('recovery_email')->assertSessionHas('status');
    Http::assertSent(fn ($request) => $request['code'] === '012345'
        && $request['password_confirmation'] === 'Mi nueva frase segura!');
    $this->assertGuest();
});

test('invalid code shows API validation without flashing secrets', function () {
    Http::fake(['*/api/reset-password' => Http::response(['errors' => ['code' => ['El código no es válido o venció.']]], 422)]);
    $this->from('/reset-password')->withSession(['recovery_email' => 'client@example.com'])
        ->post('/reset-password', [
            'email' => 'client@example.com', 'code' => '012345',
            'password' => 'Mi nueva frase segura!', 'password_confirmation' => 'Mi nueva frase segura!',
        ])->assertRedirect('/reset-password')->assertSessionHasErrors('code')
        ->assertSessionMissing('_old_input.code')->assertSessionMissing('_old_input.password')
        ->assertSessionMissing('_old_input.password_confirmation');
});

test('API outage and throttling have actionable Spanish errors', function (int $status) {
    Http::fake(['*/api/forgot-password' => Http::response([], $status)]);
    $this->from('/forgot-password')->post('/forgot-password', ['email' => 'client@example.com'])
        ->assertRedirect('/forgot-password')->assertSessionHasErrors('email')->assertSessionMissing('recovery_email');
})->with([429, 503]);

test('connection failure preserves the form', function () {
    Http::fake(['*' => Http::failedConnection()]);
    $this->from('/forgot-password')->post('/forgot-password', ['email' => 'client@example.com'])
        ->assertRedirect('/forgot-password')->assertSessionHasErrors('email');
});

test('malformed fields never reach the API', function () {
    $this->post('/forgot-password', ['email' => ['invalid']])->assertSessionHasErrors('email');
    $this->post('/reset-password', [
        'email' => 'client@example.com', 'code' => 'abc',
        'password' => 'short', 'password_confirmation' => 'different',
    ])->assertSessionHasErrors(['code', 'password']);
    Http::assertNothingSent();
});

test('web requests have an IP limit independent of email', function () {
    Http::fake(['*' => Http::response(['status' => 'accepted'])]);
    for ($i = 0; $i < 5; $i++) {
        $this->post('/forgot-password', ['email' => "user{$i}@example.com"])->assertRedirect();
    }
    $this->post('/forgot-password', ['email' => 'another@example.com'])->assertStatus(429);
});

test('old reset links and direct visits guide users to request a code', function () {
    $this->get('/reset-password/legacy-token')->assertRedirect(route('password.request'));
    $this->get('/reset-password')->assertRedirect(route('password.request'));
});

test('existing web session cannot be used after password reset', function () {
    $user = User::factory()->create();
    $oldHash = $user->password;
    $user->forceFill(['password' => Hash::make('Changed by recovery!')])->save();
    $this->actingAs($user)->withSession(['password_hash_web' => $oldHash])
        ->get('/profile')->assertRedirect(route('login'));
    $this->assertGuest();
});
