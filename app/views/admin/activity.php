<?php /** @var array $rows  @var array $p  @var array $subjects */
$typeColour = ['application' => 'teal', 'candidate' => 'cyan', 'lead' => 'amber', 'contact' => 'orange', 'user' => 'violet'];
$lastDay = '';
?>
<div class="phead">
  <div><h1>Activity log</h1><p class="phead__sub">Everything done in the team area: who did it, what changed and when.</p></div>
</div>
<form class="filters" method="get" action="<?= e(admin_url('activity')) ?>" data-autosubmit>
  <select class="select" name="type" aria-label="Area"><option value="">All areas</option><?= options(ACTIVITY_ENTITIES, input('type')) ?></select>
  <select class="select" name="user" aria-label="Team member"><?= user_options(input_int('user') ?: null, 'Everyone') ?></select>
  <label class="small muted" for="f-from">Since</label>
  <input class="input" id="f-from" type="date" name="from" value="<?= e(input('from')) ?>">
  <?php if (input('type') !== '' || input('user') !== '' || input('from') !== ''): ?><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('activity')) ?>">Clear</a><?php endif; ?>
  <noscript><button class="btn btn--quiet btn--sm" type="submit">Filter</button></noscript>
</form>
<div class="panel">
  <?php if (!$rows): ?>
    <div class="empty"><?= icon('clock-counter-clockwise') ?><h3>No activity found</h3><p>Try a different filter.</p></div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="tbl">
      <thead><tr><th style="width:90px">Time</th><th>What happened</th><th>About</th><th>By</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $a):
        $day = substr((string) $a['created_at'], 0, 10);
        $link = activity_link($a);
        $subject = $subjects[$a['entity_type']][(int) $a['entity_id']] ?? null;
        if ($day !== $lastDay): $lastDay = $day; ?>
        <tr class="tbl__group"><th colspan="4" scope="colgroup"><?= e($day === date('Y-m-d') ? 'Today' : ($day === date('Y-m-d', strtotime('-1 day')) ? 'Yesterday' : fmt_date($day))) ?></th></tr>
        <?php endif; ?>
        <tr>
          <td class="muted nowrap num"><?= e(date('H:i', strtotime((string) $a['created_at']))) ?></td>
          <td><div class="person"><span class="tl--<?= e($a['type']) ?>"><span class="tl__ic"><?= icon(activity_icon((string) $a['type'])) ?></span></span><span><span class="t-strong"><?= e($a['title']) ?></span><?php if ($a['body'] !== null && trim((string) $a['body']) !== ''): ?><span class="t-sub clamp-1"><?= e(mb_substr(trim((string) $a['body']), 0, 160)) ?></span><?php endif; ?></span></div></td>
          <td>
            <?= badge(rtrim(ACTIVITY_ENTITIES[$a['entity_type']] ?? ucfirst((string) $a['entity_type']), 's'), $typeColour[$a['entity_type']] ?? 'slate', 'badge--plain') ?>
            <?php if ($subject !== null && $link): ?><a class="link small" href="<?= e($link) ?>"><?= e($subject) ?></a><?php elseif ($subject === null): ?><span class="muted small">Deleted</span><?php endif; ?>
          </td>
          <td class="nowrap"><?= $a['user_name'] ? e($a['user_name']) : '<span class="muted">Website</span>' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?= pager($p) ?>
  <?php endif; ?>
</div>
