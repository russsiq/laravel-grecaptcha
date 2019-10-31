<?php

namespace Russsiq\GRecaptcha\Support\Contracts;

interface GRecaptchaContract
{
    public function input(string $tpl);
    public function script(string $tpl);
    public function verifying();
}
