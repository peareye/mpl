# Overview

PitonCMS is an easy to use, powerful Content Management System (CMS) for personal and small business websites. Your website designer has configured and built this website for your specific requirements, and this administration console allows you to easily update your website content.

If you are new to your website, here are a few things to review.

## Logging In
PitonCMS does not store confidential passwords. When your website was setup by your designer, your email address was added as a registered user. To login, simply go to your website and add "[/login](/login)" to the URL and submit.

Enter your email address and click the **Request Login** button. If the submitted email matches a known user registered in PitonCMS, a login link will be sent to you with a one-time use login token that takes you to the PitonCMS administration console. The link sent to you expires in 15 minutes, and cannot be reused.

Note, for security reasons PitonCMS login links are unique to the device and web browser. This means you must request the PitonCMS login link from the _same_ device (laptop, phone, tablet etc.) that you use to access your email. Each device you use will require a separate login request.

## Settings Menu
* **General** Browse the site wide settings and confirm the values are correct. These can be changed at anytime. Consider adding a [Tinify](https://tinyjpg.com/) key to optimize uploaded image media. Your web designer can you help you register a free key.
* **Contact** If you have a contact form on your website enter your email address to have website contact messages forwarded directly to you, and also set the acknowledgement message after the contact form is submitted. If you do not set a forwarding email, you can still receive messages which are available to view under **Mailbox**.
* **Social** Add links to your social media accounts.
* **Users** Consider adding another user account, or a back up administrator email address.

And of course, browse these support documents!

## Content Menu
Most of the website content can be managed from the **Content** menu.

Pages are the building blocks of any website and have content accessible at a specific URL. Pages can be *fixed* (static) or part of a *collection* of related pages.

### Pages
Fixed pages typically include core content such as Home, About, Location, and Collection Summaries etc. and are part of the main website navigation.

### Collection Pages
Collections are groups of *related* pages such as blog posts, recipes (even a specific category of recipe), services etc.

*Collection Detail Pages* are specific pages within a collection, a blog post or a recipe for example. When you publish a new collection detail page the link to the new page is automatically added to the *Collection Summary* or navigation bar.

*Collection Summaries* display the group of links to the collection detail pages (think of the collection summary as an index pointing to the detail pages). You can also add a link to the collection as a whole to any navigation bar, so that new content automatically appears in the website navigation.

You can have multiple collections on your website, to categorize content.

### Page Structure
Page and Collection Detail Page templates contain **Blocks** that represent broad areas of the page template, to which you can add one or more **Elements** that contain your page content.

There may also be designer defined *Page Settings* or *Element Settings* in the page template. These are little bits of information you can define when you edit the page to enhance how page the displays (as built by the designer).

You can *pre-publish* pages by setting the publish date to a future date.

### Navigation
You can define how page links appear in your site's navigation, including the order of links, link text, and also sub-menu (dropdown) links.

## Media
Before you can display images and other media in your pages or collection detail pages, you need to upload the media files from the **Media** menu. You can upload any media image or PDF file type, but video and other large graphics should be hosted on a video streaming platform such as [YouTube](https://youtube.com) or [Vimeo](https://vimeo.com/). You can then embed the video player HTML into your PitonCMS page as an element.

PitonCMS recommends getting a [Tinify](https://tinyjpg.com/) key to optimize image files. You can get a free key that supports 500 media operations a month (about 100 PitonCMS image uploads). Go to [Tiny Developer API](https://tinyjpg.com/developers) and enter your email address to receive a key that you save in **Tools > General** Site Settings. Your web designer can help you set this up.

## Messages
If your site has a contact form enabled, your contact messages are saved under the **Mailbox** menu. You can search, delete, or archive your messages.

## Settings
PitonCMS site application management is handled under the **Settings** menu, including:

* General Site Settings
* Contact Form Settings
* Social Media Links
* Website Administration Users
* Sitemap updates to help Search Engine Optimization
* Define Collection Groups
* Define Media Categories

## Issues
If you encounter issues with PitonCMS, please submit them on [GitHub PitonCMS](https://github.com/PitonCMS/Piton/issues).