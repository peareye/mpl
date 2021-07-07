# Navigation
PitonCMS supports creating multiple navigation bars (navigators), including drop down menus. As a designer, you can define a navigation bar, and let the user add or remove links from the navigation bar using the **Navigation** mananger.

Note, only static Pages, Collections, and custom links can be added to a navigator by the user. Links to individual Collection Detail Pages technically can be added to a navigation bar as placeholder link, but the individual Collection Detail Pages themselves do not appear as an option in the Navigation manager, only the Collection Summaries themselves.

For example, if you create a `main` navigator, the user can add the Home and About pages as navigation links. They can also add a Collection called "Blog Posts" (assuming this had already been created) so that new posts automatically appear in the navigator. However, to pin a specific Blog Post the user will need to copy and paste that URL as Placeholder navigation entry. This is where creating a static Page might be more appropriate.

You can define separate navigators for different navigation components, such as header main navigation, sidebar navigation, footer navigation and more.

## Navigators
To define a navigator (navigation bar), open `structure/definitions/navigation.json` and add to the `navigators` array:

```json
{
    "navigators": [
        {
            "key": "main",
            "name": "Main",
            "description": "Top of page primary navigation."
        }
    ]
}
```

Where `"navigators": []` is required as the root of navigator array, and each navigator has a `key`, a display `name`, and a `description`. To add another navigator, such as for a side bar, then add:

```json
{
    "navigators": [
        {
            "key": "main",
            "name": "Main",
            "description": "Top of page primary navigation."
        },
        {
            "key": "sidebar",
            "name": "Sidebar",
            "description": "Sidebar navigation."
        }
    ]
}
```

After saving, open Navigation manager to see the new navigator.

**Note**: Once you define a navigator key, you cannot change the name!

## Displaying Navigators
To display a navigator in your template, use the Piton Twig function `getNavigator('main')` and pass in the name (key) of your navigator. This will return an array of navigation entries, that you can loop over to print each link. To print the actual anchor `href` link, be sure to use the PitonCMS Twig function `getNavigationLink()` to derive the correct URL.

For example, to print the main navigator with an active link class on the current page:

```html
<ul class="navigation">
    {% for link in getNavigator('main') %}
        <li class="nav-item {% if link.currentPage %}active{% endif %}">
            <a href="{{ getNavigationLink(link) }}">{{ link.title }}</a>
        </li>
    {% endfor %}
</ul>
```

### Navigation Data
These keys are available in each navigation link element returned by `getNavigator()`:

* `id` Navigation ID
* `navigator` Name of navigator for this link
* `parent_id` If this is a child link, then this is the parent navigation ID (otherwise null)
* `currentPage` Boolean flag if this is the current page
* `sort` Numeric position of this link relative to siblings
* `nav_title` Override link title text defined in Navigation manager
* `url` Link URL (if placeholder link)
* `collection_id` Collection ID if part of a collection
* `collection_title` Collection title if part of a collection
* `collection_slug` Collection URL segment if part of a collection
* `page_id` Page ID
* `page_title` Page title
* `published_date` Page published date
* `page_slug` Page URL segment
* `title` Link title
* `childNav` Has child navigation array, if this parent has children

### Child (Dropdown) Menus
PitonCMS supports dropdown child menus (just two levels), and if a top level navigation item has a child, that parent navigation item will have the key `childNav`, which contains the child navigation array.

To manage the HTML around this, in the Twig loop use an `if` condition to check if the current navigation item has a `childNav` to print the child navigation loop and HTML, and if not then print a normal top level link.

```html
<ul class="navigation">
    {% for link in getNavigator('main') %}
        {% if link.childNav %}
        <!-- This parent link has child navigation links -->
        <li class="nav-item {% if link.currentPage %}active{% endif %}">
            <a href="{{ getNavigationLink(link) }}">{{ link.title }}</a>
            <ul class="navigation-child">
                {% for subLink in link.childNav %}
                <li class="nav-item">
                    <a href="{{ getNavigationLink(subLink) }}">{{ subLink.title }}</a>
                </li>
                {% endfor %}
            </ul>
        </li>
        {% else %}
        <!-- This is the top level navigation link without child links -->
        <li class="nav-item {% if link.currentPage %}active{% endif %}">
            <a href="{{ getNavigationLink(link) }}">{{ link.title }}
            </a>
        </li>
        {% endif %}
    {% endfor %}
</ul>
```