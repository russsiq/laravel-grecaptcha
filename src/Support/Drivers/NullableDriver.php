<?php

namespace Russsiq\GRecaptcha\Support\Drivers;

// Сторонние зависимости.
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Contracts\Support\Renderable;
use Russsiq\GRecaptcha\Support\AbstractGRecaptcha;

/**
 * Заглушка Валидатора капчи.
 */
class NullableDriver extends AbstractGRecaptcha
{
    /**
     * Экземпляр контейнера приложения.
     * @var Container
     */
    protected $container;

    /**
     * Создать экземпляр заглушки Валидатора капчи.
     * @param  Container  $container
     * @param  array  $params
     */
    public function __construct(
        Container $container,
        array $params = []
    ) {
        $this->container = $container;
    }

    /**
     * Получить проанализированное HTML строковое представление
     * поля для ввода капчи пользователем.
     * @param  string  $view
     * @return Renderable|null
     */
    public function input(string $view = ''): ?Renderable
    {
        return null;
    }

    /**
     * Получить проанализированное HTML строковое представление
     * JavaScript'ов капчи.
     * @param  string  $view
     * @return Renderable|null
     */
    public function script(string $view = ''): ?Renderable
    {
        return null;
    }

    /**
     * Выполнить валидацию капчи.
     * @param  string  $attribute
     * @param  string|null  $userToken
     * @param  array  $parameters
     * @param  ValidatorContract  $validator
     * @return bool
     */
    public function validate(
        string $attribute,
        string $userToken = null,
        array $parameters = [],
        ValidatorContract $validator
    ): bool {

        return true;
    }
}
