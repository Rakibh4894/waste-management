<?php

namespace App\Providers;


use App\Models\UserConfig;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Debugbar', \Barryvdh\Debugbar\Facades\Debugbar::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Toastr::useVite();

        View::composer('*', function ($view) {
            if (Auth::check()){
                $darkMode = UserConfig::where('user_id',Auth::user()->id)->where('type','dark_mode_on_off')->first(['value']);
                $view->with('darkMode', $darkMode->value??'light');
            }else{
                $view->with('darkMode', 'light');
            }

        });
    }
}
