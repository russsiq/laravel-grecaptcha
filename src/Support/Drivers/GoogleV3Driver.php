<?php

namespace Russsiq\GRecaptcha\Support\Drivers;

// Исключения.
use Exception;
use InvalidArgumentException;

// Базовые расширения PHP.
use stdClass;

// Сторонние зависимости.
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Contracts\Support\Renderable;
use Psr\Http\Message\ResponseInterface;
use Russsiq\GRecaptcha\Support\AbstractGRecaptcha;

/**
 * Создание и валидация капчи с использованием драйвера GoogleV3.
 */
class GoogleV3Driver extends AbstractGRecaptcha
{
    /**
     * Код успешного ответа.
     * @const int
     */
    const HTTP_OK = 200;

    /**
     * Экземпляр контейнера приложения.
     * @var Container
     */
    protected $container;

    /**
     * Экземпляр валидатора, переданный в методе `validate`.
     * @var ValidatorContract
     */
    protected $validator;

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
     * Значение поля капчи из формы, переданное в методе `validate`.
     * @var string|null
     */
    protected $userToken;

    /**
     * Создать новый экземпляр Валидатора капчи
     * с использованием драйвера GoogleV3.
     * @param  Container  $container
     * @param  array  $params
     */
    public function __construct(
        Container $container,
        array $params = []
    ) {
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
        // Это не повод выбрасывать исключения или
        // каким-либо другим способом крушить приложение.
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
     * @return float
     */
    public function score(): float
    {
        return $this->score;
    }

    /**
     * Получить проанализированное HTML строковое представление
     * поля для ввода капчи пользователем.
     * @param  string  $view
     * @return Renderable|null
     */
    public function input(string $view = 'g_recaptcha::google_v3-input'): ?Renderable
    {
        return view($view, [

            ]);
    }

    /**
     * Получить проанализированное HTML строковое представление
     * JavaScript'ов капчи.
     * @param  string  $view
     * @return Renderable|null
     */
    public function script(string $view = 'g_recaptcha::google_v3-script'): ?Renderable
    {
        if (empty($this->siteKey)) {
            return null;
        }

        return view($view, [
                'api_render' => $this->apiRender(),
                'site_key' => $this->siteKey,

            ]);
    }

    /**
     * Выполнить валидацию капчи (токена), полученого из формы от пользователя.
     * @param  string  $attribute
     * @param  string|null  $userToken
     * @param  array  $parameters
     * @param  ValidatorContract  $validator
     * @return bool
     */
    public function validate(
        string $attribute,
        ?string $userToken,
        array $parameters,
        ValidatorContract $validator
    ): bool {
        // Устанавливаем сообщение по умолчанию об ошибке атрибута.
        $this->setUserToken($userToken ?? '')
            ->setValidator($validator)
            ->setCustomMessage(trans(
                'g_recaptcha::g_recaptcha.messages.fails'
            ));

        try {
            // Проверяем свойства экземпляра класса,
            // без которых невозможно дальнейшая реализация.
            $this->assertSecretKey($this->secretKey);
            $this->assertSiteKey($this->siteKey);
            $this->assertUserToken($this->userToken);

            return $this->verify($this->secretKey, $this->userToken);
        } catch (Exception $e) {
            logger()
                ->error(self::class, [
                    'error' => $e->getMessage(),

                ]);

            return false;
        }
    }

    /**
     * Установить значение токена, полученого из формы от пользователя.
     * @param  string  $userToken
     * @return self
     */
    protected function setUserToken(string $userToken): self
    {
        $this->userToken = $userToken;

        return $this;
    }

    /**
     * Выполнить верификацию токена, полученого из формы от пользователя.
     * @param  string  $secretKey
     * @param  string  $userToken
     * @return bool
     */
    protected function verify(string $secretKey, string $userToken): bool
    {
        $response = $this->touchAnswer($secretKey, $userToken);
        $parsed = $this->parseResponse($response);

        // logger(self::class, [$parsed]);

        return $parsed->success
            && $parsed->score >= $this->score();
    }

    /**
     * Получить ответ от сервиса Google.
     * @param  string  $secretKey
     * @param  string  $userToken
     * @return ResponseInterface
     */
    protected function touchAnswer(string $secretKey, string $userToken): ResponseInterface
    {
        $response = $this->httpClient()
            ->request('GET', '', [
                'query' => [
                    'secret' => $secretKey,
                    'response' => $userToken,

                ],

            ]);

        return $this->assertResponseIsSuccessful($response) ?: $response;
    }

    /**
     * Получить экземпляр HTTP клиента.
     * @return ClientInterface
     */
    protected function httpClient(): ClientInterface
    {
        return new HttpClient([
            'base_uri' => $this->apiVerify(),

        ]);
    }

    /**
     * Распарсить ответ от сервиса Google.
     * @param  ResponseInterface  $response
     * @return stdClass
     */
    protected function parseResponse(ResponseInterface $response): stdClass
    {
        $answer = json_decode($response->getBody());

        return $this->assertJsonIsValid(json_last_error()) ?: $answer;
    }

    /**
     * Определить, что Секретный ключ был задан.
     * @param  string  $secretKey
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function assertSecretKey($secretKey): void
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
    protected function assertSiteKey($siteKey): void
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
    protected function assertUserToken($userToken): void
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
    protected function assertResponseIsSuccessful(ResponseInterface $response): void
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
    protected function assertJsonIsValid(int $jsonError): void
    {
        if ($jsonError !== JSON_ERROR_NONE) {
            throw new Exception(sprintf(
                'JSON ERROR: %s',
                json_last_error_msg()
            ));
        }
    }
}
