# Africa Nazarene University — TVET Institute Website

A full-function, database-backed website for the ANU TVET (Technical & Vocational
Education and Training) Institute, built to sit alongside the domains you
referenced for reference (`tvet.aiu.ac.ke`, `tvet.kabarak.ac.ke`, `spu.ac.ke`,
`daystar.ac.ke`) — with the **ANU red / gold / black / white branding and the
utility top navbar (main site · eNaz Portal · Student Portal) retained** from
your original prototype.

It is a self-contained PHP + MySQL application: no external "main site" CMS is
required — it ships with its own database, its own admin panel, and its own
file uploads, so it can be deployed as its own subdomain (e.g.
`tvet.anu.ac.ke`) or in a sub-folder of the main site.

---

## ✨ What's included

**Public site**
- Home, About, Programmes (with live search/filter by department & level),
  individual Programme pages, Admissions, **Apply Online** (writes to the
  database), Board & Trainers, News, single News articles, Events, Gallery,
  Downloads (prospectus/forms), FAQs, Contact (writes to the database)
- Utility topbar retained: link back to the ANU main site, eNaz Portal,
  Student Portal, quick-dial phone
- WhatsApp floating button, accreditation/partners strip (TVETA, TVET-CDACC,
  KNEC, KASNEB, NITA), homepage stats counters, testimonials, footer links to
  other ANU Schools
- Fully responsive (Bootstrap 5), accessible semantic markup, SEO meta tags

**Admin backend** (`/admin`)
- Secure login (bcrypt password hashing, CSRF protection on every form)
- Role-based accounts: Super Admin / Registrar / Editor
- Dashboard with live counts and shortcuts
- Full CRUD for: Programmes, Departments, Board & Trainers, News, Events,
  Homepage Sliders, Gallery, Testimonials, Downloads, FAQs, Partners
- Online **Applications** manager (view, change status, add internal notes)
- **Contact Messages** inbox (read / replied tracking)
- **Site Settings** editor — topbar/portal URLs, phone numbers, email,
  address, social links, mission/vision, homepage stats — all editable
  without touching code
- Image/file uploads with type & size validation

All content on the public site is pulled live from the database — there is
**no hard-coded text** for programmes, staff, news, events, settings, etc.

---

## 🧱 Requirements

- PHP 8.0+ with the `pdo_mysql` and `mbstring` extensions (both are enabled
  by default on almost all shared hosts and control panels such as cPanel)
- MySQL 5.7+ / MariaDB 10.4+
- Apache (with `mod_rewrite`/`mod_alias`) or Nginx

---

## 🚀 Setup

1. **Upload the files** to your web root (or a sub-folder / subdomain
   document root, e.g. `tvet.anu.ac.ke` pointed at this folder).

2. **Create the database.** Import `database/schema.sql` — it creates the
   `anu_tvet` database, every table, and realistic seed data (24 sample
   programmes across 5 departments modelled on real TVETA/CDACC programme
   levels, sample staff, news, events, FAQs, testimonials and partners) so
   the site is fully populated out of the box:

   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. **Configure the database connection** in `config/config.php`:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'anu_tvet');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   ```

   If the site is deployed in a sub-folder rather than a domain root, also
   set `BASE_URL`, e.g. `define('BASE_URL', '/tvet');`.

4. **Make `uploads/` writable** by the web server:

   ```bash
   chmod -R 755 uploads
   ```

5. **Log in to the admin panel** at `/admin/login.php`:

   - Username: `admin`
   - Password: `TvetAnu#2026`

   **Change this password immediately** — go to *Admin Users → edit your
   account → set a new password*, or create a new Super Admin account and
   deactivate/delete the default one.

6. Go through **Site Settings** in the admin panel and update the topbar
   links (main site, eNaz Portal, Student Portal), phone numbers, email,
   address, social links and homepage stats to the real values.

That's it — the site is live and fully editable from the admin panel.

---

## 🎨 Branding

The palette from your original prototype is preserved exactly:

| Colour | Hex | Use |
|---|---|---|
| Red | `#FF0000` | CTAs, active nav, accents |
| Gold | `#FAC823` | Section dividers, level badges, highlights |
| Black | `#000000` | Headings, footer, admin sidebar |
| White | `#FFFFFF` | Content surfaces |

All colours are CSS custom properties at the top of `assets/css/style.css`
(and `admin/assets/css/admin.css`) — change them in one place to re-theme
the whole site.

---

## 🗂️ Project structure

```
tvet-institute/
├── config/              # DB + app configuration
├── includes/            # shared header/footer/functions (public site)
├── assets/              # public CSS/JS/images
├── uploads/              # user-uploaded images/files (writable)
├── admin/                # admin backend
│   ├── includes/         # auth, layout, generic CRUD config
│   ├── assets/            # admin CSS/JS
│   ├── crud.php           # generic list/create/edit/delete engine
│   ├── programmes.php     # dedicated Programmes management
│   └── ...
├── database/
│   └── schema.sql        # full schema + seed data
├── index.php, about.php, programmes.php, ...   # public pages
└── README.md
```

### How the generic CRUD engine works
Most simple content types (News, Events, Staff, Gallery, Testimonials,
Downloads, FAQs, Partners, Homepage Sliders, Departments) are driven by a
single configuration file, `admin/includes/crud-config.php`, and rendered by
`admin/crud.php`. To add a brand-new content type, add one array entry to
that config file — no new PHP page required. Programmes and Applications
have their own dedicated pages because of their extra relational logic
(department dropdown, status workflow).

---

## 🔒 Security notes

- All forms are protected with CSRF tokens.
- Passwords are hashed with PHP's `password_hash()` (bcrypt).
- All output is escaped with `htmlspecialchars()`; all queries use PDO
  prepared statements.
- Uploaded files are renamed on save and restricted by extension; the
  `uploads/` folder has PHP execution disabled via `.htaccess`.
- Set `display_errors` off in production — `config/config.php` already does
  this automatically when the `APP_ENV=production` environment variable is
  set on your server.

---

## 🌍 Kenyan & international alignment

Programme levels (Artisan Certificate/Level 4, Craft Certificate/Level 5,
Diploma/Level 6) and examining bodies (TVET-CDACC, KNEC, KASNEB, NITA) match
the Kenya National Qualifications Framework and the structure used by peer
institutions such as Kabarak University TVET Institute and St. Paul's
University TVET Institute — both registered under the TVET Act, 2013 and
CDACC-examined. The Admissions page also includes a dedicated note for
international/EAC applicants regarding credential recognition, in line with
UNESCO's ISCED Level 4–6 short-cycle/vocational classification used
internationally to benchmark TVET qualifications.

---

## 🧩 Extending further

- Swap the plain-text News/Programme body fields for a rich text editor
  (e.g. TinyMCE or Quill) by adding the script tag in
  `admin/includes/admin-header.php` and initialising it on the relevant
  `<textarea>`.
- Wire up transactional email (e.g. via PHPMailer + SMTP) in `apply.php` and
  `contact.php` to notify Admissions/Registrar automatically on submission.
- Add M-Pesa/PesaLink payment integration for application fees.
