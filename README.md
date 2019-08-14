# laravel-api-buddy     
> This lightweight Laravel package provides a powerful and simple toolset for quickly building high-quality RESTful API web services for Eloquent model resources with several advanced features such as schema transformation as well as sorting, filtering, selecting and paginating. Using together with the Laravel Passport package, you can have a full-fledge API system ready to serve any clients in a matter of minutes.  

**Note**: This is not a silver bullet to solve all API problems, for example: it does not support ```grouping```, ```having``` queries. It does not “apifyies” your model resources out of the box, it is just your buddy to build a powerful API system as quickly as possible. There are many rooms for you to personalize your API design, laravel-api-buddy makes your journey easier, more comfortable and saves you from doing some boilerplate coding.  

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

Supposing that we need to build some API endpoints for ```users``` resource.  

### 4.1 General information

The package provides ***```\Bkstar123\ApiBuddy\Http\Controllers\ApiController```*** as the base API controller that can be extended by other API controllers. This ```ApiController``` has been automatically injected with an ```Bkstar123\ApiBuddy\Contracts\ApiResponsible``` instance.    

You can quickly scalfold an API controller with ```apibuddy:make --type=controller``` command. For example:  
```php artisan apibuddy:make UserController --type=controller```  

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
     * @param  string $transformerClass
     * @return  \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function showCollection($builder, $apiResource = '', $transformerClass = '');

    /**
     * Show a resource instance
     *
     * @param  \Illuminate\Database\Eloquent\Model  $instance
     * @param  string $apiResource
     * @param  int $code
     * @return  \Illuminate\Http\JsonResponse
     */
    public function showInstance(Model $instance, $apiResource = '', $code = 200) : \Illuminate\Http\JsonResponse;
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
$eloquentBuilder = User::getQuery();
$queryBuilder = DB::table('users');

// You can further add more query scope or modifying the builder before passing it to showCollection()
```

The following arguments are to be passed only in the case of using transformation:  
- **```$apiResource```**: fully qualified class name of the model API resource. See more about API Resources at https://laravel.com/docs/5.8/eloquent-resources  
- **```$transformerClass```**: the fully qualified class name of the model transforming class, such as ```App\Transformers\UserTransformer```. The transforming class must extend ```\Bkstar123\ApiBuddy\Transformers\AppTransformer``` and defined the following properties:  
+) ```protected static $transformedKeys;```  
+) ```protected static $originalKeys;```  

### 4.2 Without transformation 

- Set ```useTransform``` option to ```false``` in ```/config/bkstar123_apibuddy.php```  
- Run ```php artisan apibuddy:make UserController --type=controller```  
- In ***app/Http/Controllers/UserController.php***, assuming that ```index()``` returns a collection of user resources, ```showUser()``` returns an user instance and ```create()``` creates a new user instance  
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
        return $this->apiResponser->showInstance($user, 201);
    }
}
```

### 4.3 With transformation

Set ```useTransform``` option to ```true``` in ```/config/bkstar123_apibuddy.php```  

***a) Create API resource***  
- ```php artisan apibuddy:make UsersResource --type=resource```  

The ```UsersResource``` API resource will be created in ```/app/Http/Resources``` directory, it extends ```Bkstar123\ApiBuddy\Http\Resources\AppResource```  

- The only required method for it to implement is **```resourceMapping()```**, this method defines the way to transform the API response (server->client direction)  
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

You can add more metadata to API response by using ```afterFilter()``` hook which accepts the mapping returned by ```resourceMapping()``` as the only argument, enrich & return it, for example:  
```php
<?php
namespace App\Http\Resources;

use Bkstar123\ApiBuddy\Http\Resources\AppResource;

class UsersResource extends AppResource
{
    ...
    
    protected function afterFilter($mapping)
    {
        $mapping = array_merge($mapping, [
            'links' => [
                [
                    'self' => 'this route',
                    'href' => '/to/this/route'
                ],
                [
                    'rel' => 'that route',
                    'href' => 'to/that/route'
                ]
            ]
        ]);

        return $mapping;
    }
}
```

***b) Create transformer***  
Run: ```php artisan apibuddy:make UserTransformer --type=transformer```

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
}
```

***c) ```app/Http/Controllers/UserController.php```***
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
        $transformerClass = UserTransformer::class;
        $apiResource = UsersResource::class;
        return $this->apiResponser->showCollection(User::getQuery(), $apiResource, $transformerClass);
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
        $apiResource = UsersResource::class;
        return $this->apiResponser->showInstance($user, $apiResource, 201);
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

### 4.5 Consuming API

You can use the following queries to customize the API response:  

***a) Sorting***  
```?sort_by=+col1,-col2```  

Sort the response data by col1 in the ascending order & col2 in the descending order  

***b) Selecting***  
```?fields=col1,col2```   

Includes only col1 & col2 in the response data  

***c) Filtering***  
```?col1=val1&col2{lte}=val2```   

Filter the response data where ```col1 = val1``` and ```col2 <= val2```  

The accepted operators: ```lt, lte, gt, gte, eq, neq``` (defaults to ```eq```)  

***d) Paginating***  
```?limit=10```  
```?limit=10&page=5```    

Paginating the response data with the page size of 10 items and get the page 6  

## 5. Build an example API system using laravel-api-buddy and Laravel Passport packages

This example demonstrates how easily & quickly you can build an API system using bkstar123/laravel-api-buddy and protect it using Laravel Passport.  

We will build the following API endpoints:  
- GET ```/posts```: list all the posts  
- GET ```/posts/post-slug```: show a post of the given slug  
- GET ```/posts/post-slug/tags```: list all tags of the given post  
- GET ```/posts/post-slug/users```: get the owner of a post of the given slug
- POST ```/posts```: create a new post  
- PUT ```/posts/post-slug```: update a post of the given slug  
- DELETE ```/posts/post-slug```: delete a post of the given slug

- GET ```/tags```: list all the tags  
- GET ```/tags/tag-slug```: show a tag of the given slug  
- GET ```/tags/tag-slug/posts```: list all posts of the given tag  
- POST ```/tags```: create a new tag  
- PUT ```/tags/tag-slug```: update a tag of the given slug  
- DELETE ```/tags/tag-slug```: delete a tag of the given slug  

- GET ```/users```: list all the users  
- GET ```/users/email```: show a user  
- GET ```/users/email/posts```: list all posts of the given user  

### 5.1 Application Scalfolding

Our imaginary system consists of ```users```, ```tags``` and ```posts```. Their relationships are as follows:  
- A user can create many posts  
- A post can be created by one user  
- A tag can be placed on zero or many posts  
- A post can have zero or multiple tags  

#### 5.1.1 Create posts and tags migrations
- ```php artisan make:migration create_posts_table --table=posts```  
- ```php artisan make:migration create_tags_table --table=tags```  
- Pivot table: ```php artisan make:migration create_post_tag_table --table=post_tag```  

***a) Posts migration***  
```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('slug');
            $table->text('content');
            $table->boolean('published')->default(false);
            $table->bigInteger('user_id')->unsigned()->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}

