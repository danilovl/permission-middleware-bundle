[![phpunit](https://github.com/danilovl/permission-middleware-bundle/actions/workflows/phpunit.yml/badge.svg)](https://github.com/danilovl/permission-middleware-bundle/actions/workflows/phpunit.yml)
[![downloads](https://img.shields.io/packagist/dt/danilovl/permission-middleware-bundle)](https://packagist.org/packages/danilovl/permission-middleware-bundle)
[![latest Stable Version](https://img.shields.io/packagist/v/danilovl/permission-middleware-bundle)](https://packagist.org/packages/danilovl/permission-middleware-bundle)
[![license](https://img.shields.io/packagist/l/danilovl/permission-middleware-bundle)](https://packagist.org/packages/danilovl/permission-middleware-bundle)

# PermissionMiddlewareBundle #

## About ##

Symfony bundle provides simple mechanism control permission for class or his method.

### Requirements 

  * PHP 8.0.0 or higher
  * Symfony 5.0 or higher

### 1. Installation

Install `danilovl/permission-middleware-bundle` package by Composer:
 
``` bash
$ composer require danilovl/permission-middleware-bundle
```
Add the `PermissionMiddlewareBundle` to your application's bundles if does not add automatically:

```php
<?php
// config/bundles.php

return [
    // ...
    Danilovl\PermissionMiddlewareBundle\PermissionMiddlewareBundle::class => ['all' => true]
];
```

### 2. Usage

Configuration tree options for attribute.

The `accessDeniedHttpException` parameter will be useful for `ClassMiddleware`, `ServiceMiddleware` when you create custom response and you won't want throw default AccessDeniedHttpException.

```php
$configurationTree = [
    'user' => [
        'roles',
        'userNames',
        'accessDeniedHttpException',
        'exceptionMessage' => [
            'message',
            'messageParameters',
            'domain',
            'locale'
        ],
        'redirect' => [
            'route',
            'parameters',
            'flash' => [
                'type',
                'trans' => [
                    'message',
                    'messageParameters',
                    'domain',
                    'locale'
                ]
            ]
        ]
    ],
    'date' => [
        'from',
        'to',
        'accessDeniedHttpException',
        'exceptionMessage' => [
            'message',
            'messageParameters',
            'domain',
            'locale'
        ],
        'redirect' => [
            'route',
            'parameters',
            'flash' => [
                'type',
                'trans' => [
                    'message',
                    'messageParameters',
                    'domain',
                    'locale'
                ]
            ]
        ]
    ],
    'redirect' => [
        'route',
        'parameters',
        'accessDeniedHttpException',
        'flash' => [
            'type',
            'trans' => [
                'message',
                'messageParameters',
                'domain',
                'locale'
            ]
        ]
    ],
    'class' => [
        'name',
        'method',
        'accessDeniedHttpException',
        'exceptionMessage' => [
            'message',
            'messageParameters',
            'domain',
            'locale'
        ],
        'redirect' => [
            'route',
            'parameters',
            'flash' => [
                'type',
                'trans' => [
                    'message',
                    'messageParameters',
                    'domain',
                    'locale'
                ]
            ]
        ]
    ],   
    'service' => [
        'name',
        'method',
        'accessDeniedHttpException',
        'exceptionMessage' => [
            'message',
            'messageParameters',
            'domain',
            'locale'
        ],
        'redirect' => [
            'route',
            'parameters',
            'flash' => [
                'type',
                'trans' => [
                    'message',
                    'messageParameters',
                    'domain',
                    'locale'
                ]
            ]
        ]
    ],
];
```

You can use `PermissionMiddleware` attribute for class or method.
Method of `ClassMiddleware`, `ServiceMiddleware` accept `Symfony\Component\HttpKernel\Event\ControllerEvent` as argument and must return boolean.

```php
<?php declare(strict_types=1);

namespace App\Controller;

use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

#[PermissionMiddleware([
    'user' => [
        'roles' => ['ROLE_SUPERVISOR'],
        'userNames' => ['admin'],
        'exceptionMessage' => [
            'message' => 'app.permission_method_user_error_message'    
        ],
        'redirect' => [
            'route' => 'profile_show',
            'flash' => [
                'type' => 'error',
                'trans' => [
                    'message' => 'app.permission_action_flash_message'
                ]
            ]
        ]
    ],
    'date' => [
        'from' => '01-01-2021',
        'exceptionMessage' => [
            'message' => 'app.permission_date_error_message'
        ],
        'redirect' => [
            'route' => 'profile_show',
            'flash' => [
                'type' => 'error',
                'trans' => [
                    'message' => 'app.permission_action_flash_message'
                ]
            ]
        ]
    ],
    'redirect' => [
        'route' => 'profile_show',
        'flash' => [
            'type' => 'warning',
            'trans' => [
                'message' => 'app.permission_action_flash_message'
            ]
        ]
    ],
   'class' => [
        'name' => 'App\Middleware\HomeControllerMiddleware',
        'method' => 'handle',
        'exceptionMessage' => [
            'message' => 'app.permission_date_error_message'
        ],
        'redirect' => [
            'route' => 'profile_show',
            'flash' => [
                'type' => 'error',
                'trans' => [
                    'message' => 'app.permission_action_flash_message'
                ]
            ]
        ]
    ],   
    'service' => [
        'name' => 'app.middleware.home_controller',
        'method' => 'handle',
    ]
])]
class HomeController extends AbstractController
{
    #[PermissionMiddleware([
        'user' => [
            'roles' => ['ROLE_SUPERVISOR'],
            'userNames' => ['admin'],
            'exceptionMessage' => [
                'message' => 'app.permission_method_user_error_message'    
            ],
            'redirect' => [
                'route' => 'profile_show',
                'flash' => [
                    'type' => 'error',
                    'trans' => [
                        'message' => 'app.permission_action_flash_message'
                    ]
                ]
            ]
        ],
        'date' => [
            'from' => '01-01-2021',
            'exceptionMessage' => [
                'message' => 'app.permission_date_error_message'
            ],
            'redirect' => [
                'route' => 'profile_show',
                'flash' => [
                    'type' => 'error',
                    'trans' => [
                        'message' => 'app.permission_action_flash_message'
                    ]
                ]
            ]
        ],
        'redirect' => [
            'route' => 'profile_show',
            'flash' => [
                'type' => 'warning',
                'trans' => [
                    'message' => 'app.permission_action_flash_message'
                ]
            ]
        ]
    ])]
    public function index(Request $request): Response
     {
         return $this->render('home/index.html.twig');
     } 
     
    #[PermissionMiddleware([
        'date' => [
            'to' => '31-12-2020',
            'redirect' => [
                'route' => 'new_news',
                'flash' => [
                    'type' => 'warning',
                    'trans' => [
                        'message' => 'app.old_section_is_closed'
                    ]
                ]
            ]
        ]
    ])]
    public function oldNews(Request $request): Response
    {
        return $this->render('home/news.html.twig');
    }
 
    #[PermissionMiddleware([
        'date' => [
            'from' => '01-01-2021',
            'redirect' => [
                'route' => 'new_news',
                'flash' => [
                    'type' => 'warning',
                    'trans' => [
                        'message' => 'app.new_section_is_open',
                        'parameters' => ['date' => '01-01-2021'],
                        'domain' => 'flashes',
                        'locale' => 'en'
                    ]
                ]
            ]
        ]
    ])]
    public function news(Request $request): Response
    {
        return $this->render('home/news.html.twig');
    }

     #[PermissionMiddleware([
        'user' => [
            'roles' => ['ROLE_SUPERVISOR'],
            'userNames' => ['admin'],
            'redirect' => [
                'route' => 'homepage',
                'flash' => [
                    'type' => 'error',
                    'trans' => [
                        'message' => 'app.permission_denied'
                    ]
                ]
            ]
        ]
    ])]
    public function editNews(Request $request): Response
    {
        return $this->render('home/edit_news.html.twig');
    }

   #[PermissionMiddleware([
        'redirect' => [
            'route' => 'homepage'
        ]
   ])]
   public function redirect(Request $request): Response
   {
       return $this->render('home/redirect.html.twig');
   }

   #[PermissionMiddleware([
        'redirect' => [
            'route' => 'homepage',
            'flash' => [
                'type' => 'success',
                'trans' => [
                    'message' => 'app.redirect_success'
                ]
            ]
        ]
   ])]
   public function redirectWithFlash(Request $request): Response
   {
       return $this->render('home/redirect.html.twig');
   }

   #[PermissionMiddleware([
        'user' => [
            'roles' => ['ROLE_ADMIN'],
            'userNames' => ['admin'],
            'redirect' => [
                'route' => 'login'
            ]
        ]
   ])]
   public function admin(Request $request): Response
   {
       return $this->render('home/admin.html.twig');
   }

   #[PermissionMiddleware([
        'user' => [
            'userNames' => ['admin', 'editor', 'publisher'],
            'redirect' => [
                'route' => 'login'
            ]
        ]
   ])]
   public function adminByUsernameRedirect(Request $request): Response
   {
       return $this->render('home/admin.html.twig');
   }
   
   #[PermissionMiddleware([
        'user' => [
            'userNames' => ['admin', 'editor', 'publisher'],
            'exceptionMessage' => [
                'message' => 'app.permission_denied'    
            ]
        ]
   ])] 
   public function adminByUsernameExceptionMessage(Request $request): Response
   {
       return $this->render('home/admin.html.twig');
   }   
   
   #[PermissionMiddleware([
        'class' => [
            'name' => 'App\Middleware\ShowCalendarMiddleware',
            'method' => 'handle'
        ]
   ])] 
   public function showCalendar(Request $request): Response
   {
       return $this->render('home/admin.html.twig');
   }   
   
   #[PermissionMiddleware([
        'service' => [
            'name' => 'app.middleware.create_article',
            'method' => 'handle'
        ]
   ])] 
   public function createArticle(Request $request): Response
   {
       return $this->render('home/admin.html.twig');
   }
}
```

## License

The PermissionMiddlewareBundle is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).