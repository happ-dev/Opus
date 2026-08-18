# Changelog — Upgrade to version 1.0

## Completed

- [x] Adjust directory/namespace to current changes
- [x] Required _public_ directory structure
- [x] CSS
	- [x] _opus.css_ file copied to _public/vendor/opus_
	- [x] Design a light/dark view
- [x] Config class
	- [x] Adapting the class to the new version of the configuration file
	- [x] `"role": "prod|dev"` default is prod
	- [x] `"vendor:" ...` file path validation
- [x] Opus\view\login\Login class
	- [x] Rewriting Login class using StandardFormElements
- [x] Lang class
	- [x] Handle messages depending on the selected language
- [x] Query class
	- [x] SELECT
- [x] Subpages loaded asynchronously
	- [x] AsyncPage class
	- [x] Asynchronous functions for loading subpage content in _global.js_
	- [x] Renamed _sApp_ to _asyncPage_ in config
	- [x] Event class handles asyncpage loading
	- [x] asyncAction(): mixed in InterfaceController
	- [x] Request class, TYPE_ASYNC_PAGE = 'apage'
- [x] New internal app: _demo_
	- [x] Sidebar, Modal, Offcanvas, Collapse, Buttons
	- [x] SQL for table event
	- [x] Table with asyncTable event
	- [x] OpusSelect — replacing Select2
	- [x] OpusDatePicker — replacing TempusDominus

## JS (partially complete)

- [x] Generating _opus.js_ from _Opus/js_ files
- [x] _opus.js_ copied to _public/vendor/opus_
- [x] _{{app_name}}.lib.js_ compiled and copied
- [x] JS files from config added at end of `<body>`
- [x] Adjust scanFiles function
- [x] OpusSelect (ajax, search, selectEvent)
- [x] OpusDatePicker
- [x] Light/dark view toggle from navbar