```  

***b) Tags migration***  
```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
}

```  

***c) Pivot table migration***  
```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_tag', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('post_id');
            $table->integer('tag_id');
            $table->timestamps();

            $table->unique(['post_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_tag');
    }
}

```  

#### 5.1.2 Migration

Run ```php artisan migrate```  

#### 5.1.3 Create models

***a) User***  
```php
<?php

namespace App;

use App\Post;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'email';
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

```  
***b) Post***  
```php
<?php

namespace App;

use App\Tag;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'slug', 'content', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}

```  

***c)Tag***  
```php
<?php

namespace App;

use App\Post;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'slug',
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}

```

#### 5.1.4 Create factories

***a) PostFactory***  
```php
<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Post::class, function (Faker $faker) {
    $title = $faker->sentence;

    return [
        'user_id' => function() {
            return factory(App\User::class)->create()->id;
        },
        'title' => $title,
        'slug' => str_slug($title, '-').'-'.time().'-'.mt_rand(0, 100),
        'published' => $faker->boolean(50),
        'content' => $faker->paragraph,
    ];
});

```  

***b) TagFactory***  
```php
<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Tag::class, function (Faker $faker) {
    $name = $faker->sentence(3);

    return [
        'name' => $name,
        'slug' => str_slug($name, '-').'-'.time().'-'.mt_rand(0, 100),
        'description' => $faker->paragraph,
    ];
});

```  

***c) UserFactory***  
```php
<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ];
});

```  

Finally, populating faked data as follows
- ```php artisan tinker```   
- ```factory(App\User::class,5)->create()```  
- ```factory(App\Post::class,50)->create()```   
- ```factory(App\Tag::class,10)->create()```  

- ```for ($i = 1; $i <=50;  $i++) {$post = App\Post::all()->random();$tag = App\Tag::all()->random();try {DB::insert('insert into post_tag (post_id, tag_id) values (?, ?)',[$post->id, $tag->id]);} catch (\Exception $e) {}}``` (populating the pivot table)


#### 5.1.5 Authentication scalfolding

- ```php artisan make:auth``` 

### 5.2 Create API endpoints

***Add prefix to API endpoints in routes/api.php***  

```php
<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function () {
    // Define API routes here
});
```

***Create controllers, resources and transformers***  
- ```php artisan apibuddy:make PostController --type=controller```  
- ```php artisan apibuddy:make PostResource --type=resource```  
- ```php artisan apibuddy:make PostTransformer --type=transformer```  

- ```php artisan apibuddy:make TagController --type=controller```  
- ```php artisan apibuddy:make TagResource --type=resource```  
- ```php artisan apibuddy:make TagTransformer --type=transformer```  

- ```php artisan apibuddy:make UserController --type=controller```  
- ```php artisan apibuddy:make UserResource --type=resource```  
- ```php artisan apibuddy:make UserTransformer --type=transformer```  

***PostResource***  
```php
<?php
/**
 * PostResource resource
 */
namespace App\Http\Resources;

use Bkstar123\ApiBuddy\Http\Resources\AppResource;

class PostResource extends AppResource
{
    /**
     * Specify the resource mapping
     *
     * @return array
     */
    protected function resourceMapping()
    {
        return [
            'title' => $this->title,
            'body' => $this->content,
            'postSlug' => $this->slug,
            'visible' => $this->published,
            'created' => (string) $this->created_at,
            'updated' => (string) $this->updated_at,
        ];
    }

    protected function afterFilter($mapping)
    {
        if (!empty($this->slug)) {
            $mapping = array_merge($mapping, [
                'links' => [
                    [
                        'rel' => 'self',
                        'href' => route('posts.show', $this->slug),
                    ],
                    [
                        'rel' => 'tags',
                        'href' => route('post.tags.index', $this->slug),
                    ],
                    [
                        'rel' => 'owner',
                        'href' => route('post.owner.show', $this->slug),
                    ],
                ],
            ]);
        }

        return $mapping;
    }
}
```  
***TagResource***  
```php
<?php
/**
 * TagResource resource
 */
namespace App\Http\Resources;

use Bkstar123\ApiBuddy\Http\Resources\AppResource;

class TagResource extends AppResource
{
    /**
     * Specify the resource mapping
     *
     * @return array
     */
    protected function resourceMapping()
    {
        return [
            'tag' => $this->name,
            'description' => $this->description,
            'tagSlug' => $this->slug,
            'created' => (string) $this->created_at,
            'updated' => (string) $this->updated_at,
        ];
    }

    protected function afterFilter($mapping)
    {
        if (!empty($this->slug)) {
            $mapping = array_merge($mapping, [
                'links' => [
                    [
                        'rel' => 'self',
                        'href' => route('tags.show', $this->slug),
                    ],
                    [
                        'rel' => 'posts',
                        'href' => route('tag.posts.index', $this->slug),
                    ],
                ],
            ]);
        }

        return $mapping;
    }
}
```  

***UserResource***  
```php
<?php
/**
 * UserResource resource
 */
namespace App\Http\Resources;

use Bkstar123\ApiBuddy\Http\Resources\AppResource;

class UserResource extends AppResource
{
    /**
     * Specify the resource mapping
     *
     * @return array
     */
    protected function resourceMapping()
    {
        return [
            'user' => $this->name,
            'mailaddress' => $this->email,
            'created' => (string) $this->created_at,
            'updated' => (string) $this->updated_at,
        ];
    }

