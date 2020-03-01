<?php

namespace Russsiq\GRecaptcha\Support;

// Сторонние зависимости.
use Illuminate\Support\Manager;
use Russsiq\GRecaptcha\Contracts\GRecaptchaContract;
use Russsiq\GRecaptcha\Support\Drivers\GoogleV3Driver;

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
        return $this->config->get('g_recaptcha.driver', $this->defaultCaptcha);
    }

    /**
     * Задать имя драйвера репозитория, используемого по умолчанию.
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver(string $name)
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
        $config = $this->getMasterConfig('google_v3');

        return new GoogleV3Driver(
            $this->container,
            $config
        );
    }

    /**
     * Получить конфигурацию Мастера обновлений
     * в соответствии с выбранным драйвером.
     * @param  string  $driver
     * @return array
     */
    protected function getMasterConfig(string $driver): array
    {
        // Получаем массив всех настроек Мастера обновлений.
        $config = $this->config->get('g_recaptcha', []);

        // Пробрасываем уровнем выше настройки выбранного драйвера.
        $config = array_merge($config, $config['drivers'][$driver] ?? []);

        // Удаляем массив со списком всех драйверов.
        unset($config['drivers']);

        return $config;
    }
}
