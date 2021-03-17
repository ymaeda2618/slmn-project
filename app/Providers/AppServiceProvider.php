<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Builder::macro('if', function (bool $condition, callable $func) {
            return $condition ? $func($this) : $this;
        });
        Builder::macro('whereIf', function (bool $condition, $column, $operator = null, $value = null, $boolean = 'and') {
            return $condition ? $this->where($column, $operator, $value, $boolean) : $this;
        });
    }
}
