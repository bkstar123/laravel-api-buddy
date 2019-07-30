# laravel-api-buddy     
> This lightweight Laravel package provides a powerful and simpple toolset for quickly building high-quality RESTful API web services for Eloquent model resources with several advanced features such as schema transformation as well as sorting, filtering, selecting and paginating. Using together with the Laravel Passport package, you can have a full-fledge API system ready to serve any clients in a matter of minutes.    

## 1 Requirements  

It is recommended to install this package with PHP version 7.1.3+ and Laravel Framework version 5.6+ 

## 2 Installation
    composer require bkstar123/laravel-api-buddy

It will also install ***```barryvdh/laravel-cors```*** as a dependency. You can visit https://github.com/barryvdh/laravel-cors for the detailed description of that package.  

After installing, run:
```php artisan apibuddy:publish```

It will copy all necessary configuration files to ```/config/bkstar123_apibuddy.php``` & ```/config/cors.php```  

## 3 Configuration

```/config/cors.php``` is the config file of ***```barryvdh/laravel-cors```*** package, you should consult its documentation for the further details.  

```/config/bkstar123_apibuddy.php``` is the package's main config file, it contains the following options:  
- **```max_per_page```**:  The maximum page size that a request can specify, by default it is 1000 items/page
- **```default_per_page```**: The default page size that will be applied if a request does not specify, by default it is 10 items/page
- **```replace_exceptionhandler```**: Whether or not to replace the Laravel default exception handler with the one provided by the package. It is recommended to be set to ```true``` (its default value) so that all exceptions can be converted to appropriate JSON responses
- **```useTransform```**: Whether or not to use transformation. It is recommmended to be set to ```true``` (its default value) for the best security protection. Since the underlying PDO DB driver does not support binding column names, see https://laravel.com/docs/5.8/queries; the transformation should always be used whenever you allow user input to dictate the column names referenced by your queries.

## 4 Usage

### 4.1 General information

The package provides ***```\Bkstar123\ApiBuddy\Http\Controllers\ApiController```*** as the base API controller that can be extended by other API controllers. This ```ApiController``` has been automatically injected with an ```Bkstar123\ApiBuddy\Contracts\ApiResponsible``` instance.  

All API controllers extending ```ApiController``` have access to the property **``$apiResponser``** which holds an ```ApiResponsible``` instance. The ```ApiResponsible``` instance exposes the following methods:  

```php
<?php
    /**
     * Send error response in JSON format
     *
     * @param  mixed  $errors
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($errors, int $status = 500) : \Illuminate\Http\JsonResponse;

    /**
     * Send success response in JSON format
     *
     * @param  mixed  $data
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data, int $status = 200) : \Illuminate\Http\JsonResponse;
    
    /**
     * Show a collection of resources
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string $apiResource
     * @param  string $modelClass
     * @return  mixed (JSON)
     */
    public function showCollection($builder = null, $apiResource = '', $modelClass = '');

    /**
     * Show a resource instance
     *
     * @param  \Illuminate\Database\Eloquent\Model  $instance
     * @param  string $apiResource
     * @return  mixed (JSON)
     */
    public function showInstance(Model $instance, $apiResource = '');
```

- **```showCollection()```**: is to return a collection of model resources in JSON with some features such as sorting, filtering, column selecting and paginating  
- **```showInstance()```**: is to return a model instance in JSON with column selecting capability  
- **```successResponse()```**: is to return a generic success JSON response  
- **```errorResponse()```**: is to return a generic error JSOn response  

Where:
- **```$builder```**: either ```\Illuminate\Database\Eloquent\Builder``` or ```\Illuminate\Database\Query\Builder```.  
Example:  
```php
<?php
$eloquenrBuilder = User::getQuery();
$queryBuilder = DB::table('users');

// You can further add more query scope or modifying the builder before passing it to showCollection()
```

- **```$apiResource```**: fully qualified class name of the model API resource. See more about API Resources at https://laravel.com/docs/5.8/eloquent-resources  
- **```$modelClass```**: the fully qualified class name of the model, such as ```App\User```

### 4.2 Without transformation

Supposing that we need to build some API endpoints for ```users``` resource.  

- Set ```useTransform``` option to ```false``` in ```/config/bkstar123_apibuddy.php```  
- Make ```UserController``` to extend ```\Bkstar123\ApiBuddy\Http\Controllers\ApiController```  
```php
<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class UserController extends Controller
{
	...
}
```
- Assuming that ```index()``` returns a collection of user resource, ```showUser()``` returns an user instance and ```create()``` creates a new user instance  
```php
<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class UserController extends Controller
{
	public function index()
    {
    	return $this->apiResponser->showCollection(User::getQuery());
    }

    public function showUser(User $user)
    {
    	return $this->apiResponser->showInstance($user);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ], [
            'email.email' => 'The email must be valid'
        ]);
        $user = User::create($request->all());
        return $this->apiResponser->successResponse($user, 201);
    }
}
```

