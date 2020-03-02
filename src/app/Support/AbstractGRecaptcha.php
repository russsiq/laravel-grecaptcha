<?php

namespace Russsiq\GRecaptcha\Support;

// Сторонние зависимости.
use Illuminate\Contracts\Support\Htmlable;
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
     * Получить HTML строковое представление поля ввода капчи пользователем.
     * @return Htmlable|null
     */
    abstract public function input(): ?Htmlable;

    /**
     * Получить HTML строковое представление JavaScript капчи.
     * @param  string  $view
     * @return Htmlable|null
     */
    abstract public function script(string $view): ?Htmlable;

    /**
     * Выполнить валидацию капчи.
     * @param  string  $attribute
     * @param  string|null  $userToken
     * @param  array  $parameters
     * @param  ValidatorContract  $validator
     * @return bool
     */
    abstract public function validate(
        string $attribute,
        string $userToken = null,
        array $parameters = [],
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
    protected function setCustomMessage(string $message)
    {
        $this->validator->setCustomMessages([
            'g_recaptcha' => $message,

        ]);
    }
}
