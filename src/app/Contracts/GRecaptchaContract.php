<?php

namespace Russsiq\GRecaptcha\Contracts;

interface GRecaptchaContract
{
    public function input(string $tpl);
    public function script(string $tpl);
    public function verifying();
}
