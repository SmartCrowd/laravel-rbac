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
