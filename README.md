# Africa Nazarene University Websites

Source code for the Africa Nazarene University (ANU) web platform.

## What is in this repository

| Folder | What it is |
| --- | --- |
| `anuweb-network/main-site` | The main university website and its admin panel |
| `anuweb-network/*-site` | The unit websites: admissions, alumni, anu-online, business-school, chaplaincy, humanities-school, law-school, library, religion-school, research, scitech-school, sepc, student-life |
| `anu` | ANU Online (distance learning) |
| `tvet-institute` | The Non-Degree / TVET institute website |

The unit websites share one database with the main site (`anu_cms`). TVET (`anu_tvet`) and ANU Online (`anu_online`) each have their own.

## Built with

- PHP 8.5 (plain PHP with PDO)
- MariaDB
- Apache with `mod_rewrite` (clean URLs, no `.php` in addresses)
- Bootstrap 5 and Bootstrap Icons

## Main features

- Per-site admin panels with their own sign-in
- Popup announcements that each site manages for itself
- Cookie consent with HubSpot chat, loaded only after the visitor accepts
- Accessibility menu (text size, contrast, reading guide, read aloud and more)
- Campus Tour page with a clickable list of places, using the ANU campus video
- Privacy, data protection and terms pages on every site

## What is not included

For security, these are not stored in the repository:

- Database settings and passwords (`config/config.php`, `config/db.php`, `config/mail.php`)
- Uploaded files (`uploads/` folders) and logs
- Backups and setup scripts that hold credentials

To run a site you need to create its `config` files with your own database host, name, user and password.

## Credits

Designed and developed by Lennox Caleb, with editorial by Molly Otieno.

## License

Copyright (c) 2026 Africa Nazarene University. All rights reserved. See [LICENSE](LICENSE).
