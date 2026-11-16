# Africa Nazarene University — Website Network

This is a **network of sites sharing one database**: the main ANU site plus
**fourteen** custom-designed unit sites — every school, TVET (labeled "Non
Degree" in the nav), SEPC, ANU Online, Institute of Research, Admissions,
Student Life, Chaplaincy, Library, and Alumni.

```
anuweb-network/
├── main-site/              The hub: home (hero carousel), about, governance,
│                            schools nav, resources library, news, events,
│                            contact, and the full admin panel (incl. admin
│                            users and hero-image management).
├── law-school-site/         School of Law — black+gold "chambers" design.
├── business-school-site/    School of Business — white+red "growth" design.
├── scitech-school-site/     School of Science & Technology — black+gold
│                            "circuit grid" design with a monospace accent.
├── humanities-school-site/  School of Humanities & Social Sciences — warm
│                            cream "editorial/storytelling" design.
├── religion-school-site/    School of Religion & Christian Ministry —
│                            reverent black/gold contemplative design.
├── tvet-site/                Non Degree (TVET) — black+white "workshop"
│                            design with a gold/black hazard-stripe motif.
├── sepc-site/                 Short Courses & Professional Studies — clean
│                            white/black professional course-catalog design.
├── anu-online-site/           ANU Online — a real, independently-built
│                            application (imported, not authored from
│                            scratch), with its own separate database and
│                            admin panel. See "ANU Online" section below.
├── research-site/            Institute of Research — restrained "academic
│                            journal" design: white, hairline gold rules.
├── admissions-site/          Admissions — bold black/red conversion-focused
│                            design with a persistent sticky Apply bar.
├── student-life-site/        Student Life — vibrant rounded-card "campus
│                            life" design, with a dropdown to sub-sections.
├── chaplaincy-site/           Chaplaincy — warm cream "candle-glow"
│                            pastoral design.
├── library-site/              Library — "reading room" design using the
│                            same bookshelf motif as the Resources Library.
├── alumni-site/                Alumni — "legacy gold" celebratory design,
│                            gold-dominant with ribbon-corner cards.
├── httpd-vhosts-sample.conf   Apache virtual host config (optional, see below)
└── README.md                (this file)
```

> **Fresh install? Just import `main-site/database/install_all.sql`.** It
> now automatically drops and recreates the `anu_cms` database before
> building everything, so "table already exists" errors are no longer
> possible — you never need to manually drop the database first. It
> combines `schema.sql` through `schema_phase9.sql` (schools, programmes,
> news, events, staff, research, resources, announcements, governance,
> partners, and all site settings) into one file, in order, and seeds a
> real working admin login (see below).
> ⚠️ Re-importing it wipes anything currently in `anu_cms`, including admin panel
> content you've already added — back up first if you need to keep it.

## Why subdomains + a shared database

You chose subdomains (`law.anuweb.ac.ke` instead of `anuweb.ac.ke/schools/law/`)
with fully custom per-school design. That means each unit is genuinely its
own website — own folder, own HTML/CSS, own look — but **all of them read
and write the same MySQL database** (`anu_cms`) as the main site. Content
entered once in the shared admin panel (`main-site/admin`) — programmes,
schools, faculty/staff, settings — shows up correctly on every unit site
that references it. This is what keeps 9 different-looking websites
manageable from one backend instead of nine separate ones.

## Local setup on XAMPP (no vhost required)

The project now works straight out of the box the way you're already
running it — the whole `anuweb-network` folder dropped into `htdocs` and
browsed by folder path. No hosts file or Apache virtual host editing is
needed for local testing.

1. **Unzip** into `htdocs`, e.g. `C:\xampp\htdocs\anuweb-network\`
   (Mac: `/Applications/XAMPP/htdocs/anuweb-network/`).

2. **Start Apache and MySQL** from the XAMPP Control Panel.

3. **Import the database.** In phpMyAdmin, click **Import**, choose
   `main-site/database/install_all.sql`, click **Go**. This one file
   drops and recreates `anu_cms` from scratch, then creates everything —
   schools, programmes, news, events, staff, research, resources,
   announcements, governance, and all settings. Safe to re-run any time
   you want a clean reset (it always wipes and rebuilds).

4. **Set the admin password.** The seed row has a placeholder hash. In the
   XAMPP Control Panel, click **Shell**, then run:
   ```bash
   php -r "echo password_hash('YourNewStrongPassword', PASSWORD_BCRYPT);"
   ```
   Copy the output, then in phpMyAdmin open `anu_cms` → `admin_users`,
   edit the one row, and paste it into `password_hash`.

   **Add images.** Every site already ships with the ANU logo in its own
   `assets/images/anu-logo.png` — nothing to do there. Just add
   `campus-savannah.jpg` to `main-site/assets/images/` for the homepage
   hero.

5. **Visit:**
   - Main site: `http://localhost/anuweb-network/main-site/index.php`
   - **Admin panel (the backend)**: `http://localhost/anuweb-network/main-site/admin/login.php`
     — login: `admin` / `AnuAdmin#2026`. There's no public link to this on
     the site by design; bookmark this URL. Once in, a Super Admin can
     create more admin accounts (Admin Users in the sidebar) with Editor
     or Marketing roles for narrower access.
   - School of Law: `http://localhost/anuweb-network/law-school-site/index.php`
   - School of Business: `http://localhost/anuweb-network/business-school-site/index.php`
   - School of Science & Technology: `http://localhost/anuweb-network/scitech-school-site/index.php`
   - School of Humanities & Social Sciences: `http://localhost/anuweb-network/humanities-school-site/index.php`
   - School of Religion & Christian Ministry: `http://localhost/anuweb-network/religion-school-site/index.php`
   - Non Degree (TVET): `http://localhost/anuweb-network/tvet-site/index.php`
   - Short Courses & Professional Studies (SEPC): `http://localhost/anuweb-network/sepc-site/index.php`
   - Institute of Research: `http://localhost/anuweb-network/research-site/index.php`
   - Admissions: `http://localhost/anuweb-network/admissions-site/index.php`
   - Student Life: `http://localhost/anuweb-network/student-life-site/index.php`
   - Chaplaincy: `http://localhost/anuweb-network/chaplaincy-site/index.php`
   - ANU Online: `http://localhost/anuweb-network/anu-online-site/index.php`
   - Library: `http://localhost/anuweb-network/library-site/index.php`
   - Alumni: `http://localhost/anuweb-network/alumni-site/index.php`

   The main site's navbar links directly to each of these (TVET, ANU
   Online, SEPC, Admissions and Research are top-level nav items; the five
   academic Schools are in the "Schools" dropdown) — plus there's an
   "Explore Our Network" section right on the homepage with one-click
   tiles to every site, handy for presenting.

