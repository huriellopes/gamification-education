<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Env;
use Illuminate\Support\Facades\Request;

class AppVersion
{
    private static ?string $version = null;

    /**
     * Versão atual da aplicação (fonte: arquivo VERSION na raiz).
     *
     * Lida diretamente do arquivo (memoizada por processo) para refletir
     * sempre a versão do código implantado, sem sofrer com o config:cache.
     * Pode ser sobrescrita por APP_VERSION em ambientes específicos.
     */
    public static function current(): string
    {
        if (self::$version !== null) {
            return self::$version;
        }

        $fromEnv = Request::server('APP_VERSION') ?? Env::get('APP_VERSION');

        if (is_string($fromEnv) && $fromEnv !== '') {
            return self::$version = $fromEnv;
        }

        $path = base_path('VERSION');
        $fromFile = is_file($path) ? mb_trim((string) file_get_contents($path)) : '';

        return self::$version = ($fromFile !== '' ? $fromFile : '0.0.0');
    }
}
