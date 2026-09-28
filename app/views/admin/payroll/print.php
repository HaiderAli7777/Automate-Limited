<?php
/** Printable payslips: one, or every payslip in a run. The browser's print dialog saves them as PDF.
 * @var array $slips list of ['slip' => array, 'lines' => array] @var string $title @var string $back */
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?></title>
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="slip-page">
<div class="slip-bar">
  <a class="btn btn--ghost btn--sm" href="<?= e($back) ?>"><?= icon('arrow-left') ?>Back</a>
  <?php if (count($slips) > 1): ?><span class="slip-bar__count"><?= plural(count($slips), 'payslip') ?>, one per page</span><?php endif; ?>
  <button class="btn btn--primary btn--sm" type="button" data-print><?= icon('printer') ?>Print or save as PDF</button>
</div>
<?php foreach ($slips as $one) { partial('admin/payroll/_slip', $one); } ?>
<script src="<?= e(asset('assets/js/print.js')) ?>" defer></script>
</body>
</html>
