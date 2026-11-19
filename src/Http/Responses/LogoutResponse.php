<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class LogoutResponse implements LogoutResponseContract
{
    /**
     * @param Request $request
     */
    public function toResponse(mixed $request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }

        return redirect(Fortify::redirects('logout', '/'));
    }
}