## Moving to real subdomains later (production)

The subdomain architecture you originally asked for still works — it's
just not required for local testing anymore. When you're ready to deploy
to real domains (`law.anu.ac.ke`, etc.), or want the vhost-based local
setup for a more production-like feel:

1. Change `BASE_URL` back to `''` in each site's `config/config.php`
   (and `MAIN_SITE_URL` in each unit site to the real main-site domain).
2. Update the `url_school_*`, `url_tvet`, `url_sepc` settings in the
   database to the real domain URLs.
3. Use `httpd-vhosts-sample.conf` (still included below) and the hosts
   file instructions if you want that setup locally too.

## What's centrally managed vs. what's custom per site

| Managed centrally (main-site/admin) | Custom per unit site |
|---|---|
| Schools, programmes, news, events | HTML/CSS layout and visual design |
| Faculty & staff directory (`staff` table, filtered by unit) | Page structure and copy |
| Site settings (phones, emails, subdomain URLs, mission text) | Imagery and tone |
| Resources library, research publications | — |
| Popup announcements (shown site-wide) | — |
| Contact messages (tagged by which site they came from) | — |

When you build the next unit site, copy `law-school-site/` as a starting
skeleton: swap `config/config.php`'s `UNIT_SLUG` and `SCHOOL_SLUG`
constants, then redesign `assets/css/style.css` and the page templates to
give that school its own distinct look — same four brand colors, different
composition, the way School of Law uses black+gold-dominant "chambers"
styling versus the main site's white+red.

## ANU Online — special setup (different from every other site)

Unlike the other 12 unit sites, ANU Online is a **real, complete, independently-built
application** — not something built from scratch to share the main `anu_cms`
database. It has its own database, its own admin panel, its own API, and a
much richer feature set (news, events, RSVPs, testimonials, clubs, a
support/tutorial resource center, live event streaming, prayer requests,
analytics/reports, and more). It was imported as-is rather than rewritten,
so its internal code doesn't follow the same conventions as the rest of
this network (e.g. it uses inline styles throughout) — that's intentional;
it's someone else's complete working system, not new code built under this
project's conventions.

**Setup (separate from the main `install_all.sql`):**
1. Import `anu-online-site/database.sql` into MySQL — this creates its own
   `anu_online` database (separate from `anu_cms`).
2. `anu-online-site/includes/config.php` is already set to `root` / no
   password / port 3306 to match the rest of this network's XAMPP setup,
   and its `SITE_URL` is already updated to `/anuweb-network/anu-online-site`.
