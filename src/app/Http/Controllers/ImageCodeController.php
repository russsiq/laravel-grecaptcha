<?php

namespace Russsiq\GRecaptcha\Http\Controllers;

// Зарегистрированные фасады приложения.
use GRecaptcha;

// Сторонние зависимости.
use Illuminate\Http\Response;

/**
 * Контроллер для генерации изображения капчи.
 * Попробуем без наследований от контроллера фреймворка.
 */
class ImageCodeController
{
    /**
     * Создать экземпляр контроллера для генерации изображения капчи.
     */
    public function __construct(

    ) {

    }

    /**
     * Отобразить изображение капчи.
     * @return Response
     */
    public function show(): Response
    {
        // Стартуем сессию для кода капчи.
        // Не нужно `это` пихать в контроллер!
        GRecaptcha::setSessionCode();

        // Задаем HTTP заголовки и предотвращаем кэширование на стороне клиента.
        return response(GRecaptcha::createImage(), 200, [
            'Content-Type' => 'image/png',
            'Pragma' => 'no-cache',
            'Expires' => 'Wed, 1 Jan 1997 00:00:00 GMT',
            'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
            'Cache-Control' => 'must-revalidate, no-cache, no-store, private',

        ]);
    }
}
