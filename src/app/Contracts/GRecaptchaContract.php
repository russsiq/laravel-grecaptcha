<?php

namespace Russsiq\GRecaptcha\Contracts;

// Сторонние зависимости.
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;

/**
 * Контракт публичных методов валидатора капчи.
 * @var interface
 */
interface GRecaptchaContract
{
    /**
     * Получить HTML строковое представление поля ввода капчи пользователем.
     * @return Htmlable|null
     */
    public function input(): ?Htmlable;

    /**
     * Получить HTML строковое представление JavaScript капчи.
     * @param  string  $view
     * @return Htmlable|null
     */
    public function script(string $view): ?Htmlable;

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
    ): bool;

    /**
     * Выполнить верификацию токена, полученого из формы от пользователя.
     * @param  string  $secretKey
     * @param  string  $userToken
     * @return bool
     */
    public function verifying(string $secretKey, string $userToken): bool;
}
