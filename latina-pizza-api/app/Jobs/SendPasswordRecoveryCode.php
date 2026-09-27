<?php

namespace App\Jobs;

use App\Services\PasswordRecovery;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPasswordRecoveryCode implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 30;

    public function __construct(public string $email, public int $requestedAt)
    {
        $this->onQueue('password-recovery');
    }

    public function handle(PasswordRecovery $recovery): void
    {
        // Drop stale queued requests after an outage.
        if (now()->timestamp - $this->requestedAt < 600) {
            $recovery->send($this->email);
        }
    }
}
