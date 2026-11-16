# Africa Nazarene University — Website Revamp Prototype

A working PHP + MySQL + Bootstrap 5 prototype for a redesigned ANU website
with a custom, database-driven admin panel. Every piece of content that appears on
the public site (news, events, schools, programmes, settings, hero banner)
is editable from `/admin` — no code changes needed to publish new content.

## What was reviewed before building this

- `anu.ac.ke` (current site) — WordPress + Elementor, generic default theme
  styling, 20+ item mega-menu, no visible content hierarchy, mixed date
  formats, several placeholder/blank "Life @ ANU" tiles, dense unfiltered
  programme lists.
- `spu.ac.ke` (St Paul's University) and `aiu.ac.ke` (Africa International
  University) — reviewed as regional references for navigation depth and
  page structure (schools, portals, admissions flow).

## What's wrong with the current ANU site (my assessment)

1. **No visual identity.** It uses stock WordPress/Elementor defaults —
   nothing about the layout, color, or type says "ANU" specifically, or
   evokes the actual campus (savannah, Nairobi National Park).
2. **Menu overload.** The main nav + mega-menu expose ~90 links at once.
   Prospective students can't find "Apply," "Programmes," or "Fees" quickly.
3. **No content structure for staff.** Updates go through a general-purpose
   WordPress install with Elementor page-builder markup — slow to edit,
   easy to break, no clear separation between "what changes often" (news,
   events) and "what's static" (About, Governance).
4. **Duplicate/broken info.** Two different primary phone numbers shown in
   different places on the same page; the M-PESA/tuition guidance is buried.
5. **Weak calls to action.** "Apply Now" competes visually with a dozen
   other links instead of standing out.

## Design direction taken here

Full rationale in `assets/css/style.css` comments, summarized:

- **Palette**: official Nazarene brand colors only — red (`#FF0000`), gold
  (`#FAC823`), white (`#FFFFFF`) and black (`#000000`). No other hues were
  introduced; soft panel backgrounds and muted text use opacity variants of
  these four colors rather than new colors. Red carries links/accents, gold
  is reserved for the primary "Apply Now" action and tags, black anchors
  the nav/footer/hero, white is the page background.
- **Type**: Fraunces (serif, headlines) for academic gravitas + Work Sans
  (sans, body/UI) for legibility — two clearly distinct families.
- **Layout**: asymmetric hero, hairline-divided news/school lists instead of
  identical rounded SaaS cards everywhere, numbered school list (justified
  because there genuinely are 5 sequential schools), one CTA color reserved
  for "Apply Now" so it's never competing with anything else.
- **Navigation**: collapsed to 8 top-level items (Home, About, Schools,
  Programmes, Admissions, News, Events, Contact) with "Apply Now" as a
  separate, permanently-visible button — not squeezed into the menu.

This is a starting prototype, not a pixel-final design — treat the CSS
tokens in `:root` as the place to adjust before rollout.

## Tech stack

- **PHP 8.1+** (no framework — plain PHP with PDO, easy for any developer
  to maintain without extra tooling)
- **MySQL 8 / MariaDB 10.4+**
- **Bootstrap 5.3** (via CDN) for grid + components, layered with custom
  CSS tokens in `assets/css/style.css`
- **Vanilla JS** for the couple of interactive bits (nav shadow-on-scroll)

## Folder structure

```
anuweb/
├── config/              # DB credentials + app config (NOT web-accessible)
│   ├── config.php
│   └── db.php
├── includes/            # shared PHP partials (NOT web-accessible directly)
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   └── images/          # add anu-logo.png, anu-logo-white.png, campus-savannah.jpg
├── database/schema.sql  # full schema + seed data taken from the live site
├── admin/                # admin panel back office (login required)
│   ├── login.php / logout.php
│   ├── dashboard.php
│   ├── news.php / news_form.php / news_delete.php
│   ├── events.php / events_form.php / events_delete.php
│   ├── schools.php / schools_form.php / schools_delete.php
│   ├── programmes.php / programmes_form.php / programmes_delete.php
│   ├── messages.php      # contact form inbox
│   ├── subscribers.php   # newsletter list + CSV export
│   └── settings.php      # site-wide key/value settings (phones, emails, mission…)
├── index.php, about.php, schools.php, school-single.php,
│   programs.php, program-single.php, news.php, news-single.php,
│   events.php, admissions.php, contact.php, subscribe.php, privacy.php
└── README.md
```

