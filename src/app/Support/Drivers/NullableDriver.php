<?php

namespace Russsiq\GRecaptcha\Support\Drivers;

// Сторонние зависимости.
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Contracts\Support\Htmlable;
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
     * @return void
     */
    public function __construct(Container $container, array $params = [])
    {
        $this->container = $container;
    }

    /**
     * Получить HTML строковое представление поля ввода капчи пользователем.
     * @return Htmlable|null
     */
    public function input(): ?Htmlable
    {
        return null;
    }

    /**
     * Получить HTML строковое представление JavaScript капчи.
     * @param  string  $view
     * @return Htmlable|null
     */
    public function script(string $view = ''): ?Htmlable
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
