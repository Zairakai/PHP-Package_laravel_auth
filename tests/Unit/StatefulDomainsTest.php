<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zairakai\LaravelAuth\Support\StatefulDomains;

final class StatefulDomainsTest extends TestCase
{
    #[Test]
    public function it_parses_stateful_domains_and_discards_empty_entries(): void
    {
        $domains = StatefulDomains::parse(' localhost, localhost:3000 ,,127.0.0.1 ');

        $this->assertSame(['localhost', 'localhost:3000', '127.0.0.1'], $domains);
    }

    #[Test]
    public function it_returns_an_empty_array_when_no_domains_are_defined(): void
    {
        $this->assertSame([], StatefulDomains::parse(''));
    }
}