## Local setup (XAMPP)

This build is pre-configured to run at **http://localhost/anuweb/** with
zero code edits.

1. **Place the folder.** Unzip and copy the whole `anuweb` folder into your
   XAMPP `htdocs` directory:
   - Windows: `C:\xampp\htdocs\anuweb`
   - Mac: `/Applications/XAMPP/htdocs/anuweb`

   The final path should be `htdocs/anuweb/index.php`.

2. **Start Apache and MySQL** from the XAMPP Control Panel.

3. **Import the database.** Go to `http://localhost/phpmyadmin`, click
   Import, choose `anuweb/database/schema.sql`, click Go. This creates the
   `anu_cms` database, tables, and seed content (schools, programmes,
   sample news) copied directly from the live ANU site.

4. **Database credentials are already set for XAMPP** in
   `config/config.php` (`root` / empty password — XAMPP's default). No
   change needed unless you've customized your MySQL root password.

5. **BASE_URL is already set to `/anuweb`** in `config/config.php` to match
   this folder name. If you rename the folder or set up a virtual host at
   your domain root instead, update that one line:
   ```php
   define('BASE_URL', '/anuweb'); // change to '' for a vhost at domain root
   ```

6. **Add images** to `assets/images/`:
   - `anu-logo.png`, `anu-logo-white.png` (real ANU logo files)
   - `campus-savannah.jpg` (hero background — a real campus photo)

7. **Set the admin password.** The seed row has a placeholder hash. In the
   XAMPP Control Panel, click **Shell**, then run:
   ```bash
   php -r "echo password_hash('YourNewStrongPassword', PASSWORD_BCRYPT);"
   ```
   Copy the output, then in phpMyAdmin open `anu_cms` → `admin_users`,
   edit the one row, and paste it into `password_hash`.

8. **Visit the site:**
   - Public site: `http://localhost/anuweb/index.php`
   - admin panel login: `http://localhost/anuweb/admin/login.php` (username `admin`)

## Production checklist before going live

- [ ] Serve over HTTPS only; the session cookie is already set `secure`
      when `$_SERVER['HTTPS']` is present.
- [ ] Turn `display_errors` off in `config/config.php` and log errors
      to a file instead.
- [ ] Move DB credentials to environment variables, not a committed file.
- [ ] Add rate limiting / a CAPTCHA to `admin/login.php` and `contact.php`
      to slow down brute-force and spam.
- [ ] Add real image upload handling (not included in this prototype —
      `cover_image` / `image` columns currently expect filenames placed
      manually in `assets/uploads/`). A simple `admin/upload.php` using
      `move_uploaded_file()` with MIME-type and size checks is the next
      piece to add.
- [ ] Set up scheduled MySQL backups.
- [ ] Replace `privacy.php` placeholder text with ANU's approved policy.
- [ ] Populate Governance, History, Affiliations, and the remaining
      "Life @ ANU" sub-pages (Accommodation, Chaplaincy, Clubs, etc.) as
      additional admin panel-managed pages using the same pattern as `news`/`events`.

## What's intentionally out of scope for this prototype

To keep this reviewable, the following real features from anu.ac.ke were
**not** rebuilt yet, but the admin panel pattern here (a table + admin CRUD pages +
a public list/detail page) extends directly to each of them:

- Online application portal (ANU already has a separate system at
  `applications.anu.ac.ke` — this site links out to it rather than
  re-building it)
- Student/Faculty portal, e-learning, library OPAC (all separate existing
  systems — link out, don't rebuild)
- Multi-admin roles/permissions beyond the `role` column already in
  `admin_users`
- Search
- Multi-language support
