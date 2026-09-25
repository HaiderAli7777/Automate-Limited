<?php
/**
 * Document head shared by every public page.
 * @var string $title @var string $description @var string $path  canonical path, e.g. "careers/"
 * @var array|null $jsonld  extra JSON-LD blocks @var bool|null $noindex @var bool|null $preloadHero
 */
$path = $path ?? '';
$canonical = abs_url($path);
$ogImage = abs_url('og-automate.png');
?><!doctype html>
<html lang="en" class="no-js">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta name="robots" content="<?= !empty($noindex) ? 'noindex,follow' : 'index,follow' ?>">
<meta name="theme-color" content="#F4F7FB">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Automate Limited">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="<?= e($ogImage) ?>">
<link rel="icon" href="<?= e(url('favicon.ico')) ?>" sizes="32x32">
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%20100%20100%27%3E%3Cstyle%3E.a%7Bfill%3A%23042F65%7D%40media%28prefers-color-scheme%3Adark%29%7B.a%7Bfill%3A%23FFFFFF%7D%7D%3C%2Fstyle%3E%3Csvg%20x%3D%276%27%20y%3D%2716%27%20width%3D%2788%27%20height%3D%2768%27%20viewBox%3D%270%200%20402%20342%27%3E%3Cg%20transform%3D%27translate%280.000000%2C342.000000%29%20scale%280.100000%2C-0.100000%29%27%3E%3Cpath%20class%3D%27a%27%20d%3D%27M1785%203397%20c-34%20-19%20-48%20-39%20-110%20-162%20-39%20-77%20-118%20-230%20-175%20-340%20-146%20-282%20-200%20-387%20-338%20-657%20-66%20-130%20-127%20-247%20-135%20-260%20-8%20-13%20-75%20-142%20-149%20-288%20-74%20-146%20-148%20-290%20-165%20-320%20-17%20-30%20-81%20-154%20-143%20-275%20-62%20-121%20-116%20-224%20-120%20-230%20-4%20-5%20-53%20-100%20-110%20-210%20-56%20-110%20-105%20-204%20-110%20-210%20-23%20-30%20-219%20-428%20-215%20-436%204%20-5%20185%20-9%20449%20-9%20409%200%20444%201%20458%2018%2013%2015%20608%201206%20701%201402%2019%2041%2094%20195%20165%20342%2072%20146%20141%20288%20153%20315%2090%20200%20150%20312%20164%20307%208%20-4%2015%20-13%2015%20-21%200%20-8%2013%20-42%2029%20-76%2027%20-59%2041%20-90%2076%20-172%208%20-19%2022%20-52%2031%20-72%209%20-21%2022%20-51%2030%20-67%208%20-16%2014%20-33%2014%20-37%200%20-4%2013%20-36%2029%20-71%2016%20-35%2036%20-81%2046%20-103%209%20-22%2028%20-65%2042%20-95%2014%20-30%2029%20-64%2033%20-75%204%20-11%2018%20-42%2030%20-70%2012%20-27%2026%20-59%2030%20-70%204%20-11%2021%20-49%2038%20-85%2016%20-36%2038%20-83%2047%20-105%209%20-22%2023%20-53%2031%20-69%208%20-16%2014%20-33%2014%20-37%200%20-4%2013%20-36%2029%20-71%2016%20-35%2036%20-79%2044%20-98%208%20-19%2026%20-60%2039%20-90%2014%20-30%2031%20-71%2038%20-90%208%20-19%2027%20-59%2042%20-89%2015%20-29%2028%20-57%2028%20-62%200%20-5%2013%20-37%2028%20-71%2016%20-35%2035%20-79%2042%20-98%208%20-19%2027%20-59%2042%20-89%2015%20-29%2028%20-57%2028%20-63%200%20-5%2013%20-37%2029%20-71%2016%20-34%2036%20-80%2046%20-102%2077%20-179%2034%20-165%20502%20-165%20348%200%20425%202%20429%2014%203%207%20-15%2056%20-40%20109%20-26%2053%20-46%2099%20-46%20101%200%203%20-22%2051%20-49%20108%20-27%2057%20-57%20121%20-66%20143%20-10%2022%20-33%2071%20-51%20110%20-19%2038%20-34%2074%20-34%2078%200%205%20-17%2046%20-39%2090%20-21%2045%20-46%20100%20-56%20122%20-10%2022%20-33%2071%20-52%20109%20-18%2038%20-33%2073%20-33%2078%200%204%20-23%2055%20-50%20111%20-28%2057%20-50%20106%20-50%20110%200%204%20-17%2044%20-39%2089%20-21%2046%20-46%20101%20-56%20123%20-9%2022%20-27%2063%20-40%2090%20-12%2028%20-31%2068%20-40%2090%20-10%2022%20-33%2071%20-51%20110%20-19%2038%20-34%2073%20-34%2077%200%204%20-27%2063%20-60%20130%20-33%2068%20-60%20127%20-60%20132%200%205%20-22%2054%20-49%20110%20-27%2055%20-58%20122%20-70%20149%20-45%20100%20-64%20143%20-78%20177%20-9%2019%20-35%2076%20-58%20125%20-23%2050%20-48%20106%20-56%20125%20-7%2019%20-26%2059%20-41%2089%20-15%2029%20-28%2057%20-28%2062%200%205%20-17%2046%20-39%2091%20-36%2078%20-52%20113%20-85%20191%20-79%20186%20-58%20177%20-433%20176%20-286%20-1%20-300%20-1%20-338%20-22z%27%2F%3E%3C%2Fg%3E%3C%2Fsvg%3E%3C%2Fsvg%3E">
<link rel="apple-touch-icon" href="<?= e(url('apple-touch-icon.png')) ?>">
<link rel="preload" href="<?= e(url('fonts/outfit-latin-wght-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= e(url('fonts/ibm-plex-sans-latin-wght-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preconnect" href="https://images.unsplash.com">
<script>
(function(r){r.className=r.className.replace("no-js","js");
try{if(localStorage.getItem("automate-theme")==="dark")r.setAttribute("data-theme","dark")}catch(e){}
if(window.matchMedia&&matchMedia("(prefers-reduced-motion: reduce)").matches)r.className+=" no-motion";
setTimeout(function(){if(!window.AUTOMATE_READY)r.className+=" rv-off"},4000);
})(document.documentElement);
</script>
<link rel="stylesheet" href="<?= e(asset('assets/css/site.css')) ?>">
<?php foreach (($jsonld ?? []) as $block): ?>
<script type="application/ld+json"><?= json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php endforeach; ?>
</head>
