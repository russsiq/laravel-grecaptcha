<?php

namespace Russsiq\GRecaptcha\Http\Controllers;

// Зарегистрированные фасады приложения.
use GRecaptcha;

class ImageCodeController
{
    public function __construct()
    {

    }

    public function show()
    {
        $content = GRecaptcha::setSessionCode()
            ->createImage();

        // Print HTTP headers and prevent caching on client side.
        return response($content, 200, [
            'Content-Type' => 'image/png',
            'Pragma' => 'no-cache',
            'Expires' => 'Wed, 1 Jan 1997 00:00:00 GMT',
            'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
            'Cache-Control' => 'must-revalidate, no-cache, no-store, private',

        ]);
    }
}
