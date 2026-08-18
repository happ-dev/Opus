# Configuration

> [!IMPORTANT]
> In the future all configuration files will become true as soon as you write Hello World.

## public/index.php

```php
chdir(dirname(__DIR__));
$autoload = 'vendor/autoload.php';

try {
	// test if autoload file exist
	file_exists($autoload) ?: throw new Exception($autoload . ' file could not be found.');

	// autoload composer
	require_once $autoload;

	// load configuration
	Opus\config\Config::loadConfiguration();

	// session start
	Opus\controller\Controller::session();

	// start application
	Opus\controller\Controller::run();
} catch (Exception $ex) {
	echo $ex->getMessage() . PHP_EOL;
}
```

## config/global.json

```json
{
	"apps": [
		"hello", "demo"
	],

	"storage": [
		{
			"happ": {
				"type": "pgsql",
				"name": "happ",
				"encoding": "UTF8"
			}
		}
	],

	"navbar": {
		"brand_icon": "img/app-indicator-icon-happ.svg",
		"brand_text": "Opus",
		"login_form": "yes|true|no|false"
	},

	"languages": [
		"pl|en"
	],

	"role": "prod|dev",

	"icon": "img/app-indicator-icon-happ.svg",

	"title": "hApp",

	"vendor": [
		"bootstrap/bootstrap.min.css",
		"bootstrap/bootstrap-happ.css",
		"bootstrap/bootstrap-icons.min.css",
		"datatables/datatables.min.css",
		"jquery/jquery.min.js",
		"jquery/jquery.mask.min.js",
		"popperjs/popper.min.js",
		"bootstrap/bootstrap.min.js",
		"datatables/datatables.min.js",
		"moment/moment.min.js",
		"moment/locale/pl.js",
		"tempusdominus/tempus-dominus.min.js"
	],

	"email": "admin@opus.dev",

	"trusted_hosts": ["localhost"]
}
```

## config/local.json

> First time you run it, your login and password will be encrypted and a _secret.key_ file will be created

```json
{
	"storage": [
		{
			"happ": {
				"user": "happ_user",
				"pass": "happ_password",
				"host": "localhost",
				"port": "5432"
			}
		}
	]
}
```

## apps/{{app_name}}/config/{{app_name}}.config.json

```json
{
	"app": {
		"type": "page",
		"class": "apps\\hello\\src\\HelloController",
		"access": 3,
		"version": "0.2-alpha.1",
		"description": "hello world app"
	},

	"route": ["hello"],

	"nav": {
		"type": "menu",
		"disabled": "false",
		"id": "001_nav",
		"name": "Hello-World",
		"icon": "bi-bootstrap"
	},

	"view": {
		"index": "apps/hello/view/hello.phtml"
	},

	"js": {
		"index": "apps/hello/js/hello.js"
	},

	"vendor": [
		"highlight/hljs.opus.css",
		"highlight/highlight.min.js"
	],

	"idTableEvent": "id__hello-event-dt",

	"injectEvent": {
		"vthead": {
			"file": "apps/hello/view/inject/vthead.phtml"
		}
	},

	"asyncPage": {
		"hello": {
			"type": "apage",
			"access": 3,
			"view": "apps/hello/view/world/WorldPage.php",
			"class": "apps\\hello\\src\\world\\World"
		}
	},

	"tableEvent": {
		"hello": {
			"primaryKey": "id__hello",
			"table": "public.hello",
			"columns": [
				{ "db": "id__hello" },
				{ "db": "hello" },
				{ "db": "select_db" },
				{ "db": "select_text_value" },
				{ "db": "disabled" },
				{ "db": "button" }
			],
			"join": false || "LEFT JOIN public.world ON (world.id_to_hello = hello.id__hello)",
			"select": {
				"select_db": "SELECT id__value, text FROM public.hello",
				"select_text_value": {
					"value": ["1", "3"],
					"text": ["Hello", "World"]
				}
			},
			"disabled": {
				"disabled": "true"
			},
			"buttons": {
				"button": {
					"text": "demo.bonus" || "Button",
					"icon": "bi-person-x",
					"attributes": {
						"data-bs-toggle": "modal",
						"data-bs-target": "#id__hello-dt"
					}
				}
			},
			"access": {
				"show": 0,
				"add": 3,
				"edit": 6,
				"delete": 9
			}
		}
	},

	"asyncEvent": {
		"world": {
			"type": "api",
			"access": 2,
			"file": "apps/hello/src/world/WorldApi.php",
			"class": "apps\\hello\\src\\world\\WorldApi"
		}
	}
}
```
