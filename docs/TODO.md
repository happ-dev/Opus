# TODO

## JS
- [ ] Chart library (chart.js + ewentualnie API do pobierania danych)

## Layout class
- [x] ~~Rewrite offcanvas html in layout.phtml according to Opus\html\offcanvas\Offcanvas class~~ (not applicable — navbar offcanvas has different structure)

## View class
- [x] Finish while writing the __Demo__ application

## Internal apps
- [x] Settings - user panel settings with the ability to change for root
- [x] Logs - search engine for all saved events
- [x] Profile - a place where the user can change their data and reset their password

## Known issues
- [x] ~~Form::addElement, if there is no data in text, value add the message no data~~ (exception is correct behavior — empty select indicates a bug)
- [x] Navbar incorrectly displays icons in resolutions other than the default browser resolution
- [x] Offcanvas-header - after changing the theme to dark, the text color remains black
- [ ] Opening the second page in the browser changes csrf token, which causes an error in the first page opened
