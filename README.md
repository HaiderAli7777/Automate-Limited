# Automate Limited

The website for automateltd.com, plus a private team area with:

- **Careers page** at `/careers/`, where people browse open roles and apply with their CV.
- **ATS** (applicant tracking): jobs, candidates, a drag-and-drop hiring pipeline, interview
  scheduling with calendar invitations, scorecards, offers and email templates.
- **CRM**: every message from the website contact form becomes a lead in a sales pipeline,
  with contacts, activity logging (calls, meetings, WhatsApp, email), follow-ups and deal values.
- **Dashboards**: an overview for everyone, an ATS dashboard and a CRM dashboard.

Plain PHP 8.1+ and MySQL, no build step and no Composer, so Hostinger runs it exactly as it
sits in this repository.

---

## First-time setup on Hostinger

Do these once, in this order.

1. **PHP version.** hPanel, Advanced, PHP Configuration: choose PHP 8.1 or newer (8.2 or 8.3 is
   fine). Make sure the `pdo_mysql`, `openssl` and `mbstring` extensions are on (they are by default).
2. **Database.** hPanel, Databases, MySQL Databases: create a database and a user, and note the
   database name, user and password. The host is `localhost`.
3. **Deploy the code.** Push this repository the way you already do (Git deployment in hPanel).
   `index.php` must sit at the top of `public_html`, not inside a folder.
4. **Run the installer.** Open `https://automateltd.com/install/` and fill in the database
   details, your name, email and a password. It creates the tables, your administrator account
   and a private config file, then locks itself.
5. **Mailbox and SMTP.** Create `info@automateltd.com` in hPanel, Emails, if it doesn't exist yet.
   Then sign in at `https://automateltd.com/admin/`, open **Settings**, choose *SMTP*, and enter
   `smtp.hostinger.com`, port `465`, SSL, the mailbox address and its password. Press
   **Send a test** to confirm. Without SMTP the site falls back to PHP `mail()`, which works but
   lands in spam more often.
6. **Invite your team** from **Team**, and post your first job from **Jobs**.

### Where private data lives

The installer puts the config file and uploaded CVs **one folder above** the website, in
`automate-private/` next to `public_html`. Git deployments never touch it and the web server
can't serve it. If the host doesn't allow that, it falls back to `app/storage/`, which
`.htaccess` blocks from the web.

Back up the database **and** the `automate-private` folder. Hostinger's account backups include
both; check hPanel, Files, Backups.

To reinstall from scratch, delete `automate-private/config.php` and open `/install/` again.

---

## Deploying an update

Merge into the branch Hostinger deploys from and push. Database changes, when there are any,
apply themselves the next time someone opens the team area.

If an old version still shows, purge the CDN cache in hPanel and hard refresh with
Ctrl+Shift+R.

---

## Using the team area

