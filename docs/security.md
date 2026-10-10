---
title: Security
description: What Mason trusts, what it does not, and what bricks have to do themselves.
---

# Security

## The editor state comes from the browser

The field's state is entangled with the browser. Anyone who can edit a record can send any content for the field, not only what the editor's buttons produce: any brick id, any config, any attributes. Bricks therefore treat their config as untrusted input:

- Escape values in Blade with `{{ }}`. Output HTML from a rich editor or other free text only after sanitizing it, e.g. with `Str::sanitizeHtml()`.
- Check values that pick options (colours, sizes, layouts) against the allowed options instead of passing them on.
- Do not take markers or rendered HTML from the config. If a brick needs HTML that its caller computed, pass it as an object such as `HtmlString`, which cannot come from stored JSON, and ignore strings under that key.

`toUnsafeHtml()` outputs the bricks' HTML as it is. Use it only for bricks that sanitize their own output.

## Iframe context and data

The preview and entry render in an iframe fed by a separate request.

- The bricks and the layout travel in a signed render context. A client can replay it, but cannot change it, so it cannot make the server render classes or views it was not given.
- The entry's render data (`MasonEntry::data()`) is encrypted, models are reduced to class and key and resolved again on the server.