3. **Admin login**: `http://localhost/anuweb-network/anu-online-site/admin/login.php`
   — the site's own README claims `admin` / `password` works out of the
   box, but the seeded password hash in `database.sql` doesn't actually
   verify against that password (checked directly, it fails). Use the
   included `reset_admin.php` in the site's root to set a real working
   password after importing the database, then delete that file (it says
   so itself — it's a one-time recovery tool, not meant to stay live).
4. It links back to the main network via a "Africa Nazarene University"
   link in its own topbar and footer, and the main site's navbar/utility
   bar link to it via `settings.url_anu_online` (already pointed at
   `/anuweb-network/anu-online-site/index.php`).
5. Its brand colors already match this network's exactly — `#ff0000` red
   and `#fac823` gold, confirmed directly in its own CSS — so no color
   changes were needed there.
6. I fixed two real pre-existing bugs in the imported code while
   integrating it: unescaped quotes in the WhatsApp button's PHP that
   caused a syntax error, and ~46 files' hardcoded `/anu/` paths (fixed
   to point at `/anuweb-network/anu-online-site/` throughout).

## Latest round of fixes

- **Apply Now, network-wide, now points at the real portal**:
  `https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp` — fixed
  in the shared `apply_url` setting (covers all 12 built-from-scratch
  sites automatically) and separately in ANU Online's own database and
  five hardcoded links that bypassed its settings system.
- **Hero images no longer get cropped oddly** — `object-position: center
  top` added everywhere, so uploaded photos keep their tops (where faces
  usually are) instead of being center-cropped.
- **Fonts switched to Roboto** (with Helvetica Neue/Arial/system
  fallbacks) across all 12 built sites and the admin panel, replacing the
  Fraunces serif — left ANU Online's own fonts untouched since that's
  imported, not authored here.
- **Logos enlarged** across every site's nav.
- **Vacancies** — new real feature: `vacancies` table, full admin CRUD,
  public listing + detail pages, linked from the utility bar. Grounded in
  the fact that anu.ac.ke has a real `/vacancies/` page.
- **Alumni page** added with real content (Alumni Scholarship Fund,
  regional chapters, Alumni Card) grounded in anu.ac.ke's actual `/alumni/`
  section — not yet its own full site, just a page for now.
- **ANU Foundation** linked in the utility bar (to the real anu.ac.ke
  page — confirmed it exists, haven't built a dedicated site for it).
- **VC message** moved to its own page (`vc-message.php`) instead of a
  section on the About page.
- **DVC entries added** to Governance/Management — Prof. Simon Obwatho
  (Academic & Student Affairs), Dr. Rhonda Slingerland (Finance), Prof.
  Bob Brower (Strategy) — real names found on anu.ac.ke's own pages, but
  flagged in the data itself since different cached pages show some
  as "Interim"/"Acting" — worth reconfirming current titles before
  publishing.
- **Utility bar reordered**: eNaz, Student Portal, Resources, SEPC,
  Online, Vacancies, Alumni, ANU Foundation, Contact Us, Donate.
- Swept the whole network for leftover "TVET" text and changed it to
  "Non-Degree" (beyond the TVET site itself, which was already done in
  an earlier round) — except "TVETA" (the real regulatory authority) and
  internal code identifiers, which are correctly left alone.

## Still to build (next passes)

- Research grants and a searchable researcher directory (publications are
  live now; grants and a directory are the natural next addition to the
  Institute of Research site).
- A real admin login/branding pass so the admin panel itself carries ANU visual
  identity, not just the public sites.
- The Student Life / Clubs / Accommodation / Sports / Counseling / Career
  Services / Chaplaincy Ministries pages are structural placeholders (real
  section headings and layout, but no invented club names or specific
  programme details) — the relevant offices need to populate real content
  through a future admin panel extension for those sites, or directly in the files
  for now.
- Admin role permissions could go finer-grained still — right now it's
  three tiers (Super Admin / Editor / Marketing) enforced on every admin
  page, but within "Editor" there's no way to restrict someone to, say,
  only News and not Programmes.
- **Full-screen event popups already exist** — this isn't new, but it's
  easy to miss: the "Popup Announcements" section in the admin panel
  (Marketing/Super Admin) is exactly the "advertise an event full-screen
  on the main page" feature. Create an announcement, mark it Active, and
  it shows as a full-screen overlay to visitors on the main site until
  dismissed. No separate build needed for this.

## Admin roles (now enforced everywhere)

- **Super Admin** — full access, including Site Settings and managing
  other admin accounts (Admin Users, in the sidebar — only Super Admins
  see or can reach this).
- **Editor** — News, Events, Schools, Programmes, Faculty & Staff,
  Governance, Research Publications.
- **Marketing** — Announcements (the popup), Resources Library, Messages,
  Subscribers, Partners.

Every admin page checks the logged-in admin's role server-side (not just
hiding sidebar links) — an Editor account hitting Settings directly by URL
gets a 403, not the settings form.
