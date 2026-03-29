<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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
