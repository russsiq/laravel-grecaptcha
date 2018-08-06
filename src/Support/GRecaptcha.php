<?php

namespace Russsiq\GRecaptcha\Support;

use Russsiq\GRecaptcha\Support\Contracts\GRecaptchaContract;

class GRecaptcha implements GRecaptchaContract
{
	protected $apiRender;
	protected $apiVerify;

	protected $siteKey;
	protected $secretKey;
	protected $score;

	protected $response;

	public function __construct($config, $response)
	{
		$this->apiRender = $config['api_render'] ?? 'https://www.google.com/recaptcha/api.js?render=';
		$this->apiVerify = $config['api_verify'] ?? 'https://www.google.com/recaptcha/api/siteverify';

		$this->siteKey = $config['site_key'] ?? null;
		$this->secretKey = $config['secret_key'] ?? null;
		$this->score = $config['score'] ?? 0.5;

		$this->response = $response ?? null; // ?? to >= PHP 7.0
	}

	public function input(string $tpl = 'g_recaptcha::g_recaptcha_input')
	{
		return view($tpl);
	}

	public function script(string $tpl = 'g_recaptcha::g_recaptcha_script')
	{
		if (is_null($this->siteKey)) {
			return null;
		}

		return view($tpl, [
				'api_render' => $this->apiRender,
				'site_key' => $this->siteKey
			])->render();
	}

	public function verifying()
	{
		if (is_null($this->secretKey) or is_null($this->response)) {
			return false;
		}

		$path = $this->apiVerify.'?'.http_build_query([
			'secret' => $this->secretKey,
			'response' => $this->response
		]);

		$verified = json_decode(file_get_contents($path), true);

		if (JSON_ERROR_NONE !== json_last_error()) {
		    return false;
		}

		if ($verified['success'] and $verified['score'] >= $this->score) {
			return true;
		}

		return false;
	}
}
