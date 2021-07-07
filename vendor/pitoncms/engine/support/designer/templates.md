# Page and Element Templates
A page template consists of an HTML layout file and a JSON definition file with matching names, that represent a type of page structure that can be reused for different content.

Static pages and Collection Detail pages both use page templates, but a static Page is defined in the JSON file as `"templateType": "page"` while a Collection Detail is defined as `"templateType": "collection"`.

PitonCMS uses [Twig](https://twig.symfony.com/doc/3.x/) to render templates, and understanding how Twig works as a designer will simplify building custom websites with PitonCMS.

## Overview
Page templates have designer defined **Blocks** which are broad areas of a page layout design, and **Elements** which are small reusable HTML template files, which a client user can select to put into the predefined Blocks in the page.

In this example of the **With Hero** template, the web designer defined the Blocks ("Hero" and "Content"), while the client can simply add content **Elements** to the Blocks when editing the page.

![Page Template Overview"](/admin/img/help/pageBlockElementOverview.png)

When designing a new website, identify how many unique page layouts are needed. Then for each layout then determine content areas for each page. These content areas might be considered Blocks. Examples might be, main content, sidebar - all broad regions of a reusable page.

Then define the various Elements you need for a custom website. Elements may include a basic text content element, a reusable contact form, a video embed element, etc.

In addition to Blocks and Elements, your HTML layout _may_ include:

* A base layout. Using Twig's [extends](https://twig.symfony.com/doc/3.x/tags/extends.html) syntax, you can define a base file to load shared components such as headers, footers, boiler plate sections. Each of your page templates can then inherit the base layout.
  * **Tip**: You can also have intermediate base layouts between the base layout and the page template. This may solve some complex layout requirements without repeating code.
* Includes. With Twig's [includes](https://twig.symfony.com/doc/3.x/functions/include.html) supports reusable blocks of HTML kept in separate files, to help declutter complex templates.
* Twig [Macros](https://twig.symfony.com/doc/3.x/tags/macro.html). Macros are HTML functions that can be used in templates, and are a great way to organize reusable code where the same HTML statement needs to repeat on a page but with different data.

## Templates Directory Structure
The **Structure** directory in the root of your project consists of:

* `definitions` This contains JSON files for:
  * [Contact Form](/admin/help/designer/contact) custom fields (`contactInputs.json`)
  * [Navigators](/admin/help/designer/navigation) (`navigation.json`)
  * [Custom Settings](/admin/help/designer/settings) (`siteSettings.json`).
* `sass` For your Sass files (if you use Sass).
* `templates` Where your Page templates (and matching JSON), Element templates (and matching JSON), includes, and system templates.

The `templates` directory will contain most of your HTML files. This sub directory contains:

* `elements` For custom element HTML and JSON files
* `includes` For Twig include and macro files
* `pages` For custom page HTML and JSON files
* `system` Reserved for system templates such as 404 Not Found

For the `elements` and `pages` template directories, you can create any level of additional sub-directories to organize your files. However, each `page` or `element` HTML file has a matching JSON definition file, and these two files **must** be in the same sub-directory as siblings, and must have the same filename (except for the extension `.html` and `.json`).

## Page HTML and JSON
At a minimum a Page template consists of one HTML file and one matching JSON file (by name) in the same sub-directory.

The HTML file can contain static HTML, or can extend an optional base layout file, and if you want the client user to add dynamic content then you need at lest one Block defined. The built in Content Page HTML template (`contentPage.html`) has:

```html
{% `extends` 'pages/_base_layout.html' %}

{% block body %}

<div class="container mainContent">
  {{ getBlockElementsHtml(page.blocks.contentBlock) }}
</div>

{% endblock body %}
```

Explanation:
* The `extends` statement means to inherit the surrounding HTML from the `pages/base_layout.html` file
* The `block body` and `endblock body` tags contain the content that will replace the matching tags in the base layout file
* There is one block to hold dynamic content. This content is wrapped in a `<div>` but could be any markup
* Twig print statement `{{ }}` to print the dynamic content
* A Twig PitonCMS function `getBlockElementsHtml()` that returns all dynamic elements saved for this block
* `page.blocks.contentBlock` is the Twig variable which contains the dynamic content (`page` key > `blocks` key > `contentBlock` which matches the block definition in the c`ontentPage.json` file)

In this brief template we have a custom layout suitable to print any generic dynamic content.

The JSON file contains the important information about this template, how it is used, any custom blocks or settings, and element restrictions. The built in Content Page JSON file (`contentPage.json`) has:

```json
{
    "templateName": "Without Hero",
    "templateDescription": "Page without hero image and with content blocks.",
    "showFeaturedImage": true,
    "templateType": "page",
    "blocks": [
        {
            "name": "Content",
            "key": "contentBlock",
            "description": "Home page text areas",
            "elementTypeDefault": "text/text"
        }
    ]
}
```

Explanation:
* `templateName` Required. The name of the template displayed to the user when creating a new page
* `templateDescription` Required. Optional. The description of the page template to help the user select the right template
* `showFeaturedImage` Optional. A flag (true or false) on whether the user can select a primary image for page content
* `templateType` Required. The type of template, `page` for static content or `collection` for groups of related content
* `blocks` An array of blocks that define areas of the page layout

Other keys not shown:
* `showSubTitle` Optional, default true. Whether the page should have a sub title field.

The blocks array contains objects `{ }` representing how the block should display and be controlled. For each block on your page, define a block in the JSON file. Keys:

* `name` Required. The name of the block displayed to the user when editing the page
* `key` Required. A page-unique string to identify that block in your template code. Must not contain any spaces, and only consist of a-z, A-Z, 0-9, underscore ( _ ), max length 60 characters. Use this key in the page template blocks variable `page.blocks.<key>`
* `description` Optional. The description of the block displayed to the user when editing the page
* `elementTypeDefault` Optional. Which element option (the element path and filename without the extension) should be automatically selected when adding elements to the block
* `elementTypeOptions` Optional. An array of allowable elements (by path with filename without extension) to display to the user. If no `elementTypeOptions` is provided, the user will see all elements in the elements directory.
* `elementCountLimit` Optional. The max number of elements allowed by design. If no value is provided, then the user can add any number of elements.

Pages and Block Elements can also support custom settings for small bits of dynamic information.

## Elements HTML and JSON
Elements are the smallest unit of reusable HTML on your website. As a designer, you can create custom elements (or delete built in PitonCMS elements) as needed for the site design. However, the client will select available elements when they create content; elements are not hard coded into a page. As a designer, you can restrict the type of elements that can be used for any block, including the number of elements.

The HTML file for a basic content element (which as a title and rich textarea) can be:

```html
<section class="textElement">
  <h2>{{ element.title }}</h2>

  {{ element.content }}

</section>
```

When the element HTML is loaded, the element data is available in the `element` array. At a minimum, there is a `title` and `content` (from the rich text editor) available in each element. Page level dynamic data in the `page` array in the element.

Element HTML files do not need to contain variables to print, it can consist of just boilerplate HTML and text.

Other variables may also exist depending on the element type.

The element JSON file definition is:

* `elementName` Required. The name of the element displayed to the user
* `elementDescription` Optional. The description of the element displayed to the user
* `enableInput` Optional. Display additional built-in input option for the type of element (just one option). Options are "collection", "embedded", "image", and "gallery"
* `showContentTextarea` Optional, defaults to true.
* `enableEditor` Optional, defaults to true. Enables the rich text editor
* `settings` Optional. An array of [Custom Settings](/admin/help/designer/settings).