    protected function afterFilter($mapping)
    {
        if (!empty($this->email)) {
            $mapping = array_merge($mapping, [
                'links' => [
                    [
                        'rel' => 'self',
                        'href' => route('users.show', $this->email),
                    ],
                    [
                        'rel' => 'posts',
                        'href' => route('user.posts.index', $this->email),
                    ],
                ],
            ]);
        }

        return $mapping;
    }
}
```  

***PostTransformer***
```php
<?php
/**
 * PostTransformer transformer
 */
namespace App\Transformers;

use Bkstar123\ApiBuddy\Transformers\AppTransformer;

class PostTransformer extends AppTransformer
{
    /**
     * Transformed keys -> Original keys mapping
     *
     * @var array
     */
    protected static $transformedKeys = [
        'title' => 'title',
        'body' => 'content',
        'postSlug' => 'slug',
        'visible' => 'published',
        'created' => 'created_at',
        'updated' => 'updated_at',
        'owner' => 'user_id',
    ];
}
```  

***TagTransformer***  
```php
<?php
/**
 * TagTransformer transformer
 */
namespace App\Transformers;

use Bkstar123\ApiBuddy\Transformers\AppTransformer;

class TagTransformer extends AppTransformer
{
    /**
     * Transformed keys -> Original keys mapping
     *
     * @var array
     */
    protected static $transformedKeys = [
        'tag' => 'name',
        'description' => 'description',
        'tagSlug' => 'slug',
        'created' => 'created_at',
        'updated' => 'updated_at'
    ];
}
```  

***UserTransformer***  
```php
<?php
/**
 * UserTransformer transformer
 */
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
        'user' => 'name',
        'mailaddress' => 'email',
        'password' => 'password',
        'created' => 'created_at',
        'updated' => 'updated_at'
    ];
}
```  

#### 5.2.1 List all the posts

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::get('posts', 'PostController@getAllPosts')->name('posts.index');
    // ...
});

```  

***b) PostController***  
```php
<?php
/**
 * PostController API controller
 */
namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use App\Transformers\PostTransformer;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class PostController extends Controller
{
    // ...

    public function getAllPosts()
    {
        return $this->apiResponser->showCollection(Post::getQuery()), PostResource::class, PostTransformer::class);
    }

    // ...
}
```  
***c) Queries***  
```bash
curl -X GET /api/v1/posts
curl -X GET /api/v1/posts\?limit=10 
curl -X GET /api/v1/posts\?fields=title,postSlug
curl -X GET /api/v1/posts\?sort_by=created,-title
curl -X GET /api/v1/posts\?postSlug=your-post-slug
curl -X GET /api/v1/posts\?created{lte}=2019-08-10%2019:22:30
```  

#### 5.2.2 Show a post of the given slug

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::get('posts/{post}', 'PostController@getPost')->name('posts.show');
    // ...
});

```  

***b) PostController***  
```php
<?php
/**
 * PostController API controller
 */
namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use App\Transformers\PostTransformer;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class PostController extends Controller
{
    // ...

    public function getPost()
    {
        if (empty($post)) {
            return $this->apiResponser->errorResponse('There is no resource of the given identificator', 404);
        }
        return $this->apiResponser->showInstance($post, PostResource::class);
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X GET /api/v1/posts/{post-slug}
curl -X GET /api/v1/posts/{post-slug}\?fields=title,postSlug
```  

#### 5.2.3 List all tags of the given post 

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::get('posts/{post}/tags', 'PostController@getPostTags')->name('post.tags.index');
    // ...
});

```  

***b) PostController***  
```php
<?php
/**
 * PostController API controller
 */
namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Resources\TagResource;
use App\Transformers\TagTransformer;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class PostController extends Controller
{
    // ...

    public function getPostTags(Post $post)
    {
        if (empty($post)) {
            return $this->apiResponser->errorResponse('There is no resource of the given identificator', 404);
        }
        return $this->apiResponser->showCollection($post->tags()->getQuery(), TagResource::class, TagTransformer::class);
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X GET /api/v1/posts/{post-slug}/tags # you can also apply sorting, filtering, paginating and selecting queries
```  

#### 5.2.4 Get the owner of a post of the given slug

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::get('posts/{post}/users', 'PostController@getPostOwner')->name('post.owner.show');
    // ...
});

```  

***b) PostController***  
```php
<?php
/**
 * PostController API controller
 */
namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class PostController extends Controller
{
    // ...

    public function getPostOwner(Post $post)
    {
        if (empty($post)) {
            return $this->apiResponser->errorResponse('There is no resource of the given identificator', 404);
        }
        return $this->apiResponser->showInstance($post->user()->first(), UserResource::class);
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X GET /api/v1/posts/{post-slug}/users # you can also apply selecting query
```  

#### 5.2.5 Create a new post 

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::post('posts', 'PostController@createPost')->name('posts.create');
    // ...
});

```  

***b) PostController***  
```php
<?php
/**
 * PostController API controller
 */
namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use App\Transformers\PostTransformer;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class PostController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('apibuddy.transform:'. PostTransformer::class)->only('createPost');
    }

    // ...

    public function createPost(Request $request)
    {
        $request->validate([
            'title' => 'required|min:5|max:255',
            'content' => 'required|min:5|max:255',
        ]);

        $postData = request()->all();
        $postData['user_id'] = 1; // it will later be changed to the current token-based authenticated user
        $postData['slug'] = str_slug($postData['title'], '-').'-'.time().'-'.mt_rand(0, 100);
        $post = Post::create($postData);
        return $this->apiResponser->showInstance($post->fresh(), PostResource::class, 201);
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X POST /api/v1/posts \
     -H 'Content-Type: application/x-www-form-urlencoded' \
     -d 'title=New%20Post&body=Very%20nice%20post'
```  

#### 5.2.6 Update a post of the given slug

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::put('posts/{post}', 'PostController@updatePost')->name('posts.update');
    // ...
});

```  

***b) PostController***  
```php
<?php
/**
 * PostController API controller
 */
namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use App\Transformers\PostTransformer;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class PostController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('apibuddy.transform:'. PostTransformer::class)->only('createPost', 'updatePost');
    }

    // ...

    public function updatePost(Request $request, Post $post)
    {
        if (empty($post)) {
            return $this->apiResponser->errorResponse('There is no resource of the given identificator', 404);
        }
        $request->validate([
            'title' => 'min:5|max:255',
            'content' => 'min:5|max:255',
        ]);
        if (empty($request->title) && empty($request->content)) {
            return $this->apiResponser->successResponse('Nothing to change', 200);
        }
        if ($post->update($request->all())) {
            return $this->apiResponser->showInstance($post->fresh(), PostResource::class, 200);
        } else {
            return $this->apiResponser->errorResponse('Unknown error occurred');
        }
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X PUT /api/v1/posts/{post-slug} \
     -H 'Content-Type: application/x-www-form-urlencoded' \
     -d 'title=New%20Post&body=Very%20nice%20post'
```  

**Note**: You must submit PUT request with the header ```Content-Type: application/x-www-form-urlencoded```  

#### 5.2.7 Delete a post of the given slug

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::delete('posts/{post}', 'PostController@deletePost')->name('posts.destroy');
    // ...
});

```  

***b) PostController***  
```php
<?php
/**
 * PostController API controller
 */
namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class PostController extends Controller
{
    // ...

    public function deletePost(Request $request, Post $post)
    {
        if (empty($post)) {
            return $this->apiResponser->errorResponse('There is no resource of the given identificator', 404);
        }
        $post->tags()->detach();
        if ($post->delete()) {
            return $this->apiResponser->successResponse('The resource of the given identificator has been permanently destroyed', 200);
        }

        return $this->apiResponser->errorResponse('Unknown error occurred');
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X DELETE /api/v1/posts/{post-slug}
```  

#### 5.2.8 List all the tags

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::get('tags', 'TagController@getAllTags')->name('tages.index');
    // ...
});

```  

***b) TagController***  
```php
<?php
/**
 * TagController API controller
 */
namespace App\Http\Controllers;

use App\Tag;
use Illuminate\Http\Request;
use App\Http\Resources\TagResource;
use App\Transformers\TagTransformer;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class TagController extends Controller
{
    // ...

    public function getAllTags()
    {
        return $this->apiResponser->showCollection(Tag::getQuery(), TagResource::class, TagTransformer::class);
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X GET /api/v1/tags
curl -X GET /api/v1/tags\?limit=10 
curl -X GET /api/v1/tags\?fields=tag,tagSlug
curl -X GET /api/v1/tags\?sort_by=created,-tag
curl -X GET /api/v1/tags\?tagSlug=your-tag-slug
curl -X GET /api/v1/tags\?created{lte}=2019-08-10%2019:22:30
```  

#### 5.2.9 Show a tag of the given slug

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::get('tags/{tag}', 'TagController@getTag')->name('tags.show');
    // ...
});

```  

***b) TagController***  
```php
<?php
/**
 * TagController API controller
 */
namespace App\Http\Controllers;

use App\Tag;
use Illuminate\Http\Request;
use App\Http\Resources\TagResource;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class TagController extends Controller
{
    // ...

    public function getTag(Tag $tag)
    {
        if (empty($tag)) {
            return $this->apiResponser->errorResponse('There is no resource of the given identificator', 404);
        }
        return $this->apiResponser->showInstance($tag, TagResource::class);
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X GET /api/v1/tags/{tag-slug}
curl -X GET /api/v1/tags/{tag-slug}\?fields=tag,tagSlug
```  

#### 5.2.10 List all posts of the given tag 

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::get('tags/{tag}/posts', 'TagController@getTagPosts')->name('tag.posts.index');
    // ...
});

```  

***b) TagController***  
```php
<?php
/**
 * TagController API controller
 */
namespace App\Http\Controllers;

use App\Tag;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use App\Transformers\PostTransformer;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class TagController extends Controller
{
    // ...

    public function getTagPosts(Tag $tag)
    {
        if (empty($tag)) {
            return $this->apiResponser->errorResponse('There is no resource of the given identificator', 404);
        }
        return $this->apiResponser->showCollection($tag->posts()->getQuery(), PostResource::class, PostTransformer::class);
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X GET /api/v1/tags/{tag-slug}/posts # you can also apply sorting, filtering, paginating and selecting queries
```  

#### 5.2.11 Create a new tag 

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::post('tags', 'TagController@createTag')->name('tags.create');
    // ...
});

```  

***b) TagController***  
```php
<?php
/**
 * TagController API controller
 */
namespace App\Http\Controllers;

use App\Tag;
use Illuminate\Http\Request;
use App\Http\Resources\TagResource;
use App\Transformers\TagTransformer;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class TagController extends Controller
{
    // ...

    public function __construct()
    {
        parent::__construct();
        $this->middleware('apibuddy.transform:'. TagTransformer::class)->only('createTag');
    }

    public function createTag(Request $request)
    {
        $request->validate([
            'name' => 'required|min:5|max:255',
            'description' => 'required|min:5|max:255',
        ]);

        $tagData = request()->all();
        $tagData['slug'] = str_slug($tagData['name'], '-').'-'.time().'-'.mt_rand(0, 100);
        $tag = Tag::create($tagData);
        return $this->apiResponser->showInstance($tag->fresh(), TagResource::class, 201);
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X POST /api/v1/tags \
     -H 'Content-Type: application/x-www-form-urlencoded' \
     -d 'tag=New%20Tag&description=Very%20nice%20tag'
```  

#### 5.2.12 Update a tag of the given slug 

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::put('tags/{tag}', 'TagController@updateTag')->name('tags.update');
    // ...
});

```  

***b) TagController***  
```php
<?php
/**
 * TagController API controller
 */
namespace App\Http\Controllers;

