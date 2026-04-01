<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        Model::preventLazyLoading(! app()->isProduction());


        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by($request->user()->id)
                : Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });


        Builder::macro('withIncludes', function (
            array $allowed,
            ?Request $request = null
        ): Builder {
            $request = $request ?? request();

            $requested = $request->has('include')
                ? explode(',', $request->query('include'))
                : [];

            $valid = array_intersect($requested, $allowed);

            if (filled($valid)) {
                $this->with($valid);
            }

            return $this;
        });

        Builder::macro('withSorting', function (
            array $allowed,
            string $default = '-created_at',
            ?Request $request = null
        ): Builder {
            $request = $request ?? request();
            $sortParam = $request->query('sort', $default);
            $fields = explode(',', $sortParam);

            foreach ($fields as $field) {
                $direction = 'asc';

                if (str_starts_with($field, '-')) {
                    $direction = 'desc';
                    $field = substr($field, 1);
                }

                if (in_array($field, $allowed)) {
                    $this->orderBy($field, $direction);
                }
            }

            return $this;
        });
    }
}
