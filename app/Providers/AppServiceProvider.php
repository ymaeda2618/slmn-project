<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Http\ViewCreators\ViewSwitchCreator;

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
        // 明示的に default を指定
        Paginator::defaultView('vendor.pagination.default');

        // ビューを SP ブレードに自動切替
        View::creator('*', ViewSwitchCreator::class);

        Builder::macro('if', function (bool $condition, callable $func) {
            return $condition ? $func($this) : $this;
        });
        Builder::macro('whereIf', function (bool $condition, $column, $operator = null, $value = null, $boolean = 'and') {
            return $condition ? $this->where($column, $operator, $value, $boolean) : $this;
        });
    }
}
