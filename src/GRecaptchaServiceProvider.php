<?php

namespace Russsiq\GRecaptcha;

// Зарегистрированные фасады приложения.
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;

// Сторонние зависимости.
use Illuminate\Support\ServiceProvider;
use Russsiq\GRecaptcha\Support\GRecaptchaManager;

/**
 * Поставщик службы GRecaptcha.
 */
class GRecaptchaServiceProvider extends ServiceProvider
{
    /**
     * Путь до директории с исходниками.
     * @const string
     */
    const SOURCE_DIR = __DIR__.'/../';

    /**
     * Все синглтоны (одиночки) контейнера,
     * которые должны быть зарегистрированы.
     * @var array
     */
    public $singletons = [
        'g_recaptcha' => GRecaptchaManager::class,

    ];

    /**
     * Загрузка служб приложения.
     * @return void
     */
    public function boot(): void
    {
        // Загрузка файлов Расширения.
        $this->loadGRecaptchaFiles();

        // Определить директивы шаблонизатора Blade.
        $this->defineGRecaptchaBladeDirective();

        // Определить Расширение для валидатора.
        $this->defineGRecaptchaValidator();

        // Действия, выполнение которых может быть только из консоли.
        if ($this->app->runningInConsole()) {
            // Публикация ресурсов.
            $this->publishGRecaptchaFiles();
        }
    }

    /**
     * Получить полный путь к файлу или директории,
     * которые расположены в директории исходников.
     * @param  string  $path
     * @return string
     */
    protected function sourcePath(string $path): string
    {
        return self::SOURCE_DIR.$path;
    }

    /**
     * Определить директивы шаблонизатора Blade.
     * @return void
     */
    protected function defineGRecaptchaBladeDirective(): void
    {
        Blade::directive('g_recaptcha_input', function ($expression) {
            return "<?php echo app('g_recaptcha')->input({$expression}); ?>";
        });

        Blade::directive('g_recaptcha_script', function ($expression) {
            return "<?php echo app('g_recaptcha')->script({$expression}); ?>";
        });
    }

    /**
     * Определить Расширение для валидатора.
     * @return void
     */
    protected function defineGRecaptchaValidator(): void
    {
        Validator::extendImplicit('g_recaptcha', GRecaptchaManager::class);
    }

    /**
     * Загрузка файлов Расширения.
     * @return void
     */
    protected function loadGRecaptchaFiles(): void
    {
        $this->loadRoutesFrom($this->sourcePath('routes/web.php'));
        $this->loadTranslationsFrom($this->sourcePath('resources/lang'), 'g_recaptcha');
        $this->loadViewsFrom($this->sourcePath('resources/views/components/partials'), 'g_recaptcha');
    }

    /**
     * Публикация файлов Расширения.
     * `php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider"`
     * @return void
     */
    protected function publishGRecaptchaFiles(): void
    {
        // php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider" --tag=config --force
        $this->publishes([
            $this->sourcePath('config/g_recaptcha.php') => config_path('g_recaptcha.php'),
        ], 'config');

        // php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider" --tag=fonts --force
        $this->publishes([
            $this->sourcePath('resources/fonts') => public_path('vendor/g_recaptcha/fonts'),
        ], 'fonts');

        // php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider" --tag=lang --force
        $this->publishes([
            $this->sourcePath('resources/lang') => resource_path('lang/vendor/g_recaptcha'),
        ], 'lang');

        // php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider" --tag=views --force
        $this->publishes([
            $this->sourcePath('resources/views') => resource_path('views/vendor/g_recaptcha'),
        ], 'views');
    }
}
