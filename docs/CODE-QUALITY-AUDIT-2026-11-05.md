# Repository 5/37 — Super Simple Internal Links: codekwaliteitslog

Auditdatum: **5 november 2026**. Repository: `Yolol100/Link`. Vastgezette `main`-basis: `c8d391f5036ab7b7c3611d92057c9e340d49e0a7`. Conceptwijzigingen in [PR #5](https://github.com/Yolol100/Link/pull/5), branch `quality/csv-log-hygiene-20261105`. **Niet gemerged, niet gedeployed, geen klantdata gebruikt.** Bestaande onafhankelijke [PR #4](https://github.com/Yolol100/Link/pull/4) voor overlaprapporten is ongemoeid.

## Gecontroleerde bron en kwaliteit

- WordPress plugin 2.1.0, WP 6.0+, PHP 8.0+, automatische contentlinks, geblokkeerde HTML-contexten, ACF/WooCommerce-hooks, CSV-import/export, adminpagina's, instellingen en logs.
- Basisworkflow controleert PHP-syntaxis en pluginactivatie op geïsoleerde **WP 6.0/PHP 8.0** en **WP 7.1/PHP 8.3 + WooCommerce 11.1 + ACF 6.8.9**.
- Er zijn geen bestanden als 'dood' verwijderd. Hooks, optienamen, transities, verouderde aanroepers, CLI en uninstallpaden kunnen dynamisch bereikbaar zijn; geen verwijdering zonder contractbewijs.

## Bewezen probleem en verbetering

| ID | Prioriteit | Bevinding en vóór-bewijs | Wijziging en na-bewijs |
| --- | --- | --- | --- |
| LINK-001 | Middel / beveiliging | `csv_safe_cell()` beschermde `=1+2` maar liet `  \t=1+2`, voorloop-regeleinden en full-width operatoren onbeschermd. Echte WordPress/MySQL-regressie faalde op beide PHP-versies: [CI #41883846024](https://github.com/Yolol100/Link/actions/runs/41883846024). | Alleen de bestaande `includes/helpers.php`-eigenaar aangepast. Een uitgebreidere prefixcontrole houdt spreadsheetachtige celwaarden als tekst, voor het bestaande `fputcsv()`. [Groene eerste hertest #41884105421](https://github.com/Yolol100/Link/actions/runs/41884105421); na consolidatie moeten alle jobs opnieuw groen zijn. |
| LINK-002 | Laag / onderhoudbaarheid | `add_log()` en `save_log()` deden exact dezelfde `$wpdb->insert()` met dezelfde waarden. Verwijderen van `save_log()` zou een (mogelijk dynamische) publieke aanroeper breken. | `save_log()` blijft bestaan en delegeert naar `add_log()`. De bestaande twee interfaces worden via een echt SQL-roundtrip met testrecords getest; oorspronkelijke gedrag/kolommen/side effects behouden. |

## Vergelijking met publieke repositories / richtlijnen

1. [OWASP CSV Injection](https://owasp.org/www-community/attacks/CSV_Injection): meerdere spreadsheet-formuleprefixen inclusief whitespace, controls en full-width. Let op: geen enkele export-escape garandeert veiligheid na spreadsheet-bewerking en opnieuw opslaan.
2. [WordPress HTML API](https://developer.wordpress.org/reference/classes/wp_html_tag_processor/): vermijd oppervlakkige HTML-regexen die attributen met een letterlijke `>` kunnen breken. De huidige bestaande DOM-gebaseerde autolinker blijft behouden; de regex-fallback vereist aparte browser- en no-DOM-tests.
3. [WordPress make_clickable](https://developer.wordpress.org/reference/functions/make_clickable/): tekstlinking moet beschermde contexten en bestaande links herkennen. Geen ongeteste vervanging van de bestaande URL-/DOM-logica.
4. [10up WP Scaffold](https://github.com/10up/wp-scaffold): gebruik repo-native CI voor contracttests, zonder migratie naar een ander buildframework.

## Open punten — geen claim van perfectie

- **CSV-import/export-pariteit:** de oude importer verwijdert de door de exporter toegevoegde voorloopapostrof niet. Een export die `'=foo` opslaat kan bij import een gewijzigd keyword opleveren. Een wijziging vraagt expliciete compatibiliteits- en roundtriptests voor oorspronkelijk gequote keywords; bewust niet stil gerepareerd.
- **Rapportage:** `get_report_stats()` verwerkt per pagina maximaal 100 posts maar gebruikt globale totalen; statistieken kunnen daardoor niet representatief voor de gehele site zijn. PR #4 raakt dezelfde module; deze PR wijzigt het rapport niet om conflicten en ongewenste migratie te vermijden.
- **HTML-fallback:** `link_text_segments_fallback()` tokenizeert HTML via regex en is niet bewezen veilig voor `>` binnen geciteerde attributen. Geen blinde omschakeling naar de nieuwere WP HTML API omdat WP 6.0 expliciet ondersteund blijft; eerst contract- en DOM-pariteitstests.
- **Nog niet bewezen:** realtime site/Elementor/ACF-rendering, fallback zonder DOM-extensie, multisite, daadwerkelijke import uit spreadsheets na heropslaan, grote bibliotheken, mobiele/browser/accessibility QA, beveiligingsreview en definitieve installatie-ZIP.
- **Release:** `2.1.0` blijft ongewijzigd tot een afzonderlijk goedgekeurde versie-, changelog- en ZIP-pariteitsgate.

## Acceptatie, rollback en laatste bewijs

De voorgestelde wijzigingen mogen alleen verder na **groene GitHub Actions op de exacte PR-head**, onafhankelijke beoordeling en passende stagingacceptatie. Oude groene runs zijn geen sluitbewijs. Sluit PR #5 om alles terug te draaien; `main` en websites blijven onveranderd.
