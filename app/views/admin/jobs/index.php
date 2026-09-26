<?php /** @var array $jobs @var string $status @var string $q @var array $counts */ ?>
<div class="phead">
  <div><h1>Jobs</h1><p class="phead__sub">Open roles appear on the careers page automatically.</p></div>
  <div class="phead__actions"><a class="btn btn--quiet" href="<?= e(url('careers/')) ?>" target="_blank" rel="noopener"><?= icon('arrow-square-out') ?>Careers page</a><?php if (user_can('ats.manage')): ?><a class="btn btn--primary" href="<?= e(admin_url('jobs/new')) ?>"><?= icon('plus') ?>New job</a><?php endif; ?></div>
</div>
<nav class="tabs" aria-label="Job status">
  <?php foreach (['open' => 'Open', 'draft' => 'Drafts', 'paused' => 'Paused', 'closed' => 'Closed', 'all' => 'All'] as $k => $label): $n = $k === 'all' ? array_sum($counts) : (int) ($counts[$k] ?? 0); ?>
    <a href="<?= e(admin_url('jobs') . '?status=' . $k) ?>" class="<?= $status === $k ? 'is-active' : '' ?>"><?= e($label) ?> <span class="count"><?= $n ?></span></a>
  <?php endforeach; ?>
</nav>
<div class="panel">
  <?php if (!$jobs): ?>
    <div class="empty"><?= icon('briefcase') ?><h3><?= $status === 'open' ? 'No open jobs.' : 'Nothing here.' ?></h3><p>Create a job, add a few screening questions and publish it. It goes live on the careers page straight away.</p><a class="btn btn--primary" href="<?= e(admin_url('jobs/new')) ?>">Create a job</a></div>
  <?php else: ?>
  <div class="table-wrap"><table class="tbl">
    <thead><tr><th>Job</th><th>Status</th><th class="num">New</th><th class="num">In process</th><th class="num">Hired</th><th class="num">Total</th><th>Hiring manager</th><th>Closes</th></tr></thead>
    <tbody>
    <?php foreach ($jobs as $j): ?>
      <tr>
        <td><a class="t-strong" href="<?= e(admin_url('jobs/' . $j['id'])) ?>"><?= e($j['title']) ?></a><span class="t-sub"><?= e(implode(' · ', array_filter([$j['department'], $j['location'], EMPLOYMENT_TYPES[$j['employment_type']] ?? '']))) ?><?= !(int) $j['listed'] ? ' · Not listed' : '' ?></span></td>
        <td><?= badge(JOB_STATUSES[$j['status']] ?? $j['status'], JOB_STATUS_COLORS[$j['status']] ?? 'slate') ?></td>
        <td class="num"><?= (int) $j['new_apps'] ? '<strong>' . (int) $j['new_apps'] . '</strong>' : '<span class="muted">0</span>' ?></td>
        <td class="num"><?= (int) $j['active_apps'] ?></td>
        <td class="num"><?= (int) $j['hired_apps'] ?><span class="muted">/<?= (int) $j['openings'] ?></span></td>
        <td class="num"><?= (int) $j['total_apps'] ?></td>
        <td><?= e((string) $j['manager_name']) ?></td>
        <td class="muted nowrap"><?= $j['closes_at'] ? e(fmt_date($j['closes_at'])) : '' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>
