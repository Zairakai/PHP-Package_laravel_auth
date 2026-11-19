<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    /**
     * @param Request $request
     */
    public function toResponse(mixed $request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', Response::HTTP_NO_CONTENT);
        }

        return redirect()->intended(Fortify::redirects('email-verification') . '?verified=1');
    }
}
