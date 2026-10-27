# NPG-SW-Lab-SBar

SBar is a small PHP web application for tracking serial numbers,
barcodes and assets in an Intel NPG software lab.

## What it does

- Records device under test (DUT) serial numbers.
- Tracks HVPIP entries.
- Generates barcodes.
- Signs users in through LDAP.

## Tech

- PHP with a small custom framework in `includes` (page factory,
  templates and database classes)
- MySQL, with partial SQLite support
- JavaScript and CSS

## Setup

Set the application name and database settings in `config.php`, then
serve the folder with PHP.

## License

Licensed under the Apache License 2.0. See [LICENSE](LICENSE).
