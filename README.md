# Opus Framework

Opus is a framework used for educational purposes only, basically for me.
Please do not use it for any projects because it is very likely to be full of holes like Swiss cheese.

## Planned upgrade to version 1.0
> [!WARNING]
> I'm not planning any more improvements, only bugs will be fixed.

- [ ] adjust directory/namespace to current changes:
> [!IMPORTANT]
> __{{app_name}}__.* means that this file is required
```
- Opus/
	- apps/
		- __{{app_name}}__/
			- config/			> __{{app_name}}__.config.json
			- js/
			- libs/
			- sql/
			- src/				> __{{App_name}}__Controller.php
			- view/				> __{{app_name}}__Controller.phtml
		- skeleton/
		- settings/
		- profile/
		- logs/
		- demo/
	- config/
	- controller/
		- auth/
		- cli/
		- event/
		- exception/
		- login/
		- query/
		- request/
	- html/
		- form/					> moved from controller/
		- table/				> moved from controller/
		- buttons/				> moved from view/
		- collapse/				> moved from view/
		- modal/				> moved from view/
		- offcanvas/			> moved from view/
		- sidebar/				> moved from view/
	- js/
	- libs/						> before controller/common/
	- lang/
	- sql/
	- storage/
		- curl/
		- db/
		- exception/
		- json/
	- view/
		- script/
		- layout/
		- navbar/
		- login/
			- Login.php			> moved from view/navbar/ and renamed from LoginForm.php 
		- view/
	- css/						> opus.css
- .gitignore
- README.md
```
- [ ] CSS
	- [ ] _opus.css_ file copied to _public/css_:
		> each time if _role_ parameter is _dev_
		> if **"role"** parameter is _prod_ it will only check if the file exists, if not, it will be copied
	- [ ] _css_ files provided in _config/global.json_ copied to _public/css_:
		> regardless of _role_ parameter, it will only check if the file exists in _public/css_ if it is not copied
	- [ ] added files to **<head>** in _layout.phtml_
	- [ ] moving **"shortcut icon"** and **<title>** from _layout.phtml_ to _config/global.js_

- [ ] JS
	- [ ] generating _opus.js_ file from _*.js_ files located in _Opus/js_
	- [ ] _opus.js_ file copied to _public/js_
	- [ ] added file _opus.js_ to **<head>** in _layout.phtml_
	- [ ] files from _/apps/libs/*.js_ directory will be compiled into one __{{app_name}}.lib.js__ file
	- [ ] __{{app_name}}.lib.js__ file copied to _public/js_
	- [ ] added file __{{app_name}}.lib.js__ to **<head>** in _layout.phtml_
	- [ ] files from _/apps/js/*.js_ directory will be compiled into one __{{app_name}}.js__ file
		> does not apply to _js_ files intended for subpages,
		> whether the file belongs to a subpage will be verified based on **"sApp"**
		> located in __apps/app_name/config/**{{app_name}}**.config.json__
	- [ ] __{{app_name}}.js__ file copied to _public/js_
	- [ ] adding a file __{{app_name}}.js__ to the end of **<body>**
	- [ ] adjust **function scanFiles(array &$indexes, string $app, string $scanDir, string $fileType): void**
		> searching for files according to their intended purpose [TYPE_PAGE|TYPE_SUBPAGE]
	- [ ] write my own select menu with search engine

- [ ] write a Lang class to handle messages depending on the selected language
	> proposed function `langEcho(?string $path): string {}`
	> ?	string $path: path to the message in the file __{{lang}}.opus.json__ or __{{lang}}.app.json__

- [ ] subpages loaded asynchronously
	- [ ] asynchronous functions for loading subpage content in _global.js_
	- [ ] adjust *Request* class to detect subpage request
	- [ ] *Controller* class initiates a new Event `TYPE_SUBPAGE`
	- [ ] implementation of a new request __TYPE_SUBPAGE__ type in the *Event* class
	- [ ] new *SubpageView* view class
	- [ ] new *SubpageScript* class
		> new Event `TYPE_SUBPAGE` initiated by __Controller__
		> returns a complete new view consisting of HTML and JS, similar to the *View* class

- [ ] new internal app: _demo_
	> App is intended to demonstrate all the possibilities offered by the Opus Framework

- [ ] known issues
	- [ ] icon in the header disappears when you click on the modal again
	- [ ] Form::addElement, if there is no data in text, value add the message no data

## Hello World application
[!IMPORTANT]
In the future all configuration files will become true as soon as you write Hello World.

Create file _public/index.php_.

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

	// start application
	Opus\controller\Controller::run();
} catch (Exception $ex) {
	echo $ex->getMessage() . PHP_EOL;
}
```

Create file _config/global.json_.

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
		"login_form": "yes|true|no|false"
	},

	"languages": [
		"pl|en"
	],

	"role": "prod|dev",

	"icon": "img/app-indicator-icon-happ.svg",
	"title": "hApp",

	"vendor": {
		"js": [
			"js/jquery/jquery.min.js",
			"js/jquery/jquery.mask.min.js"
		],
		"css": [
			"css/bootstrap/bootstrap.min.css",
			"css/bootstrap/bootstrap-icons.min.css"
		]
	},

	"email": "admin@opus.dev",

	"trusted_hosts": ["localhost"]
}
```

Create file _config/local.json_.
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

Create file _apps/app_name/config/app_name.config.json_.

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

	"idTableEvent": "id__hello-event-dt",

	"injectEvent": {
		"vthead": {
			"file": "apps/hello/view/inject/vthead.phtml"
		}
	},

	"sApp": {
		"hello": {
			"type": "spage",
			"route": ["world"],
			"access": 3,
			"view": "apps/hello/view/world/world.phtml",
			"js": "apps/hello/js/world/world.js"
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
					"type": "button",
					"text": "<i class=\"me-1 bi bi-person-x\"></i><em>Button</em>",
					"attributes": {
						"type": "button",
						"class": "mr-sm-1 btn btn-warning btn-sm",
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