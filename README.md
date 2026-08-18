# Simple RSVP Comments

A playful WordPress RSVP plugin for invitation pages, wedding websites, event pages, and compact guestbooks.

Simple RSVP Comments gives guests a friendly RSVP form, saves their response, and shows their wishes instantly without refreshing the page. The interface uses a warm pixel-art style with compact pagination, colorful attendance badges, and name-based avatar initials.

Current version: `1.0.7`

## Why Use It

- Add an RSVP section with one shortcode.
- Let guests submit attendance and wishes without page reloads.
- Show responses immediately in a styled message list.
- Keep the admin experience simple with a dedicated RSVP Entries screen.
- Use playful styling without depending on a page builder.

## Quick Start

1. Upload the plugin folder to:

```text
wp-content/plugins/simple-rsvp-comments/
```

2. Activate `Simple RSVP Comments` from the WordPress Plugins screen.

3. Add this shortcode to any page or post:

```text
[simple_rsvp_comments]
```

That is enough to show the form, save RSVP entries, and render guest messages.

## Shortcodes

Main shortcode:

```text
[simple_rsvp_comments]
```

Alias shortcode:

```text
[rsvp_form]
```

Change the number of messages shown per page:

```text
[simple_rsvp_comments per_page="10"]
```

Default `per_page` value: `5`.

## Guest Experience

Guests see a focused RSVP form with:

- Name field.
- Attendance dropdown.
- Message or wishes textarea.
- Pixel-style submit button.
- Success or error feedback after submit.

After submission, the message list refreshes through AJAX, so the page stays smooth and does not reload.

## Message List

Each RSVP message is displayed as a styled card with:

- Guest name.
- Attendance badge.
- Guest message.
- Submission date.
- Initial avatar based on the guest name.
- Compact pagination with ellipsis when there are many pages.

Initial avatar rules:

```text
Ayu                         -> A
Saiful Hadi                 -> SH
Tamu Undangan VIP           -> TUV
Made Bagus Putra Wibawa     -> MBP
```

The avatar uses a maximum of 3 letters. Frame colors are randomized in JavaScript whenever comments are rendered, so colors may differ after refreshes or across devices.

## Admin Experience

The plugin registers a private custom post type:

```text
simple_rsvp_entry
```

In the WordPress dashboard, admins can open `RSVP Entries` to review and edit submissions.

Each entry stores:

- Post title: guest name.
- Post content: guest message.
- Meta `_src_attendance`: attendance status.

The admin list also includes an attendance column, and the edit screen includes a small meta box for changing the attendance value.

## Attendance Options

The default attendance values are:

```text
Hadir
Tidak Hadir
Masih Ragu
```

The frontend JavaScript normalizes these labels and applies the correct badge color automatically.

## Guest Name Prefill

If your site already provides a `[to_name]` shortcode, Simple RSVP Comments will use it to prefill the name field.

This is useful for invitation pages that personalize guest names through another theme, plugin, or invitation system.

## Styling

Frontend CSS:

```text
assets/simple-rsvp-comments.css
```

Frontend JavaScript:

```text
assets/simple-rsvp-comments.js
```

The visual direction is inspired by cozy pixel-art interfaces:

- Pixel-style form controls.
- Decorative clipped buttons.
- Warm cream, brown, gold, green, teal, and berry tones.
- Compact cards for easy reading.
- Randomized avatar frame colors.
- Attendance badges with distinct color states.

The stylesheet references `"Press Start 2P"` and `"Merchant Copy"`. Load those fonts from your theme or builder for the intended look. If they are unavailable, the browser will use fallback fonts.

## Security

The plugin uses standard WordPress safeguards:

- Nonce validation for AJAX requests.
- Sanitized text fields and textarea content.
- Attendance value validation.
- Capability checks before saving admin meta box data.

## Project Structure

```text
simple-rsvp-comments/
|-- README.md
|-- simple-rsvp-comments.php
`-- assets/
    |-- simple-rsvp-comments.css
    `-- simple-rsvp-comments.js
```

## Requirements

- WordPress.
- PHP supported by your WordPress installation.
- WordPress bundled jQuery.

## Roadmap Ideas

- CSV export for RSVP entries.
- Configurable attendance labels.
- Shortcode attributes for field labels.
- Optional moderation before messages appear publicly.
- Settings page for styling and behavior.

## License

License not specified yet.
