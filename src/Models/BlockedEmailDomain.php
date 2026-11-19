<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Zairakai\LaravelAuth\Observers\BlockedEmailDomainObserver;

/**
 * @property int                     $id
 * @property string                  $domain            the domain as it was given
 * @property string                  $normalized_domain the domain in lowercase and in ASCII (punycode), the one that is compared
 * @property array<int, string>|null $indicators        why the domain is blocked (keys of laravel-auth::indicators)
 * @property array<int, string>      $indicator_labels  the indicators in the language of the application
 */
#[ObservedBy(BlockedEmailDomainObserver::class)]
class BlockedEmailDomain extends Model
{
    protected $fillable = [
        'domain',
        'indicators',
    ];

    public function usesTimestamps(): bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'indicators' => 'array',
        ];
    }

    protected function indicatorLabels(): Attribute
    {
        return Attribute::get(fn (): array => array_map(
            static fn (string $indicator): string => Lang::has('laravel-auth::indicators.' . $indicator)
                ? Lang::string('laravel-auth::indicators.' . $indicator)
                : $indicator,
            $this->indicators ?? [],
        ));
    }
}
