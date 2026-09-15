# Super Simple Internal Links

Super Simple Internal Links (SSIL) is a WordPress plugin for mapping keywords to URLs and automatically adding controlled internal or external links to WordPress content.

The current runtime uses an HTML-aware linker instead of plain string replacement, so existing links and sensitive markup are not blindly rewritten.

## Features

- Keyword-to-URL mappings.
- HTML-aware automatic linking in text nodes.
- Configurable maximum number of links per page.
- Optional `nofollow` and `target="_blank"` attributes.
- CSV import and export.
- Link reports and logs.
- Optional WooCommerce, ACF, Elementor, widget and title integrations.
- Safe fallback behaviour on hosts where the PHP DOM extension is unavailable.

By default the linker skips existing links, scripts, styles, code blocks and buttons. Additional content such as headings, quotes and lists can be excluded through the plugin settings.

## Requirements

- WordPress 6.0 or newer.
- PHP 8.0 or newer.

The current release is `2.1.0`. The plugin metadata and `readme.txt` are the canonical source for the current compatibility matrix.

## Installation

1. Upload the plugin folder or ZIP through the WordPress Plugins screen.
2. Activate **Super Simple Internal Links**.
3. Open the SSIL administration screen.
4. Add keyword/URL mappings and configure the linking rules that should apply to the site.

For a manual installation, place the plugin folder under `wp-content/plugins/` and activate it from WordPress.

## Safe linking model

SSIL modifies rendered content conservatively:

- existing links are not nested or replaced;
- script, style and code content is skipped;
- URLs and settings are sanitized before use;
- privileged admin actions use WordPress capability and nonce checks;
- CSV import/export includes validation and spreadsheet-injection protection.

Review automatic-linking settings on staging before applying broad rules to an established content library.

## Privacy and stored data

The plugin stores keyword/URL mappings, settings and removal-log information in the WordPress database. It does not send site data to an external service by default.

Plugin data is removed on uninstall only when **Verwijder plugindata bij uninstall** is explicitly enabled.

## Repository structure

- `super-simple-internal-links.php` — plugin bootstrap and metadata.
- `includes/` — linking, settings, logging and admin actions.
- `languages/` — translation files.
- `readme.txt` — WordPress distribution documentation and changelog.

## License

GPL-2.0-or-later, as declared by the plugin metadata and `readme.txt`.