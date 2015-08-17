# Laravel RBAC
Реализация RBAC для Laravel 5

На создание этого пакета вдохновила реализация RBAC во фреймворке Yii.

## Установка
1. Запустите  
    ```bash
    composer require "smart-crowd/laravel-rbac":"dev-master"
    ```

2. Добавьте провайдер и алиас в файл `/config/app.php`.  
    ```php
    'providers' => [
        ...
    
        SmartCrowd\Rbac\RbacServiceProvider::class,
    ],
    ...
    
    'aliases' => [
        ...
        
        'Rbac' => 'SmartCrowd\Rbac\Facades\Rbac'
    ]
    ```

3. Опубликуйте конфиги пакета 
    ```bash
    php artisan vendor:publish
    ```

4. Реализуйте интерфейс `Assignable` вашей моделью пользователя. Также используйте трейт `AllowedTrait`.  
    ```php
    use SmartCrowd\Rbac\Traits\AllowedTrait;
    use SmartCrowd\Rbac\Contracts\Assignable;
    
    class User extends Model implements Assignable
    {
        use AllowedTrait;
    
        /**
         * Должен вернуть массив прав и ролей, назначенных пользователю
         *
         * @return array Массив назначенных прав и ролей
         */
        public function getAssignments()
        {
            // ваша реализация
        }
        ...
    }
    ```

## Использование
1. Опишите ваши права в файле `/Rbac/items.php`

2. Использование в коде (например в конроллере)  
    ```php
    if (Auth::user()->allowed('article.delete', ['article' => $article])) {
        // пользователь иммет доступ к действию 'somePermission.name'
    }
    ```

3. Также вы можете использовать мидлвер
    ```php
    Route::delete('/articles/{article}', [
        'middleware' => 'rbac:article.delete', 
        'uses' => 'ArticlesController@delete'
    ]);
    ```
    Конечно не забудьте зарегистрировать этот мидлвер в файле `/Http/Kernel.php`
    ```php
    protected $routeMiddleware = [
        ...
        'rbac' => 'SmartCrowd\Rbac\Middleware\RbacMiddleware',
    ];
    ```
    Если вы хотите использовать в бизнес правилах модели, вместо их идентификаторов, вам необходимо 
    привязать модель к роуту в провайдере `RouteServicePrivider.php`:
    ```php
    public function boot(Router $router)
    {
        //...
        $router->model('article', '\App\Article');
    
        parent::boot($router);
    }
    ```
    
    Есть 3 способа назничть проверяемые права роуту:
    - параметр мидлвера, как в примере выше
    - связать роут с проверяемымы правами напрямую в файле `/Rbac/actions.php`
    - назвать право или роль как экшн, напрмер `article.edit` автоматически будет проверяться для действия `ArticleController@edit`.
    Конечно если для этого роута был назначен наш мидлвер

4. Использование в blade шаблонах:
    ```php
    @allowed('article.edit', ['article' => $article])
        <a href="{{ route('edit', ['article' => $article]) }}">edit</a>
    @else
        <span>Вы не можете редактировать эту статью</span>
    @endallowed
    ```
    Если включена опция `rbac.shortDirectives`, вы можете более короткие формы директивы проверки прав, напрмер так:
    ```php
    @allowedArticleEdit(['article' => $article])
        {{ $some }}
    @endallowed
    
    @allowedIndex
        {{ $some }}
    @endallowed
    ```

### Контекстные роли
В некоторых случаях, у вас может возникнуть необходимость в динамически назначаемых ролях.
Напрмер, роль `groupModerator` динамически назначаемая, потому что, в зависимости от текущей группы,
авторизованный пользователь может иметь эту роль, а может и не иметь.
В нашем случае роль `groupModerator` является контекстной, а текущая группа - контекстом этой роли.
Контекст определяет какие дополнительные (контекстные) роли и права будут назначены 
текущему авторизованному пользователю.
Возвращаясь к нашему примеру: модель `Group` должна реализовать интерфейс `RbacContext`, который требует наличия
метода `getAssignments($user)`.

При проверке достаточно передать модель-контекст наряду с остальными параметрами
```php
@allowed('group.post.delete', ['post' => $post, 'group' => $group]) // или $post->group
    кнопка удаления поста
@endallowed
```

В роутах этот пример может выглядеть так (конечно и `group` и `post` должны быть привязаны в `RouteServicePrivider.php`):
```php
Route::delete('/group/{group}/post/{post}', [
    'middleware' => 'rbac:group.post.delete', 
    'uses' => 'PostController@delete'
]);
```

Но мы не всегда можем передать контекст в роуте вместе с основной моделью:
```php
Route::delete('/post/{post}', [
    'middleware' => 'rbac:group.post.delete', 
    'uses' => 'PostController@delete'
]);
```
В этом случае вы можете реализовать интерфейс `RbacContextAccesor` вашей моделью. В нашем примере это `Post`.
Метод `RbacContextAccesor::getContext()` должен вернуть экземпляр модели-контекста. Для нашего примера это группа, в 
которой был опубликован этот пост. Тогда и при проверке в шаблоне нет необходимости передавать модель-контекст:
```php
@allowed('group.post.delete', ['post' => $post])
    кнопка удаления поста
@endallowed
```

