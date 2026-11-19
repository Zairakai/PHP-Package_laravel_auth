<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * @param Request $request
     */
    public function toResponse(mixed $request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', Response::HTTP_CREATED);
        }

        return redirect()->intended(Fortify::redirects('register'));
    }
}