use App\Tag;
use Illuminate\Http\Request;
use App\Http\Resources\TagResource;
use App\Transformers\TagTransformer;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class TagController extends Controller
{
    // ...

    public function __construct()
    {
        parent::__construct();
        $this->middleware('apibuddy.transform:'. TagTransformer::class)->only('createTag', 'updateTag');
    }

    public function updateTag(Request $request, Tag $tag)
    {
        if (empty($tag)) {
            return $this->apiResponser->errorResponse('There is no resource of the given identificator', 404);
        }
        $request->validate([
            'name' => 'min:5|max:255',
            'description' => 'min:5|max:255',
        ]);
        if (empty($request->name) && empty($request->description)) {
            return $this->apiResponser->successResponse('Nothing to change', 200);
        }
        if ($tag->update($request->all())) {
            return $this->apiResponser->showInstance($tag->fresh(), TagResource::class, 200);
        } else {
            return $this->apiResponser->errorResponse('Unknown error occurred');
        }
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X PUT /api/v1/tags/{tag-slug} \
     -H 'Content-Type: application/x-www-form-urlencoded' \
     -d 'tag=New%20Tag&description=Very%20nice%20tag'
```  

**Note**: You must submit PUT request with the header ```Content-Type: application/x-www-form-urlencoded```  

#### 5.2.13 Delete a tag of the given slug

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::delete('tags/{tag}', 'TagController@deleteTag')->name('tags.destroy');
    // ...
});

```  

***b) TagController***  
```php
<?php
/**
 * TagController API controller
 */
namespace App\Http\Controllers;

use App\Tag;
use Illuminate\Http\Request;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class TagController extends Controller
{
    // ...

    public function deleteTag(Request $request, Tag $tag)
    {
        if (empty($tag)) {
            return $this->apiResponser->errorResponse('There is no resource of the given identificator', 404);
        }
        $tag->posts()->detach();
        if ($tag->delete()) {
            return $this->apiResponser->successResponse('The resource of the given identificator has been permanently destroyed', 200);
        }

        return $this->apiResponser->errorResponse('Unknown error occurred');
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X DELETE /api/v1/tags/{tag-slug}
```  

#### 5.2.14 List all the users 

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::get('users', 'UserController@getAllUsers')->name('users.index');
    // ...
});

```  

***b) UserController***  
```php
<?php
/**
 * UserController API controller
 */
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Transformers\UserTransformer;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class UserController extends Controller
{
    // ...

    public function getAllUsers()
    {
        return $this->apiResponser->showCollection(User::getQuery(), UserResource::class, UserTransformer::class);
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X GET /api/v1/users
curl -X GET /api/v1/users\?limit=10 
curl -X GET /api/v1/users\?fields=name,mailaddress
curl -X GET /api/v1/users\?sort_by=created,-name
curl -X GET /api/v1/users\?mailaddress=yourmail@example.com
curl -X GET /api/v1/users\?created{lte}=2019-08-10%2019:22:30
```  

#### 5.2.15 Show a user 

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::get('users/{user}', 'UserController@getUser')->name('users.show');
    // ...
});

```  

***b) UserController***  
```php
<?php
/**
 * UserController API controller
 */
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class UserController extends Controller
{
    // ...

    public function getUser(User $user)
    {
        if (empty($user)) {
            return $this->apiResponser->errorResponse('There is no resource of the given identificator', 404);
        }
        return $this->apiResponser->showInstance($user, UserResource::class);
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X GET /api/v1/users/{email}
curl -X GET /api/v1/users/{email}\?fields=name,mailaddress
```  

#### 5.2.16 List all posts of the given user 

***a) Define API endpoint***  
```php
<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {
    // ...
    Route::get('users/{user}/posts', 'UserController@getUserPosts')->name('user.posts.index');
    // ...
});

```  

***b) UserController***  
```php
<?php
/**
 * UserController API controller
 */
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use App\Transformers\PostTransformer;
use Bkstar123\ApiBuddy\Http\Controllers\ApiController as Controller;

class UserController extends Controller
{
    // ...

     public function getUserPosts(User $user)
    {
        if (empty($user)) {
            return $this->apiResponser->errorResponse('There is no resource of the given identificator', 404);
        }
        return $this->apiResponser->showCollection($user->posts()->getQuery(), PostResource::class, PostTransformer::class);
    }

    // ...
}
```  

***c) Queries***  
```bash
curl -X GET /api/v1/users/{email}/posts # you can also apply sorting, filtering, paginating and selecting queries
```  

### 5.3 Protect your API endpoints with Laravel Passport

Refer to https://laravel.com/docs/5.8/passport & https://oauth2.thephpleague.com/terminology/ for further details.  

#### 5.3.1 Install and configure Laravel Passport

- ```composer require laravel/passport```  

- ```php artisan migrate```  

- ```php artisan passport:install```  

This command will create two clients for you: ***personal access*** & ***password grant*** clients

- Add ```Laravel\Passport\HasApiTokens``` to ```App\User``` model.  

- Add ```Passport::routes()``` method within the boot method of your ```AuthServiceProvider```  

- Finally, in your ```config/auth.php``` configuration file, you should set the driver option of the api authentication guard to ```passport```. This will instruct your application to use Passport's  TokenGuard when authenticating incoming API requests.  

- When deploying Passport to your production servers for the first time, you will likely need to run the ```passport:keys``` command:  
```php artisan passport:keys```  

- By default, Passport issues long-lived access tokens that expire after one year. If you would like to configure a longer/shorter token lifetime, you may use the ```tokensExpireIn```,  ```refreshTokensExpireIn```, and ```personalAccessTokensExpireIn``` methods. These methods should be called from the boot method of your ```AuthServiceProvider```, for example:  

```php
/**
 * Register any authentication / authorization services.
 *
 * @return void
 */
public function boot()
{
    $this->registerPolicies();

    Passport::routes();

    Passport::tokensExpireIn(now()->addDays(15));

    Passport::refreshTokensExpireIn(now()->addDays(30));

    Passport::personalAccessTokensExpireIn(now()->addMonths(6));
}
```  

#### 5.3.2 Secure API routes

There are two middleware that you can use to secure API routes:  
- **```auth:api```**    
- **```Laravel\Passport\Http\Middleware\CheckClientCredentials```**    

The latter can be use by placing an alias to the ```$routeMiddleware``` property of your ```app/Http/Kernel.php``` file:  
```php
use Laravel\Passport\Http\Middleware\CheckClientCredentials;

protected $routeMiddleware = [
    'client' => CheckClientCredentials::class,
];
```  

Then, use ```client```middleware to protect your required API routes or controller methods.  

&ndash; ```CheckClientCredentials::class``` provides the lowest level of protection, it only verifies the client itself and does not care about the client owner's perspective. So, this middleware is suitable for machine-to-machine authentication. For example, you might use this grant type in a scheduled job which is performing maintenance tasks over an API. This grant type can be used for any client, however, it is recommended to create a dedicated client with ```php artisan passport:client --client```because this client does not need to represent any user.  

