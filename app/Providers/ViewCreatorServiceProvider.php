<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\ViewCreators\ViewSwitchCreator;

class ViewCreatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        View::creator('*', ViewSwitchCreator::class);
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        //
    }
}
