# Print Website Data

All website data is saved to a MySQL database, and when a page is rendered the relevant data is made available to the page template.

When you include Twig data variables in your custom templates, the variable and surrounding delimiters are replaced with the saved text. For example, if you want to print the page title in your template, include:

```html
<h1>{{ page.title }}</h1>
```

and when Twig renders the page you will see:
```html
<h1>My Life on the Water</h1>
```

The data is injected into that template as a data `array`. To access a specific key in the array use dot notation. So the `title` key inside the `page` array is accessed with `page.title`.

If you don't know the key or want to print all elements in the array (such as in a list) then use the Twig [for loop](https://twig.symfony.com/doc/3.x/tags/for.html) syntax:

```html
<ul>
    {% for user in users %}
        <li>{{ user.username }}</li>
    {% endfor %}
</ul>
```

**Tip:** If you are unsure what variables are available to you in a page, add this Twig `dump()` debugger statement to your script to print all variables:

```html
{{ dump() }}
```
To get more details on a sub array item, E.g. Page Blocks

```html
{{ dump(page.blocks) }}
```

## Data Array
The standard data array payload injected into each page template contains:

* `page` User saved data for this page
* `site` General website global data
* `alert` System messages, which are handled automatically

To access any one of these variables, use the dot separator between array keys, as in `site.settings.theme`.

### Page Data
The **page** array key contains all variables unique to this page (and URL address) under the `page` key. The data is managed by the client user under the Content manager in the application.

#### Page Blocks
Each Page template should have defined one or more `blocks` representing broad areas of the layout design, such as body, sidebar, hero etc.

Under `blocks` are one or more of these custom blocks, defined in the page template JSON file in `structure/templates/pages/`. Each custom block needs a unique key name as an address. If you name a custom block in a page template as `"key": "sidebar"`, then you would access elements in this block as `page.blocks.sidebar.<elementIndex>`.

Each custom block may then have one or more elements (as defined in the definition file). Because the order of elements may vary when the user updates the page content, it is best to loop through the elements. To help with this ,it is always best to use the `getBlockElementsHtml()` Twig function that will print all elements using the correct element template:

```html
{{ getBlockElementsHtml(page.blocks.sidebar) }}
```

#### Page Settings
Page level settings behave like the global site settings, but are unique to each page. This is useful to define small data points that are user editable, or to add flags to conditionally display information, and more.

As a designer, you can define custom page settings in the page template JSON file.

#### Page Media (Featured)
If enabled in the page template JSON file, a user can specific a featured image to use. This can be used in a page hero, or even a thumbnail (automatically) in a collection summary. If enabled, the featured media array will contain these keys:

* `aspectRatio` Calculated image aspect ratio
* `orientation` Either `landscape` or `portrait`
* `id` Media ID
* `filename` Media filename
* `width` Original image width
* `height` Original image height
* `caption` Media caption

#### Other Page Data
In addition, each `page` array contains these keys to use in your templates:

* `id` Page ID
* `collection_slug` Optional, collection URL segment
* `collection_title` Optional, collection title
* `collection_id` Optional, collection ID
* `page_slug` Page URL segment
* `template` Page template to load
* `title` Page title
* `sub_title` Page subtitle (if enabled)
* `meta_description` Page meta description
* `published_date` Page published date
* `media_id` Featured media ID (if enabled)
* `created_by` User ID who created the page
* `created_date` Created date (which can be different from the published date)
* `updated_by` User ID who last updated the page
* `updated_date` Last updated date and time

### Site Data
**Site** data contains an array of global variables under the `site` key, all client user managed in the **Settings** manager. All site variables are available on each page.

#### Site Settings
As a designer, you can add or remove global site `settings` for each website you build with PitonCMS, in the `structure/definitions/siteSettings.json` definition file. Your client user can then set the desired values for those custom site settings. You can access these variables as `site.settings.<key>` where you use the key you defined in the definition file.

Site settings can be useful data such as a link to a Twitter account, but can also be used as a flag (Y or N, for example) to control application flow.

These settings are core to PitonCMS and cannot be changed in the design definition file:
* `siteName`
* `tinifyApiKey`
* `contactFormEmail`
* `contactFormAcknowledgement`

#### Site Environment
The `environment` contains system information such as the logged in user information, the PitonCMS version, production flag. These cannot be modified, but can be used in the design. These are the environment keys under `site.environment`:

* `appAlert` System messages which are handled automatically
* `engine` PitonCMS Engine version number
* `schema` Database schema build version
* `production` Production flag (boolean, true or false)
* `assetVersion` Used in PitonCMS admin to break the cache on external asset files
* `currentRouteName` The currently executed route name

For example, the `production` variable is a boolean flag (true or false) that relies on `$config['environment']['production']` setting in `config.local.php` that is unique to each environment. You can use this flag in your code to conditionally print content depending on whether the code is running on a development server, or a production server. For example, to only run tracking analytics code in your layout template when in production, wrap the analytics code in a Twig `if` statement:

```html
{% if site.environment.production %}
    <script>
        /* My Tracking Analytics Code */
    </script>
{% endif %}
```

#### Site CSRF
The CSRF token is used to help prevent [Cross Site Request Forgery](https://owasp.org/www-community/attacks/csrf) attempts on POST submissions. The use of this token is automatic.

## Using Dates
Dates are stored in PitonCMS in the ISO 8601 format, YYYY-MM-DD. If you print a date variable you will get exactly that, E.g. "2020-10-05".

To print a date or datetime in a more friendly way that is localised to the user, you can provide the date format mask in the Twig `date()` filter using standard [PHP date formats](https://www.php.net/manual/en/datetime.format.php):

```html
<div class="published-date">Published on {{ page.published_date|date('F jS, Y') }}</div>
```
Would then print:
```html
<div class="published-date">Published on October 5th, 2020</div>
```
