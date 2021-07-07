# Security

This is not meant to be a comprehensive cybersecurity overview, but does include an overview of PitonCMS options and features to consider when building a client website.

## Security Headers
PitonCMS comes with a default set of security response headers which are defined in `vendor/pitoncms/engine/config/config.default.php`. You can overwrite, change, or disable any header in `config/config.local.php`. These headers will be added to all HTTP responses for both backend PitonCMS administration pages and front end visitor pages.

The default response headers are:

```php
/**
 * Response Headers
 *
 * Sets response headers dynamically at application runtime
 * Define any standard or custom headers as a key:value, in the $config['header'] array in config.local.php
 */
$config['header']['X-Content-Type-Options'] = 'nosniff';
$config['header']['Referrer-Policy'] = 'no-referrer-when-downgrade';
$config['header']['X-XSS-Protection'] = '1; mode=block';
$config['header']['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
$config['header']['Content-Security-Policy'] = "default-src 'self'; script-src 'self' 'nonce' 'strict-dynamic' 'unsafe-inline'; connect-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self'; base-uri 'none'; frame-ancestors 'none'";
```
Note: `style-src https://fonts.googleapis.com` and `font-src https://fonts.gstatic.com` are needed for PitonCMS backend administration pages.

For more on these headers and others see [MDN HTTP Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers).

You can add other standard and custom headers by adding the header name and value to the `$config['header']` array in `config/config.local.php`:

```php
$config['header']['Breakfast'] = 'pancakes';
```

Note that the `Strict-Transport-Security` header is treated slightly differently.

### Strict Transport Security
This header tells the browser that all future requests to this site must be sent as HTTPS. You can specify how far in the future to honor this request by setting the `max-age` (in seconds), and with each page view this header pushes that date out further. `includeSubDomains` also forces HTTPS on subdomains.

This header is only set when _not_ on localhost. If your website is not on HTTPS you might want to disable this header in `config/config.local.php` by setting it to `false` to avoid headaches.

### Content Security Policy
A Content Security Policy (CSP) is a very powerful way to mitigate risk to your website from XSS attacks and other forms of malicious code injection. It instructs the browser how to control what resources the user agent is allowed to load for a given page. An effective CSP blocks everything from running on your website, except for what you allow. See [MDN Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy) for more information.

A Content Security Policy may also easily break your site if you include inline script or external assets, unless you properly define the required policies and list allowed sources.

The default CSP sent by PitonCMS is inherently a strict policy, see [Strict CSP With Google](https://csp.withgoogle.com/docs/strict-csp.html) for an explanation of the following:

```apache
Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce' 'strict-dynamic' 'unsafe-inline'; connect-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self'; base-uri 'none'; frame-ancestors 'self'
```

This CSP is meant to _prevent_ unauthorized code, styles, images from running on your website. This will also block your good code from running unless you add it to the allowed list. If you are unable to make a feature or design work it is possible the CSP is blocking the code from running. If the CSP is blocking functionality on your website you will see the errors in the browser developer Console or Network panels to help you debug.

By default, PitonCMS blocks all scripts (inline and external) unless you include a nonce in the script element, including all JS analytics tracking code. To enable javascript, see the section on [CSP Nonces](#csp-nonce) below.

To modify the CSP header define a new policy as a string in `config/config.local.php`:

```php
$config['header']['Content-Security-Policy'] = "default-src 'self'; script-src 'strict-dynamic'; img-src *";
```

Because the CSP is potentially a very long string on a single line, it may be easier to use the concatenation operator `.` to separate all directives onto separate lines for easier review. Note, all directives should end with a semicolon, but the last directive policy does not require a trailing semicolon:

```php
$config['header']['Content-Security-Policy'] =
    "default-src 'self'; " .
    "script-src 'self' 'nonce' 'strict-dynamic' 'unsafe-inline'; " .
    "connect-src 'self'; " .
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
    "font-src 'self' https://fonts.gstatic.com; " .
    "img-src 'self'; " .
    "base-uri 'none'; " .
    "frame-ancestors 'none'";
```

#### CSP Nonce
PitonCMS will automatically generate a 128bit base64 encoded nonce which is sent by default in the [CSP](#content-security-policy) `script-src` directive, and is available to print in all templates as `site.environment.cspNonce`. This nonce is regenerated on each page view.

To add the nonce to other CSP policy directives, just include the string `'nonce'` in the directive source list (with the single quotes), and also add the Twig variable `{{ site.environment.cspNonce }}` to your nonceable elements. At runtime the header `'nonce'` string will be replaced by the actual base64 nonce and the matching nonce key printed in your HTML templates.

For example, this CSP `script-src` directive:

```apache
script-src 'self' 'nonce' 'strict-dynamic' 'unsafe-inline';
```

Can be applied in your templates as:

```html
<script nonce="{{ site.environment.cspNonce }}">
    let color = "blue";
    // Or analytics JS code, for example
</script>
Or, for external resources:
<script nonce="{{ site.environment.cspNonce }}" src="{{ baseUrl() }}/assets/js/custom.js"></script>
```

To remove the default nonce from the CSP header, in `config/config.local.php` define a new CSP policy with directives that do not include the string `'nonce'`.

For tracking analytics code, in addition to printing the nonce in the inline script elements you may also need to allow the analytics endpoint sources under the `connect-src` policy.

If a CSP header policy requires a nonce to execute inline code the browser will check that the `nonce-<key>` in the element matches the header `nonce-<key>` and allow that block to run.

## Session Tokens
By default, user session tokens expire after 2 hours after the last administration request. You can change this value by stting the session `secondsUntilExpiration` configuration option. You can also use an expression to set a longer session. For example, to increase the session to 30 days in `config/config.local.php`:

```php
$config['session']['secondsUntilExpiration'] = 60*60*24*30;
```