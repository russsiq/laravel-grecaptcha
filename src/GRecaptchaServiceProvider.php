<?php

namespace Russsiq\GRecaptcha;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class GRecaptchaServiceProvider extends ServiceProvider
{
    protected $defer = false;
    
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/lang', 'g_recaptcha');
        $this->loadViewsFrom(__DIR__ . '/views/components/partials', 'g_recaptcha');
        $this->publishes([
            __DIR__ . '/config/g_recaptcha.php' => config_path('g_recaptcha.php'),
        ], 'config');
        
        Blade::directive('g_recaptcha_input', function ($expression) {
            return "<?php echo app('g_recaptcha')->input($expression); ?>";
        });
        
        Blade::directive('g_recaptcha_script', function ($expression) {
            return "<?php echo app('g_recaptcha')->script($expression); ?>";
        });
        
        $this->app->validator->extendImplicit('g_recaptcha',
            function ($attribute, $value, $parameters, $validator) {
                return app('g_recaptcha')->verifying();
            }, trans('g_recaptcha::fails')
        );
    }
    
    public function register()
    {
        $this->app->singleton('g_recaptcha', function () {
            return new GRecaptcha(config('g_recaptcha'),
                $this->app->request->input('g-recaptcha-response', null)
            );
        });
    }
    
    public function provides()
    {
        return [
            'grecaptcha',
        ];
    }
}
