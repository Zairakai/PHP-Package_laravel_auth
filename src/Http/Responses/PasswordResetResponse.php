<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetResponse implements PasswordResetResponseContract
{
    public function __construct(
        protected string $status = 'passwords.reset',
    ) {}

    /**
     * @param Request $request
     */
    public function toResponse(mixed $request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['message' => trans($this->status)]);
        }

        return redirect(
            Fortify::redirects(
                'password-reset',
                (bool) config('fortify.views', true) ? route('login') : null,
            ),
        )->with('status', trans($this->status));
    }
}
