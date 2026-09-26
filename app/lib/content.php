<?php
/* Marketing content for the public pages: services, Odoo modules and FAQs. */
declare(strict_types=1);

function site_services(): array
{
    static $s = null;
    return $s ??= require APP_DIR . '/content/services.php';
}

function site_service(string $slug): ?array
{
    return site_services()[$slug] ?? null;
}

/** Slug for a service's short key ('odoo', 'web', ...). */
function service_slug(string $key): ?string
{
    foreach (site_services() as $slug => $s) {
        if ($s['key'] === $key) {
            return $slug;
        }
    }
    return null;
}

function site_modules(): array
{
    static $m = null;
    return $m ??= require APP_DIR . '/content/modules.php';
}

function site_industries(): array
{
    return require APP_DIR . '/content/industries.php';
}

function site_projects(): array
{
    return require APP_DIR . '/content/projects.php';
}

function site_faq(): array
{
    static $f = null;
    return $f ??= require APP_DIR . '/content/faq.php';
}

/** FAQ entries tagged with a topic, as [question, answer] pairs. */
function faq_for(string $topic, int $limit = 4): array
{
    $out = [];
    foreach (site_faq() as $items) {
        foreach ($items as [$q, $a, $tags]) {
            if (in_array($topic, $tags, true)) {
                $out[] = [$q, $a];
            }
        }
    }
    return array_slice($out, 0, $limit);
}

function faq_jsonld(array $pairs): array
{
    return [
        '@context' => 'https://schema.org', '@type' => 'FAQPage',
        'mainEntity' => array_map(static fn ($p) => ['@type' => 'Question', 'name' => $p[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $p[1]]], $pairs),
    ];
}

function breadcrumb_jsonld(array $trail): array
{
    $items = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => abs_url('')]];
    $i = 2;
    foreach ($trail as $name => $path) {
        $items[] = ['@type' => 'ListItem', 'position' => $i++, 'name' => $name, 'item' => abs_url($path)];
    }
    return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
}

/** Link to the enquiry page with the service (and optionally an Odoo module) filled in. */
function enquiry_url(string $service = '', string $module = ''): string
{
    $q = array_filter(['service' => $service, 'module' => $module], static fn ($v) => $v !== '');
    return url('contact/') . ($q ? '?' . http_build_query($q, '', '&', PHP_QUERY_RFC3986) : '') . '#enquiry';
}
