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

```/config/cors.php``` is the config file of ***```barryvdh/laravel-cors```*** package, you should consult to its documentation for further details.  

```/config/bkstar123_apibuddy.php``` is the package main config file, it contains the following options:  
- **```'max_per_page'```**:  The maximum page size that a request can specify, by default it is 1000 items/page
- **```default_per_page```**: The default page size that will be applied if a request does not specify, by default it is 10 items/page
- **```replace_exceptionhandler```**: You can choose to replace the Laravel default exception handler with the one provided by the package or not. It is recommended to set to ```true``` (its default) so that all exceptions can be converted to appropriate JSON responses
- **```useTransform```**: Whether or not to use transformation. It is recommmended to set to ```true``` (its default) for the best security protection. Since the underlying PDO DB driver does not support binding column names, see https://laravel.com/docs/5.8/queries; the transformation should always be used whenever you allow user input to dictate the column names referenced by your queries.  
