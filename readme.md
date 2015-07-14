# Laravel RBAC
Laravel 5 RBAC implementation

Package was inspired by RBAC module from Yii Framework

## Installation
1. Run  
    ```bash
    composer require "smart-crowd/laravel-rbac":"dev-master"
    ```

2. Add service provider and facade into `/config/app.php` file.  
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

3. Publish package configs  
    ```bash
    php artisan vendor:publish
    ```

4. Implement `Assignable` contract in your user model. And use `AllowedTrait`.  
    ```php
    use SmartCrowd\Rbac\Traits\AllowedTrait;
    use SmartCrowd\Rbac\Contracts\Assignable;
    
    class User extends Model implements Assignable
    {
        use AllowedTrait;
    
        /**
         * Should return array of permissions and roles names,
         * assigned to user.
         *
         * @return array Array of user assignments.
         */
        public function getAssignments()
        {
            // your implementation here
        }
        ...
    }
    ```

## Usage
1. Describe you permissions in `/Rbac/items.php`

2. Use inline in code  
    ```php
    if (Auth::user()->allowed('article.delete', ['article' => $article])) {
        // user has access to 'somePermission.name' permission
    }
    ```

3. Or in middleware  
    ```php
    Route::delete('/articles/{article}', [
        'middleware' => 'rbac:article.delete', 
        'uses' => 'ArticlesController@delete'
    ]);
    ```
    Of course, don't forget to register middleware in `/Http/Kernel.php` file
    ```php
    protected $routeMiddleware = [
        ...
        'rbac' => 'SmartCrowd\Rbac\Middleware\RbacMiddleware',
    ];
    ```
    To use route parameters in business rules as models instead just ids, you should bind it in `RouteServicePrivider.php`:
    ```php
    public function boot(Router $router)
    {
        //...
        $router->model('article', '\App\Article');
    
        parent::boot($router);
    }
    ```
    
    There are 3 ways to bind permission name to action name:
    - middleware paramenter
    - bind they directelly in `/Rbac/actions.php` file
    - name permission like action, for example `article.edit` for `ArticleController@edit` action

4. Or in your views
    ```php
    @allowed('article.edit', ['article' => $article])
        <a href="{{ route('edit', ['article' => $article]) }}">edit</a>
    @else
        <span>You can not edit this article</span>
    @endallowed
    ```
    If `rbac.shortDirectives` option are enabled, you can use shorter forms of directives, like this:
    ```php
    @allowedArticleEdit(['article' => $article])
        {{ $some }}
    @endallowed
    
    @allowedIndex
        {{ $some }}
    @endallowed
    ```

### Context Roles  
In some cases, you may want to have dynamically assigned roles. For example, the role `groupModerator` is dynamic, because depending on the current group, the current user may have this role, or may not have. In our terminology, this role are "Context Role", and current group is "Role Context". The context decides which additional context roles will be assigned to the current user. In our case, `Group` model should implement `RbacContext` interface, and method `getAssignments($user)`.

When checking is enough to send context model among other parameters:
```php
@allowed('group.post.delete', ['post' => $post, 'group' => $group]) // or $post->group
    post delete button
@endallowed
```

But for automatic route check in middleware we usually send only post without group:
```php
Route::delete('/post/{post}', [
    'middleware' => 'rbac:group.post.delete', 
    'uses' => 'PostController@delete'
]);
```
For this case you can implement `RbacContextAccesor` intarface by `Post` model. `getContext()` method should return `Group` model. Then you just have to send only the post, and context roles will be applied in middleware to:
```php
@allowed('group.post.delete', ['post' => $post]) // or $post->group
    post delete button
@endallowed
```
You can not do that, if you send context with subject:
```php
Route::delete('/group/{group}/post/{post}', [
    'middleware' => 'rbac:group.post.delete', 
    'uses' => 'PostController@delete'
]);
```
