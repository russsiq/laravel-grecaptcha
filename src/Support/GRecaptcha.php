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
		$this->score = (double) $config['score'] ?? 0.5;

		$this->response = $response ?? null;
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

		try {
			$verified = $this->touchAnswer();
		} catch (Exception $e) {
            return false;
        }

		if (is_array($verified) and $verified['success'] and $verified['score'] >= $this->score) {
			return true;
		}

		return false;
	}

    protected function touchAnswer()
    {
		$query = $this->prepareQuery();

        if (extension_loaded('curl') and function_exists('curl_init')) {
			$answer = $this->getCurlAnswer($query);
        } elseif (ini_get('allow_url_fopen')) {
            $answer = $this->getFopenAnswer($query);
        } else {
            throw new Exception(
				'Not supported: cURL, allow_fopen_url.'
			);
        }

        $answer = json_decode($answer);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception('JSON answer error.');
        }

        return (array) $answer;
    }

	protected function prepareQuery()
	{
		return http_build_query([
	            'secret' => $this->secretKey,
	            'response' => $this->response
	        ]);
	}

	protected function getCurlAnswer(string $query)
	{
		$ch = curl_init();
		if (curl_errno($ch) != 0) {
			throw new Exception(
				'err_curl_'.curl_errno($ch).' '.curl_error($ch)
			);
		}
		curl_setopt($ch, CURLOPT_URL, $this->apiVerify);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$answer = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (404 == $status) {
			throw new Exception(
				'Source file not found.'
			);
		} elseif ($status != 200) {
			throw new Exception(
				'err_curl_'.$status
			);
		}
		curl_close($ch);

		return $answer;
	}

	protected function getFopenAnswer(string $query)
	{
		return file_get_contents(urlencode(
				$this->apiVerify.'?'.$query
			));
	}
}
