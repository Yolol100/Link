=== Super Simple Internal Links ===
Contributors: webactueel
Tags: internal links, seo, links, content
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 8.0
Stable tag: 2.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Koppel keywords aan URL's en plaats automatisch veilige interne links in WordPress-content.

== Description ==

Super Simple Internal Links helpt beheerders om keywords aan interne of externe URL's te koppelen. De plugin gebruikt een HTML-aware autolinker die alleen tekstnodes aanpast en bestaande links, scripts, stijlen, codeblokken, knoppen en optioneel headings, quotes en lijsten overslaat.

== Features ==

* Keyword naar URL mapping.
* Veilige HTML-aware autolinking.
* Maximaal aantal links per pagina.
* Optionele nofollow en target blank attributen.
* CSV import en export.
* Rapportage en logs.
* Optionele WooCommerce, ACF, Elementor, widget en titel-integraties.

== Privacy ==

De plugin slaat keyword/URL mappings, instellingen en verwijderlogregels op in de WordPress database. Er worden standaard geen gegevens naar externe diensten verzonden.

== Uninstall ==

Data wordt alleen verwijderd wanneer de instelling "Verwijder plugindata bij uninstall" is ingeschakeld.

== Changelog ==

= 2.0.0 =
* Replaced string replacement with HTML-aware autolinking.
* Hardened admin actions with capabilities, nonces, sanitization and safe redirects.
* Improved CSV import/export validation and CSV injection mitigation.
* Added optional integration settings.
* Improved activation, migration and uninstall behavior.
* Added safer reporting pagination.

= 2.1.0 =
* Hardened keyword admin screen sanitization and fixed unsafe query handling.
* Replaced suggestion inline JavaScript submission with server-side admin-post handling.
* Added no-DOM fallback autolinking for hosts without the PHP DOM extension.
* Removed unused legacy plugin-data table creation.
* Improved protocol-relative URL rejection and priority bounds.
