<?php

namespace Russsiq\GRecaptcha\Support;

// Сторонние зависимости.
use Illuminate\Support\Manager;
use Russsiq\GRecaptcha\Contracts\GRecaptchaContract;
use Russsiq\GRecaptcha\Support\Drivers\GoogleV3Driver;
use Russsiq\GRecaptcha\Support\Drivers\ImageCodeDriver;
use Russsiq\GRecaptcha\Support\Drivers\NullableDriver;

/**
 * Менеджер, управляющий созданием Валидатора капчи,
 * и предоставляющий доступ к его публичным методам.
 */
class GRecaptchaManager extends Manager
{
    /**
     * Драйвер, используемый по умолчанию.
     * @var string
     */
    protected $defaultCaptcha = 'google_v3';

    /**
     * Получить имя драйвера, используемого по умолчанию.
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('g_recaptcha.used', true)
            ? $this->config->get('g_recaptcha.driver', $this->defaultCaptcha)
            : 'nullable';
    }

    /**
     * Задать имя драйвера репозитория, используемого по умолчанию.
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver(string $name): void
    {
        $this->config->set('g_recaptcha.driver', $name);
    }

    /**
     * Создать экземпляр Валидатора капчи
     * с использованием драйвера GoogleV3.
     * @return GRecaptchaContract
     */
    protected function createGoogleV3Driver(): GRecaptchaContract
    {
        $config = $this->getDriverConfig('google_v3');

        return new GoogleV3Driver(
            $this->container,
            $config
        );
    }

    /**
     * Создать экземпляр Валидатора капчи
     * с использованием изображения с числом.
     * @return GRecaptchaContract
     */
    protected function createImageCodeDriver(): GRecaptchaContract
    {
        $config = $this->getDriverConfig('image_code');

        return new ImageCodeDriver(
            $this->container,
            $config
        );
    }

    /**
     * Создать экземпляр заглушки Валидатора капчи.
     * @return GRecaptchaContract
     */
    protected function createNullableDriver(): GRecaptchaContract
    {
        $config = $this->getDriverConfig('nullable');

        return new NullableDriver(
            $this->container,
            $config
        );
    }

    /**
     * Получить конфигурацию Валидатора
     * в соответствии с выбранным драйвером.
     * @param  string  $driver
     * @return array
     */
    protected function getDriverConfig(string $driver): array
    {
        // Получаем массив всех настроек Валидатора.
        $config = $this->config->get('g_recaptcha', []);

        // Пробрасываем уровнем выше настройки выбранного драйвера.
        $config = array_merge($config, $config['drivers'][$driver] ?? []);

        // Удаляем массив со списком всех драйверов.
        unset($config['drivers']);

        return $config;
    }
}
