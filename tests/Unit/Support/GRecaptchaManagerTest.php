<?php declare(strict_types=1);

namespace Tests\Unit\Support;

// Тестируемый класс.
use Russsiq\GRecaptcha\Support\GRecaptchaManager;

// Сторонние зависимости.
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
