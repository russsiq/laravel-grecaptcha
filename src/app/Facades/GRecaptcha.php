<?php

namespace Russsiq\GRecaptcha\Facades;

// Сторонние зависимости.
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Contracts\Support\Renderable|null input(string $view);
 * @method static \Illuminate\Contracts\Support\Renderable|null script(string $view);
 * @method static bool validate(string $attribute, string $userToken = null, array $parameters = [], \Illuminate\Contracts\Validation\Validator $validator);
 *
 * @see \Russsiq\GRecaptcha\Contracts\GRecaptchaContract
 * @see \Russsiq\GRecaptcha\Support\GRecaptchaManager
 */
class GRecaptcha extends Facade
{
    /**
     * Получить зарегистрированное имя компонента.
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'g_recaptcha';
    }
}
