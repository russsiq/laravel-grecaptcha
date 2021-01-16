<?php

namespace Russsiq\GRecaptcha\Support\Drivers;

// Сторонние зависимости.
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Contracts\Support\Renderable;
use Russsiq\GRecaptcha\Support\AbstractGRecaptcha;
use Russsiq\GRecaptcha\GRecaptchaServiceProvider;

/**
 * Валидатора капчи, основанный на коде изображения.
 */
class ImageCodeDriver extends AbstractGRecaptcha
{
    /**
     * Имя кода, используемое для записи в кэш.
     * @const string
     */
    const SESSION_CODE_NAME = 'g-recaptcha-response';

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
        'image_width' => 68,
        'image_height' => 38,
        'font_size' => 20,
        // https://www.dafont.com/blowbrush.font
        'font_path' => 'resources/fonts/blowbrush.ttf',

    ];

    /**
     * Высота изображения капчи.
     * @var int
     */
    protected $imageWidth;

    /**
     * Ширина изображения капчи.
     * @var int
     */
    protected $imageHeight;

    /**
     * Высота шрифта на изображении.
     * @var int
     */
    protected $fontSize;

    /**
     * Полный путь к файлу шрифта.
     * @var string
     */
    protected $fontPath;

    /**
     * Случайно сгенерированный целочисленный четырехзначный код,
     * который будет использован для отрисовки изобрачения капчи.
     * @var int
     */
    protected $code;

    /**
     * Создать экземпляр заглушки Валидатора капчи.
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
        $this->setImageWidth($params['image_width'] ?? null)
            ->setImageHeight($params['image_height'] ?? null)
            ->setFontSize($params['font_size'] ?? null)
            ->setFontPath($params['font_path'] ?? null);

        return $this;
    }

    /**
     * Получить проанализированное HTML строковое представление
     * поля для ввода капчи пользователем.
     * @param  string  $view
     * @return Renderable|null
     */
    public function input(string $view = 'g_recaptcha::image_code-input'): ?Renderable
    {
        return view($view, [
            'noncache' => time(),

        ]);
    }

    /**
     * Получить проанализированное HTML строковое представление
     * JavaScript'ов капчи.
     * @param  string  $view
     * @return Renderable|null
     */
    public function script(string $view = ''): ?Renderable
    {
        return null;
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
        $this->setValidator($validator)
            ->setCustomMessage(trans(
                'g_recaptcha::g_recaptcha.messages.fails'
            ));

        if (md5($userToken) === session(self::SESSION_CODE_NAME)) {
            return true;
        }

        $this->setCustomMessage(trans(
            'g_recaptcha::g_recaptcha.messages.invalid_code'
        ));

        return false;
    }

    /**
     * Установить ширину изображения.
     * @param  mixed  $value
     * @return $this
     */
    protected function setImageWidth($value = null): self
    {
        $this->imageWidth = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => [
                'default' => $this->defaultParams['image_width'],
            ]
        ]);

        return $this;
    }

    /**
     * Получить ширину изображения.
     * @return int
     */
    protected function imageWidth(): int
    {
        return $this->imageWidth;
    }

    /**
     * Установить высоту изображения.
     * @param  mixed  $value
     * @return $this
     */
    protected function setImageHeight($value = null): self
    {
        $this->imageHeight = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => [
                'default' => $this->defaultParams['image_height'],
            ]
        ]);

        return $this;
    }

    /**
     * Получить высоту изображения.
     * @return int
     */
    protected function imageHeight(): int
    {
        return $this->imageHeight;
    }

    /**
     * Установить размер шрифта.
     * @param  mixed  $value
     * @return $this
     */
    protected function setFontSize($value = null): self
    {
        $this->fontSize = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => [
                'default' => $this->defaultParams['font_size'],
            ]
        ]);

        return $this;
    }

    /**
     * Получить размер шрифта.
     * @return int
     */
    protected function fontSize(): int
    {
        return $this->fontSize;
    }

    /**
     * Установить полный путь к файлу шрифта.
     * @param  mixed  $value
     * @return $this
     */
    protected function setFontPath($value = null): self
    {
        $filesystem = $this->container->make('files');

        if (! $filesystem->isReadable($value)) {
            $value = GRecaptchaServiceProvider::SOURCE_DIR
                .$this->defaultParams['font_path'];
        }

        $this->fontPath = $value;

        return $this;
    }

    /**
     * Получить полный путь к файлу шрифта.
     * @return string
     */
    protected function fontPath(): string
    {
        return $this->fontPath;
    }

    /**
     * Установить значение кода капчи в сессию.
     * @param  mixed  $value
     * @return $this
     */
    public function setSessionCode(int $value = null): self
    {
        $this->code = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => 1000,
                'max_range' => 9000,
                'default' => rand(1000, 9999),
            ]
        ]);

        // Сохранить в сессию значение кода.
        session([
            self::SESSION_CODE_NAME => md5($this->code),

        ]);

        return $this;
    }

    /**
     * Создать изображение капчи для отображения его пользователю.
     * @return string
     */
    public function createImage(): string
    {
        // Если код не был ранее сгенерирован, то сделаем это сейчас.
        if (empty($this->code)) {
            $this->setSessionCode();
        }

        // Генерация изображения капчи.
        $image = imagecreatetruecolor($this->imageWidth(), $this->imageHeight());
        $back = imagecolorallocate($image, 200, 200, 200);
        $grey = imagecolorallocate($image, 150, 150, 150);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, $this->imageWidth() * 2, $this->imageHeight() * 2, $back);
        imagettftext($image, $this->fontSize(), 8, 10, 30, $grey, $this->fontPath(), $this->code);
        imagettftext($image, $this->fontSize(), 8, 6, 34, $white, $this->fontPath(), $this->code);

        // Стартуем буфер.
        ob_start();

        // Отрисовываем изображение.
        imagepng($image);

        // Получаем содержимое буфера и очищаем его.
        $content = ob_get_clean();

        // Уничтожаем изображение.
        imagedestroy($image);

        return $content;
    }

    // НЕ используется.
    protected function encodeImageData(string $data): string
    {
        return 'data:image/png;base64,'.base64_encode($data);
    }
}
