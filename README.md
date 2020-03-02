## Расширение валидатора Laravel 6.x для проверки Google reCAPTCHA v3

Расширение валидатора фреймворка Laravel 6.x для использования Google reCAPTCHA v3. Выполняет запрос к сервису об оценке действий пользователя без его участия для блокирования отправки форм ботами, которые чаще всего спамят.

 >Перед использованием пакета зарегистрируйтесь и получите **Ключ** и **Секретный ключ**: [https://g.co/recaptcha/v3](https://g.co/recaptcha/v3).

### Подключение

 - **1** Для добавления зависимости в проект на Laravel в файле `composer.json`

    ```json
    "require": {
        "russsiq/laravel-grecaptcha": "dev-master"
    }
    ```

 - **2** Для подключения в уже созданный проект воспользуйтесь командной строкой:

    ```console
    composer require russsiq/laravel-grecaptcha:dev-master
    ```

 - **3** Если в вашем приложении включен отказ от обнаружения пакетов в директиве `dont-discover` в разделе `extra` файла `composer.json`, то необходимо самостоятельно добавить в файле `config/app.php`:

    - **3.1** Провайдер услуг в раздел `providers`:

        ```php
        Russsiq\GRecaptcha\GRecaptchaServiceProvider::class,
        ```

    - **3.2** Псевдоним класса (Facade) в раздел `aliases`:

        ```php
        'GRecaptcha' => Russsiq\GRecaptcha\Support\Facades\GRecaptcha::class,
        ```

### Публикация файлов пакета

Публикация (копирование) всех доступных файлов для переопределения и тонкой настройки пакета осуществляется через интерфейс командной строки Artisan:

```console
php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider"
```

Помимо этого, доступна групповая публикация файлов по отдельным меткам `config`, `lang`, `views`:

```console
php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider" --tag=config --force
```

```console
php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider" --tag=lang --force
```

```console
php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider" --tag=views --force
```

### Настройка

Вставьте **Ключ** и **Секретный ключ** в соответствующие поля в файле `config/g_recaptcha.php` вашего проекта.

### Использование в шаблонах

Добавьте скрытое поле в обрабатываемую форму, используя директиву `@g_recaptcha_input`. Например:

```html
@error ('g-recaptcha-response')
<div class="alert alert-danger" role="alert">
    @lang('g_recaptcha::g_recaptcha.messages.fails')
</div>
@enderror

<form action="/profile" method="POST">
    @g_recaptcha_input
    @csrf

    <!-- ...code -->
</form>
```

Добавьте JavaScript в главный шаблон перед закрывающим тегом `</body>`, используя директиву `@g_recaptcha_script`. Данная директива может принимать один входящий параметр, в котором можно указать относительный путь на расположение шаблона.

После отправки формы и в случае применения асинхронного запроса (AJAX), для обновления скрытого поля используйте JavaScript функцию `grecaptcha_reload();`.

 > Обе директивы не являются обязательным к использованию: вы можете самостоятельно сформировать как скрытое поле, так и логику JavaScript.

### Объявление правил проверки (валидации)

```php
/**
 * Получить массив правил валидации,
 * которые будут применены к запросу.
 * @return array
 */
public function rules(): array
{
    return [
        'title' => [
            // ...code
        ],

        'body' => [
            // ...code
        ],

        'g-recaptcha-response' => [
            'bail',
            'required',
            'string',
            'g_recaptcha',
        ],
    ];
}
```

### Удаление пакета из вашего проекта на Laravel

```console
composer remove russsiq/laravel-grecaptcha
```

### Тестирование

Неа, не слышал.

### Лицензия

`laravel-grecaptcha` - программное обеспечение с открытым исходным кодом, распространяющееся по лицензии [MIT](https://choosealicense.com/licenses/mit/).
