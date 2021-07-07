# Site and Page Settings

With custom settings, you can create user-managed targeted custom key: values pairs to present as specific display data or in template logic.

_Site_ settings are global and are available on every page, while _Page_ settings are unique to each page. You can also define settings for page _Elements_.

When you add a new setting to the JSON definition file, that input is made available to the user in site Settigns or in the Page content editor. Be sure to commit these JSON files so that you can push to other environments.

If you delete a setting from the definition file, the user will see an orphaned flag and can delete the saved value for that setting. Until being hard deleted, the previously saved value remains available in the templates.

## Examples
As a desinger you can utilize settings in many different ways, and not just to print values. Examples include:

* Add a new social media platform link to site settings to print in the footer on all pages
* Add a text area to enter a store address, or create a separate input for each data point in the address.
  * As a site setting this could be used on all pages, or perhaps just in a single template to allow for more targeted styling
* Create a setting with a select list of predefined values to avoid user input errors
* Create a setting as a flag to control page flow
  * For example, create a pair of begin and end effective `date` inputs, and then use logic in the Twig template to only display content if today is within the effective date range

## Site Settings
Site settings are defined in `structure/definitions/siteSettings.json`, and are updated in the **Settings** manager. Site settings are available on all pages in your template, under the `site.settings.` array and indexed with the `key` you define in the JSON file. You are welcome to delete the default settings that come with PitonCMS as examples.

To edit or add site settings, in the `siteSettings.json` file edit a setting object under the `"settings"` key:

```json
{
 "settings": [
	{
	 "category": "site",
	 "label": "Google SEO Verification Link",
	 "key": "googleWebMaster",
	 "value": "",
   "inputType": "text",
   "placeholder": "Google Search Console verification"
    }
 ]
}
```

## Page and Element Settings
Page template settings are defined the custom page template definition file in `structure/templates/pages/*.json`, and are updated in the **Content** page manager for pages using that template. A user can then define different values in different pages using the same template.

Page settings are available on the pages using this template, under the `page.settings.` array and indexed with the `key` you define in the JSON file. You are welcome to delete the default settings that come with PitonCMS as examples.

To edit or add page settings, in the page template JSON file add a setting object under the `"settings"` key:

```json
{
 "blocks": [/* */],
 "settings": [
	{
	 "category": "page",
	 "label": "Google Webmaster Verification Link",
	 "key": "googleWebMaster",
	 "value": "",
	 "inputType": "text"
    }
 ]
}
```
You can also define Element settings in the element definition file, but change the `category` to `element`.

## Setting Definitions
Settings allow for very specific input types. The basic setting object has these properties at a minimum:

* `category` Where the input editor should appear, and which category of setting this is
  * For global settings, options are `site`, `social`, `contact`
  * For page settings you must use `page`
  * For element settings use `element`
  * The `piton` category is reserved for system settings and should not be used as custom fields
* `label` The display label text for the input
* `key` Unique key you will use to access the variable in your templates. Must only contain a-z, A-Z, 0-9, _ (underscores) and max 60 characters without spaces
* `value` An optional default value. Note, the default value is presented to the user when viewing the setting, but not saved to the database until a user views and then saves. Max 4,000 bytes.
* `inputType` The type of input to present, defaults to `text` if left blank
  * Options are: `text`, `select`, `textarea`, `color`, `date`, `email`, `number`, `tel`, and `url`.
* `help` Optional help text for input
* `placeholder` Optional input placeholder text. Only works with normal inputs (not `textarea` or select `types`)
* `options` If the `inputType` is `select`, then add an array of `name` and `value` options for the select list

### Input Setting
The default, and most used is a basic `text` input setting. You can also set `inputType` to `text`, `select`, `textarea`, `color`, `date`, `email`, `number`, `tel`, and `url`.

```json
{
    "category": "site",
    "label": "Google Webmaster Verification Link",
    "key": "googleWebMaster",
    "value": "",
    "inputType": "text"
}
```

### Textarea Setting
Presents a textarea to allow for longer free form content or code (such as tracking code). All values have a max length of 4,000 bytes.

```json
{
    "category": "site",
    "label": "Website Contact Address",
    "key": "contactAddress",
    "value": "",
    "inputType": "textarea"
}
```

### Select Input
Creates a select list of predefined values for the user.

```json
{
    "category": "site",
    "label": "Favorite Color",
    "key": "favColor",
    "value": "blue",
    "inputType": "select",
    "options": [
        {
            "name": "Blue",
            "value": "blue"
        },
        {
            "name": "Green",
            "value": "green"
        },
        {
            "name": "Yellow",
            "value": "yellow"
        }
    ]
}
```