### 4.3 With transformation

Set ```useTransform``` option to ```true``` in ```/config/bkstar123_apibuddy.php```  

a) Create user resource:  
- ```php artisan make:resource UserResource```  

It will be created in ```pp/Http/Resources``` directory  


- Make it to extends ```Bkstar123\ApiBuddy\Http\Resources\AppResource```  
- The only method for it to implement is **```resourceMapping()```**, this method defines the way to transform the API response (for the purpose of server->client direction)  
```php
<?php
namespace App\Http\Resources;

use Bkstar123\ApiBuddy\Http\Resources\AppResource;

class UsersResource extends AppResource
{
    /**
     * Specify the resource mapping
     *
     * @return array
     */
    protected function resourceMapping()
    {
        return [
            'fullname' => $this->name,
            'mailaddress' => $this->email,
            'creationDate' => (string) $this->created_at,
            'lastChanged' => (string) $this->updated_at,
        ];
    }
    ...
}
```
b) Create user transformer  
For example, ```app/Transformers/UserTransformer.php```

This class defines the mapping between the model's original columns and their transformed versions (mainly for the purpose of client->server direction)  
```php
<?php
namespace App\Transformers;

use Bkstar123\ApiBuddy\Transformers\AppTransformer;

class UserTransformer extends AppTransformer
{
    /**
     * Transformed keys -> Original keys mapping
     *
     * @var array
     */
    protected static $transformedKeys = [
        'fullname' => 'name',
        'mailaddress' => 'email',
        'creationDate' => 'created_at',
        'lastChanged' => 'updated_at',
        'password' => 'password'
    ];

    /**
     * Original keys -> Transformed keys mapping
     *
     * @var array
     */
    protected static $originalKeys = [
        'name' => 'fullname',
        'email' => 'mailaddress',
        'created_at' => 'creationDate',
        'updated_at' => 'lastChanged',
        'password' => 'password'
    ];
}
```

Then, assign the transformer class to ```$transformer``` property of ```App\User``` model  
```php
<?php

namespace App;

use App\Transformers\UserTransformer;
...

class User extends Authenticatable
{
    ...

    /**
     * Assign the associated transformer class name
     *
     * @var string
     */
    public static $transformer = UserTransformer::class;

    ...
}

```

c) ```app/Http/Controllers/UserController.php```  
```php
<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Resources\UsersResource;
use App\Transformers\UserTransformer;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('apibuddy.transform:'. UserTransformer::class)->only('create');
    }

    public function index()
    {
        $modelClass = User::class;
        $apiResource = UsersResource::class;
    	return $this->apiResponser->showCollection(User::getQuery(), $apiResource, $modelClass);
    }

    public function showUser(User $user)
    {
        $apiResource = UsersResource::class;
    	return $this->apiResponser->showInstance($user, $apiResource);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ], [
            'email.email' => 'The email must be valid'
        ]);
        $user = User::create($request->all());
        return $this->apiResponser->successResponse($user, 201);
    }
}

```

For some requests that modify the state of resource such as POST (creating new instance), PUT & PATCH (updating an existing instance), you will need to use ```'apibuddy.transform'``` middleware which are automatically registered with the Laravel IoC container by the package. This middleware only requires an argument which is the fully qualify name of the transform class (in the above example, it should be ```App\Transformers\UserTransformer```)  


### 4.4 CORS enabling

To enable CORS for all API endpoints, just register the middleware ```'apibuddy.cors'``` in ```app/Http/Kernel.php```'s ```$middlewareGroups``` 's ```api``` key as follows:  
```php
<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
	...
    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        ...,

        'api' => [
            'throttle:60,1',
            'bindings',
            'apibuddy.cors', // add this middleware to enable CORS
        ],
    ];

    ...
}
```

This middleware is automatically register with Laravel IoC container by the package.

## 4.5 Consuming API

You can use the following queries to customize the API response:  

a) Sorting  
?sort_by=+col1,-col2  

Sort the response data by col1 in the ascending order & col2 in the descending order  

b) Selecting  
?fields=col1,col2  

Includes only col1 & col2 in the response data  

c) Filtering  
?col1=val1&col2{lte}=val2  

Filter the response data where ```col1 = val1``` and ```col2 <= val2```  

The accepted operators: ```lt, lte, gt, gte, eq, neq``` (defaults to ```eq```)  

d) Paginating  
?limit=10  
?limit=10&page=5  

Paginating the response data with the page size of 10 items and get the page 6  