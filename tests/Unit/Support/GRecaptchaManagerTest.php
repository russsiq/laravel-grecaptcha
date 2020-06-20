<?php declare(strict_types=1);

namespace Tests\Unit\Support;

// Тестируемый класс.
use Russsiq\GRecaptcha\Support\GRecaptchaManager;

// Сторонние зависимости.
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;

// Библиотеки тестирования.
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Russsiq\GRecaptcha\Support\GRecaptchaManager
 */
class GRecaptchaManagerTest extends TestCase
{
    /**
     * Экземпляр менеджера.
     * @var GRecaptchaManager
     */
    private $manager;

    /**
     * @test
     * @covers ::__construct
     *
     * Экземпляр менеджера успешно создан.
     * @return void
     */
    public function testSuccessfullyInitiated(): void
    {
        $manager = $this->createManager();

        $this->assertInstanceOf(GRecaptchaManager::class, $manager);
    }

    /**
     * @test
     * @covers ::getDefaultDriver
     *
     * Получить имя драйвера, используемого по умолчанию.
     * @return void
     *
     * @FIX https://github.com/russsiq/laravel-grecaptcha/issues/2
     */
    public function testGetDefaultDriver(): void
    {
        // Создать заглушку для класса Container.
        $container = $this->createMock(Container::class);

        // Настроить заглушку.
        $container->method('make')
            ->will($this->returnCallback(function ($argument) {
                if ('config' !== $argument) {
                    $this->markTestSkipped(
                        'Заглушка Контейнера служб настроена только на извлечение (`make`) с абстракцией `config`.'
                    );
                }

                // Создать карту аргументов для возврата значений
                $map = [
                    [
                        'g_recaptcha', [
                            'used' => true,
                            'driver' => '',
                        ]
                    ]
                ];

                // Создать заглушку для класса ConfigRepository.
                $config = $this->createMock(ConfigRepository::class);

                // Настроить заглушку.
                $config->method('get')
                    ->will($this->returnValueMap($map));

                return $config;
            }));

        $manager = $this->createManager($container);

        $this->assertInstanceOf(GRecaptchaManager::class, $manager);

        $defaultDriver = $manager->getDefaultDriver();

        $this->assertIsString($defaultDriver, 'Драйвер по умолчанию может иметь только строкове значение.');
        $this->assertContains($defaultDriver, [
            'nullable',
            'image_code',
            'google_v3',

        ], 'Драйвер по умолчанию может иметь только одно из значений: `nullable`, `image_code`, `google_v3`.');
    }

    /**
     * [createManager description]
     * @param  Container|null  $container
     * @return GRecaptchaManager
     */
    protected function createManager(Container $container = null): GRecaptchaManager
    {
        if (is_null($container)) {
            $container = $this->getMockBuilder(Container::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        return new GRecaptchaManager($container);
    }
}
