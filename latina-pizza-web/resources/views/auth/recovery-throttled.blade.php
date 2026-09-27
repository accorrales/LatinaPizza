<x-guest-layout>
    <h2 class="text-3xl font-bold text-slate-900">Esperá un momento</h2>
    <p role="alert" class="mt-4 text-sm leading-6 text-slate-600">Recibimos varios intentos seguidos. Por seguridad, esperá un minuto antes de volver a intentar.</p>
    <a href="{{ session('recovery_email') ? route('password.reset') : route('password.request') }}" class="mt-6 inline-block font-semibold text-blue-700">Volver a recuperar mi cuenta</a>
</x-guest-layout>
