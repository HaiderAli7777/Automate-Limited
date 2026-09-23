# Automate Limited

The marketing site for automateltd.com. Plain HTML, CSS and JavaScript, no build
step on the server, so Hostinger deploys it exactly as it sits in this repository.

## Deploying an update

1. Delete everything in your local repo folder **except `.git`**.
2. Copy the contents of this folder in. Turn on hidden files first
   (File Explorer: View, Show, Hidden items), or you will miss `.htaccess`
   and `.gitattributes`.
3. Commit and push:

   ```bash
   git add -A
   git commit -m "Site update"
   git push
   ```

`index.html` must sit at the top of the repository, not inside a folder. If
the site shows 403 after a deploy, that is almost always why.

If the old version still shows after deploying, purge the CDN cache in hPanel
and hard refresh with Ctrl+Shift+R.

## Before this is fully live

- **Create the mailbox `info@automateltd.com`** in hPanel (Emails, free email).
  Every contact link on the site points there.
- **WhatsApp and phone** are switched off until you choose a number. They are
  not shown at all rather than showing a placeholder.

## What is in here

| File | Purpose |
|---|---|
| `index.html` | The whole site, with styles, script and icons inline |
| `fonts/` | Outfit and IBM Plex Sans, self-hosted, with their OFL licences |
| `404.html` | Error page, wired up in `.htaccess` |
| `.htaccess` | HTTPS, www to bare domain, compression, caching, security headers |
| `robots.txt`, `sitemap.xml` | For search engines. Submit the sitemap in Search Console |
| `og-automate.png` | The card shown when the link is shared |
| `favicon.ico`, `apple-touch-icon.png` | Browser tab and home screen icons |
| `logo-automate*.svg` | Your logo as clean vectors, plus a reversed version for dark backgrounds |

## Photos

Nine Unsplash photos are in use. The list, with each slot's ratio and what it
should show, is in a comment near the top of `index.html` under IMAGE MANIFEST.
To swap one, find-and-replace its photo id.
