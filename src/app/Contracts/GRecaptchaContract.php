<?php

namespace Russsiq\GRecaptcha\Contracts;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;

interface GRecaptchaContract
{
    public function input(string $tpl);
    public function script(string $tpl);
    public function validate(
        string $attribute,
        string $userToken = null,
        array $parameters = [],
        ValidatorContract $validator
    );
    public function verifying(string $secretKey, string $response);
}
