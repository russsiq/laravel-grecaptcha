<?php

namespace Russsiq\GRecaptcha;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

use Russsiq\GRecaptcha\Support\GRecaptcha;

class GRecaptchaServiceProvider extends ServiceProvider
{
    /**
     * Путь до директории с исходниками.
     *
     * @var string
     */
    const SOURCE_DIR = __DIR__.'/../';

    /**
     * Все синглтоны (одиночки) контейнера,
     * которые должны быть зарегистрированы.
     *
     * @var array
     */
    public $singletons = [
        'g_recaptcha' => GRecaptcha::class,
    ];

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Определить директивы шаблонизатора Blade.
        $this->defineGRecaptchaBladeDirective();

        // Определить Расширение для валидатора.
        $this->defineGRecaptchaValidator();

        // Загрузка файлов Расширения.
        $this->loadGRecaptchaFiles();

        // Действия, выполнение которых может быть только из консоли.
        if ($this->app->runningInConsole()) {
            // Публикация ресурсов.
            $this->publishGRecaptchaFiles();
        }
    }

    public function provides()
    {
        return [
            'g_recaptcha',
        ];
    }

    /**
     * Определить директивы шаблонизатора Blade.
     *
     * @return void
     */
    protected function defineGRecaptchaBladeDirective()
    {
        Blade::directive('g_recaptcha_input', function ($expression) {
            return "<?php echo app('g_recaptcha')->input($expression); ?>";
        });

        Blade::directive('g_recaptcha_script', function ($expression) {
            return "<?php echo app('g_recaptcha')->script($expression); ?>";
        });
    }

    /**
     * Определить Расширение для валидатора.
     *
     * @return void
     */
    protected function defineGRecaptchaValidator()
    {
        $this->app->validator->extendImplicit('g_recaptcha',
            function ($attribute, $value, $parameters, $validator) {
                return app('g_recaptcha')->verifying();
            }, trans('g_recaptcha::g_recaptcha.messages.fails')
        );
    }

    /**
     * Загрузка файлов Расширения.
     *
     * @return void
     */
    protected function loadGRecaptchaFiles()
    {
        $this->loadTranslationsFrom(self::SOURCE_DIR.'resources/lang', 'g_recaptcha');
        $this->loadViewsFrom(self::SOURCE_DIR.'resources/views/components/partials', 'g_recaptcha');
    }

    /**
     * Публикация файлов Расширения.
     * `php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider"`
     *
     * @return void
     */
    protected function publishGRecaptchaFiles()
    {
        // php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider" --tag=config --force
        $this->publishes([
            self::SOURCE_DIR.'config/g_recaptcha.php' => config_path('g_recaptcha.php'),
        ], 'config');

        // php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider" --tag=lang --force
        $this->publishes([
            self::SOURCE_DIR.'resources/lang' => resource_path('lang/vendor/g_recaptcha'),
        ], 'lang');

        // php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider" --tag=views --force
        $this->publishes([
            self::SOURCE_DIR.'resources/views' => resource_path('views/vendor/g_recaptcha'),
        ], 'views');
    }
}
