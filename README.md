# Simple RSVP Comments

A lightweight WordPress RSVP plugin with guest messages, instant AJAX submissions, and a clean comment-style display.

Simple RSVP Comments is built for invitation pages, event websites, wedding landing pages, and simple guestbooks. Drop in a shortcode, let guests confirm their attendance, and show their wishes without reloading the page.

## Highlights

- RSVP form powered by the `[simple_rsvp_comments]` shortcode.
- Alternative shortcode support with `[rsvp_form]`.
- Guest name, attendance status, and message fields.
- Attendance options: `Hadir`, `Tidak Hadir`, and `Masih Ragu`.
- AJAX form submission with no page refresh.
- Live message list with pagination.
- RSVP entries stored as a WordPress custom post type.
- Admin dashboard menu for reviewing and editing entries.
- Custom admin column for attendance status.
- Admin meta box for updating attendance status.
- Frontend CSS and JavaScript kept in separate asset files.

## Project Structure

```text
simple-rsvp-comments/
|-- README.md
|-- simple-rsvp-comments.php
`-- assets/
    |-- simple-rsvp-comments.css
    `-- simple-rsvp-comments.js
```

## Installation

1. Download or clone this repository.
2. Copy the `simple-rsvp-comments` folder into your WordPress plugins directory:

```text
wp-content/plugins/simple-rsvp-comments/
```

3. Open your WordPress dashboard.
4. Go to `Plugins`.
5. Activate `Simple RSVP Comments`.

## Usage

Add the shortcode to any page or post:

```text
[simple_rsvp_comments]
```

You can also use the alias shortcode:

```text
[rsvp_form]
```

By default, the message list shows 5 entries per page. You can customize that with the `per_page` attribute:

```text
[simple_rsvp_comments per_page="10"]
```

## What Guests See

The shortcode renders:

- A name field.
- An attendance dropdown.
- A message field for wishes or comments.
- A submit button.
- A paginated list of submitted RSVP messages.

When a guest submits the form, the plugin saves the RSVP and refreshes the message list through AJAX.

## How It Works

The plugin registers a private WordPress custom post type:

```text
simple_rsvp_entry
```

Each RSVP submission is saved as a post:

- Post title: guest name.
- Post content: guest message.
- Post meta `_src_attendance`: attendance status.

The frontend communicates with WordPress through `admin-ajax.php` using two AJAX actions:

- `simple_rsvp_submit` for saving new RSVP entries.
- `simple_rsvp_load` for loading paginated RSVP messages.

## Guest Name Prefill

If your site provides a `[to_name]` shortcode, this plugin will automatically use it to prefill the guest name field.

This is helpful for invitation pages that already personalize guest names through another theme, builder, or invitation system.

## Styling

Frontend styles live in:

```text
assets/simple-rsvp-comments.css
```

Frontend behavior lives in:

```text
assets/simple-rsvp-comments.js
```

The stylesheet references the `Born2` and `"Merchant Copy"` font families. Make sure those fonts are loaded by your theme or page builder if you want the intended visual style. If they are not available, the browser will fall back to default fonts.

## Security

The plugin uses standard WordPress safeguards:

- Nonce validation for AJAX requests.
- Input sanitization before saving data.
- Attendance value validation.
- Capability checks when saving admin meta box data.

## Requirements

- WordPress.
- WordPress bundled jQuery.
- PHP supported by your WordPress installation.

## Roadmap Ideas

- Export RSVP entries to CSV.
- Add configurable attendance labels.
- Add shortcode attributes for form labels.
- Add moderation controls before messages appear publicly.
- Add settings page for styling and behavior.

## License

License not specified yet.
