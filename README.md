# Automate Limited

Static marketing site. One HTML file, no build step, no dependencies to install.

---

## Before you push: three find-and-replace passes

Do these first. They take five minutes and they are much more annoying to fix
after Google has crawled the site.

### 1. Your domain (11 places)

Replace `yourdomain.com` with your real domain in:

| File | Occurrences |
|---|---|
| `index.html` | 9 (canonical, og:url, og:image, twitter:image, JSON-LD) |
| `robots.txt` | 1 |
| `sitemap.xml` | 1 |

### 2. Your contact details (`index.html`)

| Placeholder | Where it appears |
|---|---|
| `hello@yourdomain.com` | the Email us button, the footer, the JSON-LD |
| `+920000000000` | the tap-to-call link, the footer, the JSON-LD |
| `920000000000` | the WhatsApp link (`wa.me/...`, country code, no `+`) |
| `Lahore, Pakistan` | the footer and the JSON-LD address |

The WhatsApp number has no plus sign and no spaces. `+92 370 3536327`
becomes `923703536327`.

### 3. The invented content

**The portfolio pieces, the three case studies and the three reviews are not
real.** The design work is stock photography, the figures are made up, and
nobody said those quotes. Publishing them as they stand is fabricated proof,
which is a legal problem in both Pakistan and the UAE and is the sort of thing
a serious buyer checks.

Two options:

- **Replace them** with real projects, real numbers and real client wording.
  Get written permission before naming any client.
- **Hide them for now.** Open `index.html`, find `PLACEHOLDER SWITCH` near the
  end of the stylesheet, and uncomment the block underneath it. That hides the
  portfolio, the case studies, the reviews and the nav links to them. The rest
  of the site stands on its own without them.

There are also two video slots, both empty. The page falls back cleanly to its
photos until you fill them, so this is not blocking. `media/README.md` has the
shot list and the ffmpeg commands. The Odoo screen recording described in there
is the single highest-value thing you can add to this site, and it costs you
nothing but an hour.

The photos are also placeholders, though honest ones. See `IMAGE MANIFEST` at
the top of `index.html` for the twelve slots, their aspect ratios and how to
swap them.

---

## Push to GitHub

```bash
cd path/to/this/folder
git init
git add .
git commit -m "Automate Limited site"
git branch -M main
git remote add origin https://github.com/YOUR-USERNAME/YOUR-REPO.git
git push -u origin main
```

Every file must sit at the **repository root**, not inside a subfolder.
`index.html` has to be the first thing Hostinger sees.

---

## Connect Hostinger

1. hPanel → **Websites** → **Dashboard** next to your site
2. Sidebar → **Advanced** → **Git**
3. **Connect with GitHub**, authorise the Hostinger app, and give it access to
   this repository
4. Pick the repository, set the branch to `main`
5. Leave the directory as root unless you are deploying to a subdomain, in
   which case type the subfolder name
6. **Deploy**

Hostinger serves the repository exactly as it is. There is no build step, which
is why this site is a single file with no bundler.

After that, every push to `main` deploys automatically. The **Redeploy** button
on the Git page pulls on demand if you need it.

If you use the older SSH method instead of the GitHub connection, the target
directory has to be completely empty before the first pull.

---

## After it is live

- Open the site on your phone, not just your laptop
- Click **Email us**, **WhatsApp** and the phone number and confirm all three
  open correctly on a real device
- Run the URL through Google's Rich Results Test to confirm the FAQ markup is
  picked up
- Submit `sitemap.xml` in Google Search Console
- Share the URL in WhatsApp once and check the preview card looks right. If it
  does not, the domain in `og:image` is still wrong

---

## What is in here

| File | Purpose |
|---|---|
| `index.html` | The entire site. Styles, script and icons are inline. |
| `404.html` | Error page, wired up in `.htaccess` |
| `.htaccess` | HTTPS redirect, compression, caching, security headers |
| `robots.txt` | Crawler permissions, points at the sitemap |
| `sitemap.xml` | One URL. Add more if you add pages. |
| `og-automate.png` | Social share card, 1200x630 |
| `apple-touch-icon.png` | Home screen icon, 180x180 |
| `favicon.ico` | Fallback for browsers that do not take the inline SVG icon |
| `logo-automate*.svg` | Your logo, vector. Reversed version is for dark backgrounds. |
| `media/` | Two empty video slots. See `media/README.md` for what to record. |

---

## Editing later

Everything lives in `index.html`. Search for the section you want, edit the
text, commit, push. Hostinger picks it up within a minute or two.

Open `.htaccess` and pick **one** canonical host, www or non-www. Both are
commented out at the moment, which means both addresses currently work and your
SEO is split between them.
