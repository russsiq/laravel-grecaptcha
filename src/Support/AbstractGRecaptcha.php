<?php

namespace Russsiq\GRecaptcha\Support;

// Сторонние зависимости.
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Russsiq\GRecaptcha\Contracts\GRecaptchaContract;

/**
 * Абстрактный класс Валидатора капчи.
 */
abstract class AbstractGRecaptcha implements GRecaptchaContract
{
    /**
     * Экземпляр валидатора, переданный в методе `validate`.
     * @var ValidatorContract
     */
    protected $validator;

    /**
     * Получить проанализированное HTML строковое представление
     * поля для ввода капчи пользователем.
     * @param  string  $view
     * @return Renderable|null
     */
    abstract public function input(string $view): ?Renderable;

    /**
     * Получить проанализированное HTML строковое представление
     * JavaScript'ов капчи.
     * @param  string  $view
     * @return Renderable|null
     */
    abstract public function script(string $view): ?Renderable;

    /**
     * Выполнить валидацию капчи (токена), полученого из формы от пользователя.
     * @param  string  $attribute
     * @param  string|null  $userToken
     * @param  array  $parameters
     * @param  ValidatorContract  $validator
     * @return bool
     */
    abstract public function validate(
        string $attribute,
        ?string $userToken,
        array $parameters,
        ValidatorContract $validator
    ): bool;

    /**
     * Установить экземпляр валидатора.
     * @param  ValidatorContract  $validator
     * @return self
     */
    protected function setValidator(ValidatorContract $validator): self
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Установить пользовательское сообщение для валидатора.
     * @param  string  $message
     * @return void
     */
    protected function setCustomMessage(string $message): void
    {
        $this->validator->setCustomMessages([
            'g_recaptcha' => $message,

        ]);
    }
}
