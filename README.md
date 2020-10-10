# PermissionMiddlewareBundle #

## About ##

Symfony bundle provides simple mechanism control permission for class or his method.

### Requirements 

  * PHP 7.4.0 or higher
  * Symfony 5.0 or higher
  * Doctrine annotations 1.0 or higher

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

Configuration annotation tree options.

```php
$configurationTree = [
    'user' => [
        'roles',
        'userNames',
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
];
```

You can use `PermissionMiddleware` annotation for class or method.

```php
<?php declare(strict_types=1);

namespace App\Controller;

use Danilovl\PermissionMiddlewareBundle\Annotation\PermissionMiddleware;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

/**
* @PermissionMiddleware(
*     user={
*          "roles": {"ROLE_SUPERVISOR"},
*          "userNames": {"admin"},
*          "exceptionMessage": {
*              "message": "app.permission_method_user_error_message"
*          },
*         "redirect": {
*              "route": "profile_show",
*              "flash": {
*                   "type": "error",
*                   "trans": {
*                       "message": "app.permission_action_flash_message"
*                   }
*              }
*         },
*     },
*     date={
*          "from": "01-01-2021",
*           "exceptionMessage": {
*              "message": "app.permission_date_error_message"
*          },
*         "redirect": {
*              "route": "profile_show",
*              "flash": {
*                   "type": "error",
*                   "trans": {
*                       "message": "app.permission_action_flash_message"
*                   }
*              }
*         },
*     },
*     redirect={
*          "route": "profile_show",
*              "flash": {
*                   "type": "warning",
*                   "trans": {
*                       "message": "app.permission_action_flash_message"
*                   }
*              }
*     }
* )
*/
class HomeController extends AbstractController
{
    /**
     * @PermissionMiddleware(
     *     user={
     *          "roles": {"ROLE_SUPERVISOR"},
     *          "userNames": {"admin"},
     *          "exceptionMessage": {
     *              "message": "app.permission_method_user_error_message"
     *          },
     *         "redirect": {
     *              "route": "profile_show",
     *              "flash": {
     *                   "type": "error",
     *                   "trans": {
     *                       "message": "app.permission_action_flash_message"
     *                   }
     *              }
     *         },
     *     },
     *     date={
     *          "from": "01-01-2021",
     *           "exceptionMessage": {
     *              "message": "app.permission_date_error_message"
     *          },
     *         "redirect": {
     *              "route": "profile_show",
     *              "flash": {
     *                   "type": "error",
     *                   "trans": {
     *                       "message": "app.permission_action_flash_message"
     *                   }
     *              }
     *         },
     *     },
     *     redirect={
     *          "route": "profile_show",
     *              "flash": {
     *                   "type": "warning",
     *                   "trans": {
     *                       "message": "app.permission_action_flash_message"
     *                   }
     *              }
     *     }
     * )
     */
    public function index(Request $request): Response
     {
         return $this->render('home/index.html.twig');
     } 

    /**
     * @PermissionMiddleware(
     *      date={
     *          "to": "31-12-2020",
     *          "redirect": {
     *              "route": "new_news",
     *              "flash": {
     *                  "type": "warning",
     *                  "trans": {
     *                      "message": "app.old_section_is_closed"
     *                  }
     *              }
     *          }
     *      }
     * )
     */
    public function oldNews(Request $request): Response
    {
        return $this->render('home/news.html.twig');
    }

    /**
     * @PermissionMiddleware(
     *      date={
     *          "from": "01-01-2021",
     *          "redirect": {
     *              "route": "new_news",
     *              "flash": {
     *                  "type": "warning",
     *                  "trans": {
     *                      "message": "app.new_section_is_open"
     *                      "parameters": {"date": "01-01-2021"},
     *                      "domain": "flashes",
     *                      "locale": "en"
     *                  }
     *              }
     *          }
     *      }
     * )
     */
    public function news(Request $request): Response
    {
        return $this->render('home/news.html.twig');
    }

    /**
     * @PermissionMiddleware(
     *      user={
     *          "roles": {"ROLE_EDITOR"},
     *          "userNames": {"admin"},
     *          "redirect": {
     *             "route": "homepage",
     *             "flash": {
     *                 "type": "error",
     *                 "trans": {
     *                     "message": "app.permission_denied"
     *                 }
     *             }
     *         }
     *     }
     * )
     */
    public function editNews(Request $request): Response
    {
        return $this->render('home/edit_news.html.twig');
    }

   /**
    * @PermissionMiddleware(
    *      redirect={
    *          "route": "homepage"
    *     }
    * )
    */
   public function redirect(Request $request): Response
   {
       return $this->render('home/redirect.html.twig');
   }

   /**
    * @PermissionMiddleware(
    *      redirect={
    *          "route": "homepage",
    *           "flash": {
    *               "type": "success",
    *               "trans": {
    *                   "message": "app.redirect_success"
    *               }
    *           }
    *     }
    * )
    */
   public function redirectWithFlash(Request $request): Response
   {
       return $this->render('home/redirect.html.twig');
   }

   /**
    * @PermissionMiddleware(
    *      user={
    *          "roles": {"ROLE_ADMIN"},
    *          "userNames": {"admin"},
    *          "redirect": {
    *             "route": "login"
    *         }
    *     }
    * )
    */
   public function admin(Request $request): Response
   {
       return $this->render('home/admin.html.twig');
   }

   /**
    * @PermissionMiddleware(
    *      user={
    *          "userNames": {"admin", "editor", "publisher"},
    *          "redirect": {
    *             "route": "login"
    *         }
    *     }
    * )
    */
   public function adminByUsernameRedirect(Request $request): Response
   {
       return $this->render('home/admin.html.twig');
   }

   /**
    * @PermissionMiddleware(
    *      user={
    *          "userNames": {"admin", "editor", "publisher"},
    *          "exceptionMessage": {
    *              "message": "app.permission_denied"
    *          },
    *     }
    * )
    */
   public function adminByUsernameExceptionMessage(Request $request): Response
   {
       return $this->render('home/admin.html.twig');
   }
}
```
