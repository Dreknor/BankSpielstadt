<?php

namespace App\Exceptions;

use App\Services\PushService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // Push-Benachrichtigung an alle Admin-Abonnenten senden
            try {
                /** @var PushService $push */
                $push = app(PushService::class);
                $push->senden(
                    '🔴 Fehler in der Bank-App',
                    get_class($e) . ': ' . mb_substr($e->getMessage(), 0, 200),
                    '/admin/logs'
                );
            } catch (\Throwable $pushFehler) {
                // Push-Fehler nicht eskalieren
            }
        });
    }
}
