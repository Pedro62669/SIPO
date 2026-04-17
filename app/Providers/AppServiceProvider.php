<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));
        setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'ptb');

        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
            // Cookie de sessão precisa ser Secure em HTTPS; se env não definir, o login “some” após o POST (302 para /login).
            if (config('session.secure') === null || config('session.secure') === '') {
                Config::set('session.secure', true);
            }
        }

        // Livewire: usa APP_URL_PATH_PREFIX quando definido; caso contrário, deriva a subpasta do APP_URL.
        $prefix = trim((string) config('app.url_path_prefix', ''), '/');
        if ($prefix === '') {
            $appUrlPath = trim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');
            $prefix = $appUrlPath;
        }
        $livewireBase = $prefix === '' ? '' : '/'.$prefix;

        Livewire::setScriptRoute(function ($handle) use ($livewireBase) {
            return Route::get($livewireBase.'/livewire/livewire.js', $handle);
        });

        Livewire::setUpdateRoute(function ($handle) use ($livewireBase) {
            return Route::post($livewireBase.'/livewire/update', $handle)
                ->middleware('web')
                ->name('custom.livewire.update');
        });
    }
}
