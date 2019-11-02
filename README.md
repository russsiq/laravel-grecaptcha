## Расширение валидатора Laravel 6.* для проверки Google reCAPTCHA v3.

Расширение валидатора фреймворка Laravel `6.*` для использования Google reCAPTCHA v3. Выполняет запрос к сервису Google об оценке действий пользователя без его участия для блокирования отправки форм ботами, которые чаще всего спамят.

Перед использованием пакета зарегистрируйтесь и получите **Ключ** и **Секретный ключ**: [https://g.co/recaptcha/v3](https://g.co/recaptcha/v3).

### Подключение

**1** Для добавления зависимости в проект на Laravel в файле `composer.json`
```json
"require": {
    "russsiq/laravel-grecaptcha": "dev-master"
}
```

**2** Для подключения в уже созданный проект воспользуйтесь командной строкой:
```console
composer require russsiq/laravel-grecaptcha:dev-master
```

**3** В файле `config/app.php` добавьте:

**3.1** Провайдер услуг в раздел `'providers'`:
```php
Russsiq\GRecaptcha\GRecaptchaServiceProvider::class,
```

**3.2** Псевдоним класса (Facade) в раздел `'aliases'`:
```php
'GRecaptcha' => Russsiq\GRecaptcha\Support\Facades\GRecaptcha::class,
```

**4** Для публикации (копирования) файла настроек воспользуйтесь командной строкой
```console
php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider"
```

**5** Вставьте **Ключ** и **Секретный ключ** в соответствующие поля в файле `config/g_recaptcha.php` вашего проекта.

### Использование в шаблонах
Добавьте javascript в главный шаблон перед закрывающим тегов *&lt;/body&gt;*, используя директиву `@g_recaptcha_script`.

Добавьте скрытое поле между тегами *&lt;form&gt;&lt;/form&gt;* обрабатываемой формы, используя директиву `@g_recaptcha_input`.

Обе директивы могут принимать по одному входящему параметру, в котором можно указать относительные пути на расположение шаблонов. После отправки формы и в случае применения асинхронного запроса (ajax), для обновления скрытого поля используйте js функцию `grecaptcha_reload()`.

### Объявление правил проверки (валидации)
```php
$rules = [
    'g-recaptcha-response' => 'g_recaptcha',
];
```

### Удаление пакета из вашего проекта на Laravel
```console
composer remove russsiq/laravel-grecaptcha
```

### Тестирование

Неа, не слышал.

### Лицензия

laravel-grecaptcha - программное обеспечение с открытым исходным кодом, распространяющееся по лицензии [MIT](https://choosealicense.com/licenses/mit/).
