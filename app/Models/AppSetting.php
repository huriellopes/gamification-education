<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Configuração global chave-valor da plataforma. Os acessos são cacheados
 * (site público é alto tráfego) e invalidados na escrita.
 *
 * @property string $key
 * @property string|null $value
 */
#[Fillable(['key', 'value'])]
class AppSetting extends Model
{
    private const CACHE_PREFIX = 'setting:';

    protected $table = 'settings';

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = Cache::rememberForever(
            self::CACHE_PREFIX . $key,
            fn (): ?string => self::query()->where('key', $key)->value('value'),
        );

        return $value ?? $default;
    }

    public static function put(string $key, string $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_PREFIX . $key);
    }

    public static function bool(string $key, bool $default = false): bool
    {
        return filter_var(self::get($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOL);
    }
}
