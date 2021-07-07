# Contact Forms

Let visitors contact your client! With PitonCMS you can easily build contact forms that save visitor messages to the PitonCMS **Mailbox**, and are optionally emailed the client administrator.

You can also include custom form fields that allow you to create different types of contact forms including registration forms, order forms, and more.

From the PitonCMS Mailbox the client administrator can manage, search, archive, and delete messages.

## Basic Contact Form
Contact forms are best created as a **Page Element** that the client can add to a page. However, the form can also be coded directly into the Page Template if desired.

The forms are submitted by XHR (Ajax) to provide a seamless visitor experience. After being submitted the form is then replaced with an acknowledgement message set in **Settings > Contact > Contact Form Submission Acknowledgement**. If an email address was provided **Settings > Contact > Contact Form Email** then the client administrator is emailed a copy of the message as well.

A basic contact form structure.

```html
{% import "includes/_macros.html" as pitonMacro %}
<form class="contact-form" id="contact-form" method="post" accept-charset="utf-8" data-contact-form="true">
    <input type="hidden" name="context" value="{{ page.title }}">

    <label>Your Name</label>
    <input type="text" class="contact-form__form-control" name="name" maxlength="100" placeholder="Name" autocomplete="off">

    <label>Your Email<span class="text-danger">*</span></label>
    <input type="email" class="contact-form__form-control" name="email" maxlength="100" placeholder="Email address" required autocomplete="off">

    <label>Message</label>
    <textarea class="contact-form__form-control" rows="5" name="message"></textarea>

    <button class="btn" type="submit">Submit</button>

    {{ pitonMacro.contactHoneypot() }}
</form>
```

### Form Structure
The attribute `data-contact-form="true"` in the HTML `form` element is used by PitonCMS to trigger the Ajax form submission.

The `value` of the hidden `input` with the attribute `name="context"` can be set to any desired value to provide information to the client user on _which_ contact form was used to submit the message. This is useful if you have multiple types of submit forms on the website. The value can be static text, or in the example above is dynamically set to the current Page Title.

The inputs `name="name"` and `name="email"` are limited to 100 characters and should be included in all forms. The `textarea` `name="message"` captures a free text area, and is optional.

Be sure to include a `button` of `type="submit"` in the `form`. By using a _submit_ button, browsers with HTML5 support will validate and alert the user to any issues.

### Honeypot
To manage message spam PitonCMS contact forms can use a honeypot, which is a hidden email input set to a known value. Bots will typically attempt to complete all form inputs of `type="email"`. If the expected value is altered then PitonCMS will quietly ignore the whole message.

To include the honeypot be sure to import the Piton Twig Macro somewhere at the top of the page above the form, and then print the contact honeypot.

```html
{% import "includes/_macros.html" as pitonMacro %}
```

And then print the honeypot macro anywhere inside the form.
```html
{{ pitonMacro.contactHoneypot() }}
```

## Custom Input Fields
To extend the contact form with custom field inputs to create ordering or registration forms and more:

1. Add any custom inputs to your form and give each custom input a unique `name`. The name should only include letters, numbers, dashes, or underscores without spaces
2. Register the custom input in `structure/definitions/contactInputs.json`. You can put all custom contact forms inputs in this single array

For example, to include an Arrival Date and Departure Date on a guest contact form add these inputs to your form.

```html
<label>Arrival Date</label>
<input type="date" class="contact-form__form-control" name="arrivalDate">

<label>Departure Date</label>
<input type="date" class="contact-form__form-control" name="departureDate">
```

Note, any HTML5 input `type` can be used, so you can use `date`, `integer` and more to provide validation.

Then in `contactInputs.json` add to the array `[ ]` two custom inputs to match your form.

```json
[
    {
        "name": "Arrival Date",
        "key": "arrivalDate"
    },
    {
        "name": "Departure Date",
        "key": "departureDate"
    }
]
```

Set the **name** to a user friendly descriptive label to use in the email and message mailbox, and **key** matches the HTML form custom input `name` attribute.

When the form is submitted only custom form inputs with a matching key in the definition file will be saved, others will be ignored.