&ndash; ```auth:api```not only verifies the client, but also its owner's perspective. Therefore, this middleware is suitable for verifying a human authentication.  

##### 5.3.2.1 Using CheckClientCredentials::class middleware

This section will demonstrate how to use ```CheckClientCredentials::class``` middleware to protect all the GET API routes, as follows:  
```php
<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'v1'], function () {
    Route::get('posts', 'PostController@getAllPosts')->name('posts.index')->middleware('client');
    Route::get('posts/{post}', 'PostController@getPost')->name('posts.show')->middleware('client');
    Route::get('posts/{post}/tags', 'PostController@getPostTags')->name('post.tags.index')->middleware('client');
    Route::get('posts/{post}/users', 'PostController@getPostOwner')->name('post.owner.show')->middleware('client');
    
    Route::get('tags', 'TagController@getAllTags')->name('tages.index')->middleware('client');
    Route::get('tags/{tag}', 'TagController@getTag')->name('tags.show')->middleware('client');
    Route::get('tags/{tag}/posts', 'TagController@getTagPosts')->name('tag.posts.index')->middleware('client');
    
    Route::get('users', 'UserController@getAllUsers')->name('users.index')->middleware('client');
    Route::get('users/{user}', 'UserController@getUser')->name('users.show')->middleware('client');
    Route::get('users/{user}/posts', 'UserController@getUserPosts')->name('user.posts.index')->middleware('client');

    // ...Other routes
});
```  

&ndash; After that, you will no longer be able to access the above endpoints like ```curl -X GET /api/v1/tags```  

&ndash; Instead, you will need to get the ***client credentials grant type***<sup>(1)</sup> access token, and send the received token with every request to the above endpoints  

```bash
curl -X POST /oauth/token \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  -d 'client_id=1&client_secret=4rfurOhtDaxGkHEPSL73R6Ujl3GRXAFAyyHZZhDu&grant_type=client_credentials'

curl -X GET /api/v1/tags \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImM3NGVjZTVmMmJlZTQyM2Q2ZmE5NDBmYjhkODkxOTkxNWU4OGI2YmZjMTQ4NmYyYzQzZWU0YThlYzc2ZTNlYTE4MWNjZDgwNmE1ZDQ0MDc3In0.eyJhdWQiOiIxIiwianRpIjoiYzc0ZWNlNWYyYmVlNDIzZDZmYTk0MGZiOGQ4OTE5OTE1ZTg4YjZiZmMxNDg2ZjJjNDNlZTRhOGVjNzZlM2VhMTgxY2NkODA2YTVkNDQwNzciLCJpYXQiOjE1NjU1MTkzNzUsIm5iZiI6MTU2NTUxOTM3NSwiZXhwIjoxNTY2ODE1Mzc1LCJzdWIiOiIiLCJzY29wZXMiOltdfQ.ba-YHda7qk0awO4wMX2FId1c29a-WKNTbsMLMjfDgl2cgus6sJB1Q-FDZOKVZ6cXiQXqmMfp4H_QFfwMGo4RIltARzx93QND3G8Q7pVCQESJw1eK2cKAAAXSHo0-ooS33t0GpAUM1_IYv9VsoMiWc2MkD2xTwl0Z1nMhLwgxJ5_bruVgotZi11O5zXL9xTfGkB6t9OTrAWoqCZ8JT89VR-gUwBhB5vCGCDIXXTWzxFAMjpzC3N9wB-VeS1-FWnjNd_qGPMP2eTBKqbHrARgGbjnSO8CQwQGpHxpegDcT06KB5l6QxqEXJD5iRkmmZ6q6uNuGoPy-PhEMbYwzcsYGAatsBRCGAfP5yfSoy2fWD01Jw62s5zaqot2L2fuyD2r9iGsIbXkKbGTiyxdPaQl3x3qZ-wbJWUPLj4Af4MN1URoM-bbrD94W8IgOp6k_CVDgySm7uYmvbMWo3mjoXHbYnY8SA5k-8GVec3uDW-o-p8IjQJsiWnnod8K4nMjHg3BCul4WTxVpfJhqQzRXflhstc818dmzzutGxvy0abmQ5wuC-Q8AcCIpXing6TPrAkyATry_-nQzjeoMGFHWaBByOn-mfk-y7YRgae4FRds3vSWAf5j21Adiuq3BwAE6HUf0VlQ-kVXkKDUorM3lklLpYecXlLW0QQ1GZlDsvKxI83g'
```  

##### 5.3.2.2 Using auth:api middleware

This section will demonstrate how to use ```auth:api``` middleware to protect all POST, PUT and DELETE API routes, as follows:  
```php
<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'v1'], function () {
    Route::post('posts', 'PostController@createPost')->name('posts.create')->middleware('auth:api');
    Route::put('posts/{post}', 'PostController@updatePost')->name('posts.update')->middleware('auth:api');
    Route::delete('posts/{post}', 'PostController@deletePost')->name('posts.destroy')->middleware('auth:api');

    Route::post('tags', 'TagController@createTag')->name('tags.create')->middleware('auth:api');
    Route::put('tags/{tag}', 'TagController@updateTag')->name('tags.update')->middleware('auth:api');
    Route::delete('tags/{tag}', 'TagController@deleteTag')->name('tags.destroy')->middleware('auth:api');
});
```  

&ndash; The above API routes cannot be accessed by client credentials grant type access tokens like **5.3.2.1**.  

&ndash; Instead, you will need to get a token of one of the following types, and send the received token with every request to the above endpoints:  
- ***Password grant type***<sup>(2)</sup>  
- ***Authorization code grant type***<sup>(3)</sup>  
- ***Implicit grant type***<sup>(4)</sup>  
- ***Refresh token grant type***<sup>(5)</sup>  
- ***Personal access grant type***<sup>(6)</sup>  

**Note**: ```(2), (3), (4), (5), (6)``` grant type access tokens can also access the API routes protected by ```CheckClientCredentials::class``` middleware.  

***a) Password grant type access token***  

