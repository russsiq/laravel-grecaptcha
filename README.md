## Laravel 5.6 validator extension with Google reCAPTCHA v3.

Расширение валидатора фреймворка Laravel 5.6 для использования Google reCAPTCHA v3. Выполняет запрос к сервису Google об оценке действий пользователя без его участия для блокирования отправки форм ботами, которые чаще всего спамят.

### Подключение

**1** Для добавления зависимости в проект на Laravel в файле `composer.json`
```php
"require": {
    ...
    "russsiq/laravel-grecaptcha": "dev-master"
}
```

**2** Для подключения в уже созданный проект воспользуйтесь командной строкой:
```
composer require russsiq/laravel-grecaptcha:dev-master
```

**3** В файле `config/app.php` добавьте:

**3.1** Провайдер услуг в раздел `'providers'`:
```
Russsiq\GRecaptcha\GRecaptchaServiceProvider::class,
```

**3.2** Псевдоним класса (Facade) в раздел `'aliases'`:
```
'GRecaptcha' => Russsiq\GRecaptcha\Support\Facades\GRecaptcha::class,
```

**4** Для публикации (копирования) файла настроек воспользуйтесь командной строкой
```
php artisan vendor:publish --provider="Russsiq\GRecaptcha\GRecaptchaServiceProvider"
```

**5** Перед использованием пакета зарегистрируйтесь и получите **Ключ** и **Секретный ключ** reCAPTCHA v3 здесь [https://g.co/recaptcha/v3](https://g.co/recaptcha/v3). Вставьте Ключ и Секретный ключ в соответствующие поля в файле `config/g_recaptcha.php` вашего проекта.

### Использование в шаблонах
Добавьте javascript в главный шаблон перед закрывающим тегов *&lt;/body&gt;*, используя директиву `@g_recaptcha_script`.

Добавьте скрытое поле между тегами *&lt;form&gt;&lt;/form&gt;* обрабатываемой формы, используя директиву `@g_recaptcha_input`.

Обе директивы могут принимать по одному входящему параметру, в котором можно указать относительные пути на расположение шаблонов. После отправки формы и в случае применения асинхронного запроса (ajax), для обновления скрытого поля используйте js функцию `grecaptcha_reload()`.

### Объявление правил проверки (валидации)
```
$rules = [
    'g-recaptcha-response' => 'g_recaptcha',
];
```

### Удаление пакета из вашего проекта на Laravel
```
composer remove russsiq/laravel-grecaptcha
```

### Тестирование

Неа, не слышал.

### Лицензия

laravel-grecaptcha - программное обеспечение с открытым исходным кодом, распространяющееся по лицензии [MIT license](https://choosealicense.com/licenses/mit/).