Sign in at `/admin/` (the footer's **Team login** link goes there too).

| Role | Can use |
|---|---|
| Administrator | Everything, including Team and Settings |
| Manager | Recruitment and CRM, without Team or Settings |
| Recruiter | Jobs, candidates, pipeline and interviews |
| Sales | Leads, contacts and the sales pipeline |
| Interviewer | Only the interviews they're on, the candidate's CV, and their own scorecards |

**Recruitment.** Create a job, add screening questions, and set it to *Open*: it appears on
`/careers/` straight away, with structured data so Google for Jobs can pick it up. Each
application lands in the first pipeline stage and the hiring inbox gets an email. Drag cards
across the **Hiring pipeline** board; moving someone to *Rejected* asks for a reason and can send
the "not moving forward" email. From an application you can email the candidate from a
template, schedule interviews (the candidate and panel get a calendar file), save an offer and
add follow-ups. Interviewers see other panelists' scorecards only after submitting their own.

**Sales.** Website enquiries arrive in **Leads** at the first stage, with the page they came
from and any campaign tags (`utm_source`, `utm_medium`, `utm_campaign`) in the link. Log calls,
meetings and WhatsApp messages, set the next follow-up, add a value, and drag the deal along the
**Sales pipeline**. Moving a deal to *Lost* asks why, and the CRM dashboard shows the reasons.

**Settings** holds the notification addresses, SMTP, the list of services on the enquiry form,
the pipeline stages for both boards (rename, recolour, reorder, add) and every email template.

---

## What is in here

| Path | Purpose |
|---|---|
| `index.php` | Homepage |
| `careers/`, `contact/`, `privacy/` | Careers pages, the enquiry form handler, the privacy notice |
| `admin/` | Front controller for the team area |
| `install/` | One-time installer (locks itself after use) |
| `app/` | All PHP code, views and the schema. Never served (`app/.htaccess`) |
| `app/admin/controllers/` | ATS, CRM, dashboards, settings |
| `app/views/site/`, `app/views/admin/` | Templates for the website and the team area |
| `app/schema.php` | Database tables as numbered migrations |
| `assets/` | Stylesheets, scripts and the icon sprite |
| `fonts/` | Outfit and IBM Plex Sans, self-hosted, with their OFL licences |
| `sitemap.php` | Served as `/sitemap.xml`, including live job postings |
| `.htaccess` | HTTPS, www to bare domain, pretty URLs, private folders, caching, security headers |
| `og-automate.png`, `favicon.ico`, `apple-touch-icon.png`, `logo-automate*.svg` | Share card, icons, logos |

### Security

Passwords are hashed, sessions are HTTP-only and rotate on sign-in, every form in the team area
has CSRF protection, and sign-in, the contact form and applications are rate-limited. Public
forms also use a hidden spam trap and a signed timestamp. Uploaded files are checked by type and
content, renamed, stored privately and only served to signed-in users. The SMTP password is
stored encrypted. The team area sends `noindex` and a strict Content Security Policy.

---

## Photos

Unsplash photos, loaded from Unsplash's CDN. To swap one, find-and-replace its id with your own
Unsplash id or file path. Each photo carries `&sat=-12&con=5` so a mixed set reads as one shoot;
your own photography won't need it.

| Slot | File | Ratio | Id | Should show |
|---|---|---|---|---|
| Hero panel | `index.php` | 16:9 | `photo-1541535881962-3bb380b08458` | A business owner, calm, city or office |
| Monday: counter | `index.php` | 1:1 | `photo-1787209516537-a8e66a1b4360` | A sale on a point of sale terminal |
| Monday: month end | `index.php` | 16:10 | `photo-1600880292203-757bb62b4baf` | People reviewing figures together |
| Monday: shelf | `index.php` | 16:10 | `photo-1604719312566-8912e9227c6a` | A stocked shop or supermarket |
| Services: web | `index.php` | 16:10 | `photo-1636247497842-81ee9c80f9df` | Website development |
| Services: SEO | `index.php` | 16:10 | `photo-1520333789090-1afc82db536a` | SEO |
| Services: marketing | `index.php` | 16:10 | `photo-1762525984874-83d6ddf6a069` | Digital marketing |
| Services: design | `index.php` | 16:10 | `photo-1636247499180-13285c86be9b` | Graphic design |
| Services: custom | `index.php` | 16:10 | `photo-1630524274689-2950ac0fc91e` | Custom solutions |
| Homepage careers | `app/views/site/home-careers.php` | 4:5 | `photo-1603195827187-459ab02554a0` | Your team at work |
| Careers hero | `app/views/site/careers-list.php` | 5:4 | `photo-1758691737083-0e7fdbde0f05` | Your team at work |
| Careers: the work | `app/views/site/careers-list.php` | 4:5 | `photo-1580894899378-92e56886cd4d` | Someone at their desk |

Photos of your own team will do more for the careers page than any stock photo.

---

## Working on it locally

You need PHP 8.1+ and MySQL or MariaDB.

```bash
php -S localhost:8080 -t . tools/dev-router.php
```

Then open `http://localhost:8080/install/`. The router mirrors the `.htaccess` rewrites for
PHP's built-in server; Hostinger never uses it. Set `'debug' => true` in the private config
file to see errors in the browser.