Password grant allows your other first-party clients, such as a mobile application, to obtain an access token using an e-mail address/username and password. This allows you to issue access tokens securely to your first-party clients without requiring your users to go through the entire OAuth2 authorization code redirect flow.  

```bash
curl -X POST /oauth/token \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  -d 'client_id=2&client_secret=zCAzHbVRbcQFaWRSH4SEN8IU189ieiGCzbHdyaU7&grant_type=password&username=aiden15%40example.net&password=password'

curl -X POST /api/v1/posts \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImE4MGI3M2JlNzcyNjg5NTRlMTg3ZWRmZDQ2ZGZjYjE2NjJkODg4OTMxM2VhMzE0MzJhYWIyNzBjMTQ4ZjhlMDNmZWM1ZmI2NGY1N2FmNGFjIn0.eyJhdWQiOiIyIiwianRpIjoiYTgwYjczYmU3NzI2ODk1NGUxODdlZGZkNDZkZmNiMTY2MmQ4ODg5MzEzZWEzMTQzMmFhYjI3MGMxNDhmOGUwM2ZlYzVmYjY0ZjU3YWY0YWMiLCJpYXQiOjE1NjU1NzQyODEsIm5iZiI6MTU2NTU3NDI4MSwiZXhwIjoxNTY2ODcwMjgxLCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.UAgSA7fWGL4fLlOCjo9Kl0KauhKB72lFFsFS_fvsxlsvCyUUmnUamsJXVPQVGjkZ1dk-uKKYsUZYXZe9dWLQoOocqoyn9K0syaAIpDE2bfWFjHrc45CtHyQ_DYi6OctVvphiXl6LHqu4b_vLqMMoKtlTQZuxV9M8eIw2bn8VCxKl5EGMq9kmcaBlorvOD_va3VQN1_uh1zk_j4C5Xdx39l1S_SbvA7fdLWVChIY7Bzgos_iTryfbd8nsyxATkB28i5dz_0RQtm_E56RR3bhSrtwJwMGXolQZd4INhN89F4C4rxp-8I6jU7S5ZGOGFWA04qYnwBQtWYdD12VPAYNFbVsFt4NXnWNqibG92w4LpSJcM5ofO2Jx8EbChTf9TfhZspUntMfrYO9epXKMldOL_U5Cr3lPtByJ7shxIfz1OgDo353jNAUHTQBjT_eC_GO0tu7hBycKv1v-28s4JbxQqfrz1-hOSnDbduKNITxn1zt1LNTvqtNjC0AoNo7DgwjAgRk1kdcPl1LqIxHcClii5goVmWBSk00N3HjfdI5JxVPoMcKTn71H9Ite5ZWPeC_iFNT0OpbyDVg8v_AW9YCt69dQvDCB_xLtReBON67OurihQqbrp5X2r-MMSfGy0gWW4b9e0CgX4GGwlWJzmQFpRmbn0JUvo4YoYMtBKX9w0Mo' \
  -d 'title=Hello%20world&body=Hello%2C%20my%20name%20is%20Antony%20H'
```  

***b) Authorization code grant type access token***  

For the third-party applications that want to use your APIs, they need to go through an OAUTH2 flow with authorization code. You may be familiar with this process when implementing social login feature via Facebook, Twitter, Google etc...  

Our flow here is the same:  
- **Step 1**: The third party owner/developer needs to register an account with your application  
- **Step 2**: You must provide a dashboard allowing the third party owner/developer to create a client associated with his account (created in step 1). Creating this client requires the third party owner/developer to provide a callback URL  
- **Step 3**: Then, the third party application redirects a user to your application where the user can approve/deny the request to issue an access token to the client  
- **Step 4**: If the user approves the request, then your application should return an authorization code and redirect the user to the third party's callback URL (as provided in step 2)  
- **Step 5**: The third party application will use the given authorization code to exchange for an access token. This token represents an user of your application who approves the request in step 4. It has a capability to do whatever the user can do in your application, you can limit this capability by applying token scopes (authorization)  

Passport provides JSON APIs out of the box for managing clients, you will need to build your own front-end to allow users to create/remove clients & customize the Passport view for approval/denial of the authorization request.  

For the purpose of quick demonstration, we will create clients from the command line for our local testing, as follows:  

&ndash; ```php artisan passport:client```  

Supposing we create client for the user aiden15@example.net (id = 1), then we will give the following inputs:  
```bash
Which user ID should the client be assigned to?:
 > 1

 What should we name the client?:
 > Authorization Code Grant Type Client

 Where should we redirect the request after authorization? [http://testproject.acme.com/auth/callback]:
 > http://thirdparty.acme.com/callback

New client created successfully.
Client ID: 4
Client secret: 1JsWFAAOMg36yaPBwaYU15XuZbPrIgKaz4u5cNrA

```  

Where aiden15@example.net (id = 1) is the account that the third party's developer/owner created at your application (as step 1 above)  

&ndash; On browser, visit the link http://testproject.acme.com/oauth/authorize?client_id=4&redirect_uri=http://thirdparty.acme.com/callback&response_type=code (this simulates the action when a user is being redirected to your server ```/oauth/authorize``` by the third party application)  

&ndash; If the user does not have an authenticated session with your server, then a login page will be displayed for him (supposing that the user enter ```gswaniawski@example.net / password``` to login to your application)  

&ndash; If the user have an authentication session with your server, then a view will be displayed for him to approve/deny the authorization request  

&ndash; After approving the request, the user who has just logged in to your application with ```gswaniawski@example.net / password``` will be redirected back to the third party application at the following URL:  

http://thirdparty.acme.com/callback?code=def50200b978d55c2d335b85aa4ee6b2930ef63f1e79d55c684c6cc85f4ecce793524ffbef863a9dd15318fe833d8f9a0b62b22bb3d78aa5a78c28e1090d6067e913b2c3299c9e49dcdfd99b0499767e827e1a56b8a5cd8a1cdbe6c13e5b00febe6ce3f2b0bdf74b5704c9be6c5a677fd80cf1bdcf481f9e1c8c1960cbb96580ca60c79082337f78e17787671d03b5e6fa7ea665689f9da41002122e48ca0b78e46716ce272626a3c2eff8d0116b7bdaa4f34905641ffc4ba5688aaf5fef133f544d7bc078c85a49bd303df629cf606c619641c9d8d030321c20d0b70871449c19d9ebee9a56b9a087b94cfdacf7b1cbe45c231229772a43bb41d2edc4166aebf815770012d0c67dee55b853d92e68e7b4c16b27ff7269d539f4f71dbf77904c816f624755dc0e8b12643cb4df38d37acde1ec74b11ca05a2aeed60be79a5f62e924839f0013b3d1720346321c064450bab138b056c2c45ccb0029e7b9fc41f9886c27a120 

