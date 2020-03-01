<?php

namespace Russsiq\GRecaptcha\Support\Drivers;

// Исключения.
use Exception;

// Сторонние зависимости.
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Application;
use Russsiq\GRecaptcha\Contracts\GRecaptchaContract;

/**
 * Создание и валидация капчи с использованием драйвера GoogleV3.
 */
class GoogleV3Driver implements GRecaptchaContract
{
    /**
     * Код успешного ответа.
     * @var int
     */
    const HTTP_OK = 200;

    /**
     * Экземпляр контейнера приложения.
     * @var Container
     */
    protected $container;

    /**
     * Массив параметров по умолчанию экземпляра класса.
     * @var array
     */
    protected $defaultParams = [
        'api_render' => 'https://www.google.com/recaptcha/api.js?render=',
        'api_verify' => 'https://www.google.com/recaptcha/api/siteverify',
        'score' => 0.5,
        'secret_key' => '',
        'site_key' => '',

    ];

    /**
     * URL-адрес сервиса `создания` токена пользователя.
     * @var string
     */
    protected $apiRender;

    /**
     * URL-адрес сервиса `проверки` токена пользователя.
     * @var string
     */
    protected $apiVerify;

    /**
     * Ключ сайта, используемый для `создания` токена пользователя.
     * @var string
     */
    protected $siteKey;

    /**
     * Секретный ключ, используемый для `проверки` токена пользователя.
     * @var string
     */
    protected $secretKey;

    /**
     * Нижний порог оценки действий пользователя.
     * @var double
     */
    protected $score;

    /**
     * Создать новый экземпляр Валидатора капчи
     * с использованием драйвера GoogleV3.
     * @param  Container  $container
     * @param  array  $params
     * @return void
     */
    public function __construct(Container $container, array $params = [])
    {
        $this->container = $container;

        $this->configure($params);
    }

    /**
     * Конфигурирование параметров экземпляра класса.
     * @param  array  $params
     * @return $this
     */
    public function configure(array $params = []): self
    {
        $this->setApiRender($params['api_render'] ?? null)
            ->setApiVerify($params['api_verify'] ?? null)
            ->setScore($params['score'] ?? null);

		// Эти параметры являются обязательными,
		// но в силу разных обстоятельств они могут быть не указаны.
		// Поэтому на этапе инициализации устанавливаем пустые значения.
        $this->secretKey = $params['secret_key'] ?? null;
        $this->siteKey = $params['site_key'] ?? null;

        return $this;
    }

    /**
     * Установить URL-адрес сервиса `создания` токена пользователя.
     * @param  mixed  $value
     * @return $this
     */
    public function setApiRender($value = null): self
    {
        $this->apiRender = filter_var($value, FILTER_VALIDATE_URL) === false
            ? $this->defaultParams['api_render'] : $value;

        return $this;
    }

    /**
     * Получить URL-адрес сервиса `создания` токена пользователя.
     * @return string
     */
    public function apiRender(): string
    {
        return $this->apiRender;
    }

    /**
     * Установить URL-адрес сервиса `проверки` токена пользователя.
     * @param  mixed  $value
     * @return $this
     */
    public function setApiVerify($value = null): self
    {
        $this->apiVerify = filter_var($value, FILTER_VALIDATE_URL) === false
            ? $this->defaultParams['api_verify'] : $value;

        return $this;
    }

    /**
     * Получить URL-адрес сервиса `проверки` токена пользователя.
     * @return string
     */
    public function apiVerify(): string
    {
        return $this->apiVerify;
    }

    /**
     * Установить Нижний порог оценки действий пользователя.
     * @param  mixed  $value
     * @return $this
     */
    public function setScore($value = null): self
    {
        $this->score = filter_var($value, FILTER_VALIDATE_FLOAT, [
            'options' => [
                'min_range' => 0.1,
                'max_range' => 0.9,
                'default' => $this->defaultParams['score'],
            ]
        ]);

        return $this;
    }

    /**
     * Получить Нижний порог оценки действий пользователя.
     * @return double
     */
    public function score(): double
    {
        return $this->score;
    }

    public function input(string $tpl = 'g_recaptcha::g_recaptcha_input')
    {
        return view($tpl);
    }

    public function script(string $tpl = 'g_recaptcha::g_recaptcha_script')
    {
        if (empty($this->siteKey)) {
            return null;
        }

        return view($tpl, [
                'api_render' => $this->apiRender(),
                'site_key' => $this->siteKey
            ])
            ->render();
    }

    /**
     * [validate description]
     * @param  string  $attribute
     * @param  string|null  $value
     * @param  array  $parameters
     * @param  ValidatorContract  $validator
     * @return bool
     */
    public function validate(
        string $attribute,
        string $value = null,
        array $parameters = [],
        ValidatorContract $validator
    ) {
        if ($this->verifying($this->secretKey, $value)) {
            return true;
        }

        $validator->fallbackMessages['g_recaptcha'] = trans(
            'g_recaptcha::g_recaptcha.messages.fails'
        );

        return false;
    }

    public function verifying(string $secretKey = null, string $response = null)
    {
        try {
            if (is_null($secretKey)) {
                throw new Exception(
                    'Secret Key not defined.'
                );
            }

            if (is_null($response)) {
                throw new Exception(
                    'User response token not provided.'
                );
            }

            $verified = $this->touchAnswer(
                $this->prepareQuery($secretKey, $response)
            );
        } catch (Exception $e) {
            logger(self::class, [$e->getMessage()]);

            return false;
        }

        return is_array($verified)
            && $verified['success']
            && $verified['score'] >= $this->score;
    }

    protected function touchAnswer(string $query)
    {
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

    protected function prepareQuery(string $secret, string $response)
    {
        return http_build_query([
            'secret' => $secret,
            'response' => $response,

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

        curl_setopt($ch, CURLOPT_URL, $this->apiVerify());
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
            $this->apiVerify().'?'.$query
        ));
    }

    /**
     * Определить, что Секретный ключ был задан.
     * @param  string  $secretKey
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function assertSecretKey($secretKey)
    {
        if (! is_string($secretKey) || $secretKey === '') {
            throw new InvalidArgumentException(
                'Secret Key not defined.'
            );
        }
    }

    /**
     * Определить, что Ключ сайта был задан.
     * @param  string  $siteKey
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function assertSiteKey($siteKey)
    {
        if (! is_string($siteKey) || $siteKey === '') {
            throw new InvalidArgumentException(
                'Site Key not defined.'
            );
        }
    }

    /**
     * Определить, что был предоставлен токен при отправки формы пользователем.
     * @param  string  $userToken
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function assertUserToken($userToken)
    {
        if (! is_string($userToken) || $userToken === '') {
            throw new InvalidArgumentException(
                'User response token not provided.'
            );
        }
    }

    /**
     * Определить, что ответ имеет статус успешного.
     * @param  ResponseInterface  $response
     * @return void
     *
     * @throws Exception
     */
    protected function assertResponseIsSuccessful(ResponseInterface $response)
    {
        $code = $response->getStatusCode();

        if ($code !== self::HTTP_OK) {
            throw new Exception(sprintf(
                'Response status code: %s',
                $code
            ));
        }
    }

    /**
     * Подтвердить отсутствие ошибкок во время декодирования JSON.
     * @param  int  $jsonError
     * @return void
     *
     * @throws Exception
     */
    protected function assertJsonIsValid(int $jsonError)
    {
        if ($jsonError !== JSON_ERROR_NONE) {
            throw new Exception(sprintf(
                'JSON ERROR: %s',
                json_last_error_msg()
            ));
        }
    }
}
