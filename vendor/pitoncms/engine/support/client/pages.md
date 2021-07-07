# Pages and Collection Detail Pages

*Pages* have content accessible at a specific URL. Typically these pages hold mostly *fixed* content such as Home, About, Location etc. and are part of the main website navigation. You can create new Pages from the **Content > Pages > Add [Template]** menu.

Collections are groups of *related* pages such as blog posts, recipes, services, activities etc. and consist of *Collection Detail Pages* (each accessible at a specific URL), and *Collection Summaries* which is a group of the related links to the detail pages. You can create a Collection Detail Page from the **Content > Collections > Add [Template]** menu, or from the view all collection details page.

Pages and Collection Detail Pages are structured by **Templates** around designer defined page **Blocks** that represent broad areas of the page design, to which you can add one or more **Elements** that contain your page content and display media.

There may also be designer defined **Custom Page Settings** in the page template, blocks, and elements, that store specific bits of information for the page.

You can pre-publish pages by setting the published date to a future date.

## Page Structure
Pages and Collection Detail Pages can be created from **Templates**, which are created as part of your website design by your designer. These templates govern the page layout and structure. You can add custom content as **Elements** to predefined design **Blocks** in the page.

In this example of the **With Hero** template, the web designer defined the Blocks ("Hero" and "Content"), while the client can simply add content **Elements** to the Blocks when editing the page.

![Page Template Overview"](/admin/img/help/pageBlockElementOverview.png)

When editing a page, you will see:
* Page Title as the display name for the page, and the Sub Title (Optional) which slightly expands on the title
* Custom Page Settings (Optional) Custom page data configured by the designer
* Content Blocks To which you can add one or more elements to hold dynamic data (this is where you enter the actual page content)
* About This Page (Sidebar), which has the publish date, URL slug, Meta Description, and primary media image

### Editing the Page
The main content sections include:

* **Title** Required. The heading title for this page.
* **Sub Title** Optional, and may be hidden by design.


You can unpublish a page by changing the publish date to the future, or removing the publish date. **Note**: This may break links to your website!

### About This Page

* **Published Date**
* **Slug** Required. This is the relative URL to access the page content. By default the URL slug defaults to the title (after being cleaned), but can be changed. **Note**: Once the page is published, the URL slug can only be changed by unlocking the slug. Changing the URL may links to your page!
* **Meta Description** Optional. A brief description of the page and content, and might be used by search engines as the displayed text in search results.
* **Page Image** Optional, and may be hidden by design. Select an image from your media collection to display in the page. **Note**: How this featured page image works may depend on your website design.
* **Published Date** (Sidebar). The date when the page should be visible.

The page can be in three statuses:

* **Draft** The page is not visible. No publish date has been set.
* **Pending** The page is not visible. A future publish date has been set, and the page will automatically be visible on that date.
* **Published** The page is visible.


### Custom Settings
Your web designer may include custom page settings, which are specific bits of information to be used in the page. This may include examples such as:

* The link and text for a call to action button
* The color of a page feature
* A banner text to display on the page

If you do not see the **Custom Settings** tab, then the template you are using does not include any.

### Content Blocks
Blocks represent whole areas of the page design in the template you are using. A block might represent the top half of the page, or a side bar, or where a contact form should be placed. The client user cannot change how a block is positioned or works.

The client user can add **Elements** to a block to hold content to display within the block on the page.

### Block Elements
Elements are chunks of content information the client can add, edit, or delete within a block as you edit the page in PitonCMS. Elements are predefined by the designer, and may include (but may vary based on the custom design):

* Basic Text
* Media Image
* Hero Image
* Embedded Video
* Contact Form
* Media Gallery
* Collection Summary

The designer may restrict the type of Element you can select for some Blocks to be consistent with the design theme. They may also create additional blocks.

The Gallery element requires a media category be selected, and displays all media in that category.

The Collection Summary element displays a group of links to the detail pages within that collection.

**Note**: Elements nearly always include a Title and a Content field, and you can include a media image link directly within the content text.

Text content areas in PitonCMS use [Markdown](https://www.markdownguide.org/basic-syntax/) to format and style. You can apply your own markdown syntax, or use the editor buttons.

**Note**: When you delete an element it is an immediate full delete, and does not wait for the page changes to be saved first.

### Media
To use media (images and files) in your content, you should first upload and categorize your media in the Media menu. [Media help](/admin/help/adminMedia).

## Viewing Pages
To manage your pages, go to the **Content > Pages > All Pages** menu. From here, you can see all pages in any status, and filter by status. Click the **Edit** link to edit or delete that page.

To manage your collection detail pages, go to the **Content > Collections > All Collections Pages** menu. From here, you can see all collection pages in any status, and filter by status or collection name. Click the **Edit** link to edit or delete that page. [Collection help](/admin/help/adminCollection).

## Create New Page
To create a new page, go to **Content > Pages > [+ Template Name]**.

**Note**: To add a link in to your navigation to the new page, go to **Content > Navigation** to add the page. You can add the page to your navigation even if the page is not yet published; the page will on appear in the navigation once it auto publishes.

To create a new collection detail page, go to **Content > Collections > Add [Template Name]**. You can also add a collection detail page from the list of collection pages.

**Note**: You cannot add collection detail pages to the main navigation. Collection detail pages only appear as an entry in a Collection Summary Element.


## Edit Existing Page
To edit an existing page, go to **Content > Pages > All Pages** and find the desired page, and click edit.

## Delete Page
To delete a page, find the page in **Content > Pages > All Pages** and click edit. Press the delete button, and acknowledge the warning prompt. The page will be permanently deleted, including all information about the page such as any elements and navigation links, and cannot be recovered.

**Note**: You might consider unpublishing the page as a safe way to disable a page.

## Add Navigation
Your custom website design may likely have some navigation lists, which have links to site pages. When creating a page, you need to manually add the page to one or more navigation bars so that visitors and search engines can find your page content. The number of navigation bars and their position is determined by your web designer. [Navigation help](/admin/help/adminNavigation).