As you can see, your application provides an authorization code back to the third party application at their supplied ```redirect_uri``` http://thirdparty.acme.com/callback  

&ndash; Now, the third party application can use the provided authorization code to exchange an access token which represents the user gswaniawski@example.net (id = 2) of your application, as follows:  
```bash
curl -X POST /oauth/token \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  -d 'client_id=4&client_secret=1JsWFAAOMg36yaPBwaYU15XuZbPrIgKaz4u5cNrA&grant_type=authorization_code&redirect_uri=http%3A%2F%2Fthirdparty.acme.com%2Fcallback&code=def50200b978d55c2d335b85aa4ee6b2930ef63f1e79d55c684c6cc85f4ecce793524ffbef863a9dd15318fe833d8f9a0b62b22bb3d78aa5a78c28e1090d6067e913b2c3299c9e49dcdfd99b0499767e827e1a56b8a5cd8a1cdbe6c13e5b00febe6ce3f2b0bdf74b5704c9be6c5a677fd80cf1bdcf481f9e1c8c1960cbb96580ca60c79082337f78e17787671d03b5e6fa7ea665689f9da41002122e48ca0b78e46716ce272626a3c2eff8d0116b7bdaa4f34905641ffc4ba5688aaf5fef133f544d7bc078c85a49bd303df629cf606c619641c9d8d030321c20d0b70871449c19d9ebee9a56b9a087b94cfdacf7b1cbe45c231229772a43bb41d2edc4166aebf815770012d0c67dee55b853d92e68e7b4c16b27ff7269d539f4f71dbf77904c816f624755dc0e8b12643cb4df38d37acde1ec74b11ca05a2aeed60be79a5f62e924839f0013b3d1720346321c064450bab138b056c2c45ccb0029e7b9fc41f9886c27a120'


# Received response
{
    "token_type": "Bearer",
    "expires_in": 1296000,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjlkMWJhZjhjZTE5ZTViZWNlY2RiYzRmMGI3NjNhZjU2NmE2ZDYwYjlmNWI3ODcwOTdlYWJkNTUxYzhmM2NmNWMwN2VkMDE0NDZhODNhZjNkIn0.eyJhdWQiOiI0IiwianRpIjoiOWQxYmFmOGNlMTllNWJlY2VjZGJjNGYwYjc2M2FmNTY2YTZkNjBiOWY1Yjc4NzA5N2VhYmQ1NTFjOGYzY2Y1YzA3ZWQwMTQ0NmE4M2FmM2QiLCJpYXQiOjE1NjU3NDY0MDcsIm5iZiI6MTU2NTc0NjQwNywiZXhwIjoxNTY3MDQyNDA3LCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.KeoWqAnopDObPtCkFDU5ofVWEmnRk0nRh6eGXl1M4xNn8wVA12xBd0fDMmnnqrXLxa2fAIEn0BINxWNUCV6t4pTq23B4X0jADvkRXIXAUDZp95dfKsJgnho2zNSDnK4pVfOPSe-LtC4N5Dcr9F2yjA7xsF1UAryC_B6nFYSbhZ61scCsQaKiJ5KKleJavCWMS4cbUfSS_ZCKXslasGWsPrjdr3mChIi9JZcJOyH_dT5cj9flajYiEMxMpPM4sJaH0wWWt6McIPggVagm4qtSs5W8Fww0jPkf2bZemtRp1rFdzTEqngc8LW5xIHMP9VJtWh5MvXoLPMwl2hDIZDbkWDctwyV8VrYmxbQHdghRhAEg3y0VMiGoKWzZ3d8M7KbZEg32jfnt2MY0A64bH0vZWgCZYTJiHPRbLdldIAG54r5n1j0Nq91Z_xqKG7eYzpdiooL96yZQO7jyym9ozLFqyuL0SPXOWu2ozCsUwnf_vdhQBXFEw8GB3VQc6I5rv5Q8DSorjX1NHuG2R04NIZtd3RhzxWnwyjPReEbfFmegvuXtzFeRb93lg_yTUVh1tm13_6awwHtEqWZe404j7JnqyphN42UfSc649Kc9ZLnzUAKQI3PHUjaPFu0GgG8s3Gamad60sj5qPNrGd5D9ZdnqoG-Yoe3LXk1MB_RnASjYVgE",
    "refresh_token": "def502005a0d09f8789ac22e48438f8d0f3ee4c8bc8dde941606809fca292918f68770bca3463ed31fe55e21705de1f13c4033dbd9f7015264d3c0cdefed2aa6a2496bc62041c29b0fa8ef6737773d647a6ca318a117fd4eabd9ba0a896f1e61760f061cebaec25e078a0d8cc0723f7b91de16db9598790720d47bddcafdab415a9b6dc568e3ebe283ca80c1f15cead9a6c180c563ad81b9960f4a9e3ad97dd8c1b383d39aab9479bc496a6ff04a65ad72d291ba5ea1d74ea330b2c6819da91d1d2385d56f3dceca8890773b20a77841d3b7e70b32e3c259437a5d49c568b4a0f02e2bc400d2085487cf8ba77409d0b546e27ffdb75e7449047f4282da242b1023c5b40815155318ca51fdce44014762dee8efbc15d2525250a00c18bb870a4a73997db5258a7a747ca1304c1ba12398861524f638835422027e36c8efacd20d801274872bbf207249aea48a4498c065d48a8f0ab485bcc33e1e9ffd01f2985dd2"
}
```  

At this point, we can say that a user of the third party application, who also has an account at your application with the email of gswaniawski@example.net (id = 2), has given an access token to a client created by the third party's developer/owner at your application. The third party's developer/owner also has an account at your application with email aiden15@example.net (id = 1). Using the access token, the client can act on behalf of the user gswaniawski@example.net within your application without knowing his password.  
