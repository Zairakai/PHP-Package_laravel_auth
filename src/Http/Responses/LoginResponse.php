<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * @param Request $request
     */
    public function toResponse(mixed $request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse([
                'two_factor' => false,
                'intended'   => $request->session()->pull('url.intended'),
            ]);
        }

        return redirect()->intended(Fortify::redirects('login'));
    }
}
