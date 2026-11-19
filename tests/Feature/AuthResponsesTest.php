<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Feature;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Zairakai\LaravelAuth\Http\Responses\LoginResponse;
use Zairakai\LaravelAuth\Http\Responses\LogoutResponse;
use Zairakai\LaravelAuth\Http\Responses\PasswordResetResponse;
use Zairakai\LaravelAuth\Http\Responses\RegisterResponse;
use Zairakai\LaravelAuth\Http\Responses\TwoFactorLoginResponse;
use Zairakai\LaravelAuth\Http\Responses\VerifyEmailResponse;
use Zairakai\LaravelAuth\Tests\TestCase;

final class AuthResponsesTest extends TestCase
{
    #[Test]
    public function it_answers_a_json_request_with_json(): void
    {
        $login = new LoginResponse()->toResponse($this->request(json: true));

        self::assertSame(Response::HTTP_OK, $login->getStatusCode());
        self::assertSame(['two_factor' => false, 'intended' => null], json_decode((string) $login->getContent(), true));

        self::assertSame(Response::HTTP_NO_CONTENT, new LogoutResponse()->toResponse($this->request(json: true))->getStatusCode());
        self::assertSame(Response::HTTP_CREATED, new RegisterResponse()->toResponse($this->request(json: true))->getStatusCode());
        self::assertSame(Response::HTTP_NO_CONTENT, new TwoFactorLoginResponse()->toResponse($this->request(json: true))->getStatusCode());
        self::assertSame(Response::HTTP_NO_CONTENT, new VerifyEmailResponse()->toResponse($this->request(json: true))->getStatusCode());
    }

    #[Test]
    public function it_redirects_a_logout_to_the_root_by_default(): void
    {
        self::assertSame('http://localhost', $this->target(new LogoutResponse()->toResponse($this->request())));
    }

    #[Test]
    public function it_redirects_a_password_reset_with_its_status(): void
    {
        config(['fortify.redirects.password-reset' => '/after-reset']);

        $response = new PasswordResetResponse('passwords.reset')->toResponse($this->request());

        self::assertSame('http://localhost/after-reset', $this->target($response));
        self::assertSame(trans('passwords.reset'), $response instanceof RedirectResponse ? $response->getSession()?->get('status') : null);
    }

    #[Test]
    public function it_redirects_an_html_request_to_the_configured_targets(): void
    {
        config([
            'fortify.redirects.login'              => '/after-login',
            'fortify.redirects.register'           => '/after-register',
            'fortify.redirects.email-verification' => '/after-verify',
        ]);

        self::assertSame('http://localhost/after-login', $this->target(new LoginResponse()->toResponse($this->request())));
        self::assertSame('http://localhost/after-register', $this->target(new RegisterResponse()->toResponse($this->request())));
        self::assertSame('http://localhost/after-login', $this->target(new TwoFactorLoginResponse()->toResponse($this->request())));
        self::assertSame(
            'http://localhost/after-verify?verified=1',
            $this->target(new VerifyEmailResponse()->toResponse($this->request())),
        );
    }

    #[Test]
    public function it_returns_the_message_of_the_password_reset_as_json(): void
    {
        $response = new PasswordResetResponse('passwords.reset')->toResponse($this->request(json: true));

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(['message' => trans('passwords.reset')], json_decode((string) $response->getContent(), true));
    }

    private function request(bool $json = false): Request
    {
        $request = Request::create('/', 'POST', server: $json ? ['HTTP_ACCEPT' => 'application/json'] : []);
        $request->setLaravelSession(resolve(Session::class));

        return $request;
    }

    private function target(Response $response): string
    {
        self::assertInstanceOf(RedirectResponse::class, $response);

        return $response->getTargetUrl();
    }
}
