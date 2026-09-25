<?php
/* sitemap.xml (rewritten here by .htaccess): the public pages plus every live job posting. */
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');
$urls = [
    ['loc' => abs_url(''), 'lastmod' => date('Y-m-d', (int) @filemtime(__DIR__ . '/index.php'))],
    ['loc' => abs_url('careers/'), 'lastmod' => date('Y-m-d')],
    ['loc' => abs_url('privacy/'), 'lastmod' => date('Y-m-d', (int) @filemtime(__DIR__ . '/privacy/index.php'))],
];
if (app_installed()) {
    try {
        foreach (public_jobs() as $job) {
            $urls[] = ['loc' => abs_url('careers/' . $job['slug']), 'lastmod' => date('Y-m-d', ts($job['updated_at']) ?? time())];
        }
    } catch (Throwable $e) {
        // the static pages are still worth listing
    }
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo '  <url><loc>' . e($u['loc']) . '</loc><lastmod>' . e($u['lastmod']) . "</lastmod></url>\n";
}
echo "</urlset>\n";
