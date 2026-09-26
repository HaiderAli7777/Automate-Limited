<?php
/** @var array $users */
$status = input('status');
$all = db()->all('SELECT is_active, last_login_at, role FROM users');
$activeCount = count(array_filter($all, static fn ($u) => (int) $u['is_active'] === 1));
$offCount = count($all) - $activeCount;
$adminCount = count(array_filter($all, static fn ($u) => (int) $u['is_active'] === 1 && $u['role'] === 'admin'));
$pendingCount = count(array_filter($all, static fn ($u) => (int) $u['is_active'] === 1 && !$u['last_login_at']));
$roleColour = ['admin' => 'violet', 'manager' => 'blue', 'recruiter' => 'teal', 'sales' => 'amber', 'interviewer' => 'cyan', 'viewer' => 'slate', 'custom' => 'pink'];
$level = static function (string $v): string {
    if ($v === 'None' || $v === 'No') {
        return '<span class="acc acc--none">' . e($v) . '</span>';
    }
    return '<span class="acc' . (str_starts_with($v, 'Full') || $v === 'Yes' ? ' acc--full' : ' acc--view') . '">' . e($v) . '</span>';
};
?>
<div class="phead">
  <div><h1>Team and access</h1><p class="phead__sub">Add people, choose what they can see and do, and switch accounts off when someone leaves.</p></div>
  <div class="phead__actions"><a class="btn btn--primary" href="<?= e(admin_url('team/new')) ?>"><?= icon('user-plus') ?>Add a team member</a></div>
</div>

<div class="kpis">
  <div class="kpi"><span class="kpi__label"><?= icon('users-three') ?>Active members</span><span class="kpi__value"><?= $activeCount ?></span><span class="kpi__foot">Can sign in today</span></div>
  <div class="kpi"><span class="kpi__label"><?= icon('shield-check') ?>Administrators</span><span class="kpi__value"><?= $adminCount ?></span><span class="kpi__foot">Full control, including access rights</span></div>
  <div class="kpi"><span class="kpi__label"><?= icon('paper-plane-tilt') ?>Not signed in yet</span><span class="kpi__value"><?= $pendingCount ?></span><span class="kpi__foot">Invited, waiting for their first sign-in</span></div>
  <div class="kpi"><span class="kpi__label"><?= icon('power') ?>Switched off</span><span class="kpi__value"><?= $offCount ?></span><span class="kpi__foot">Can't sign in. Their history stays</span></div>
</div>

<div class="panel">
  <div class="panel__head">
    <nav class="seg" aria-label="Filter team members">
      <a href="<?= e(admin_url('team')) ?>" class="<?= $status === '' ? 'is-active' : '' ?>">Everyone</a>
      <a href="<?= e(admin_url('team') . '?status=active') ?>" class="<?= $status === 'active' ? 'is-active' : '' ?>">Active</a>
      <a href="<?= e(admin_url('team') . '?status=off') ?>" class="<?= $status === 'off' ? 'is-active' : '' ?>">Switched off</a>
    </nav>
    <span class="muted"><?= e(plural(count($users), 'person', 'people')) ?></span>
  </div>
  <?php if (!$users): ?>
    <div class="empty"><?= icon('users-three') ?><h3>Nobody here</h3><p>No team members match this filter.</p></div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="tbl tbl--team">
      <thead><tr><th>Team member</th><th>Access level</th><th>Recruitment</th><th>Interviews</th><th>Sales</th><th>Last sign-in</th><th><span class="sr-only">Actions</span></th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): $sum = access_summary($u); $editable = team_editable($u); $isMe = (int) $u['id'] === auth_id(); ?>
        <tr class="<?= (int) $u['is_active'] ? '' : 'is-off' ?>">
          <td><div class="person"><?= avatar($u['name']) ?><span>
            <?php if ($editable): ?><a class="t-strong" href="<?= e(admin_url('team/' . $u['id'])) ?>"><?= e($u['name']) ?></a><?php else: ?><span class="t-strong"><?= e($u['name']) ?></span><?php endif; ?>
            <?= $isMe ? '<span class="chip chip--xs">You</span>' : '' ?>
            <span class="t-sub"><?= e($u['email']) ?><?= $u['title'] ? ' · ' . e($u['title']) : '' ?></span></span></div></td>
          <td>
            <?= badge(role_label($u['role']), $roleColour[$u['role']] ?? 'slate', 'badge--plain') ?>
            <?php if (!(int) $u['is_active']): ?> <?= badge('Switched off', 'slate') ?><?php endif; ?>
            <?php if ($sum['Also'] !== '' && $u['role'] !== 'admin'): ?><span class="t-sub">Also: <?= e($sum['Also']) ?></span><?php endif; ?>
          </td>
          <td><?= $level($sum['Recruitment']) ?></td>
          <td><?= $level($sum['Interviews']) ?></td>
          <td><?= $level($sum['Sales']) ?></td>
          <td class="muted nowrap"><?= $u['last_login_at'] ? e(time_ago($u['last_login_at'])) : ((int) $u['is_active'] ? 'Invited, not yet' : 'Never') ?></td>
          <td class="t-right">
            <?php if ($editable): ?>
            <details class="dropdown">
              <summary class="btn btn--ghost btn--icon" aria-label="Actions for <?= e($u['name']) ?>"><?= icon('dots-three') ?></summary>
              <div class="dropdown__menu">
                <a href="<?= e(admin_url('team/' . $u['id'])) ?>"><?= icon('sliders-horizontal') ?>Edit details and access</a>
                <?php if ((int) $u['is_active']): ?>
                <form method="post" action="<?= e(admin_url('team/' . $u['id'] . '/invite')) ?>"><?= csrf_field() ?><button type="submit"><?= icon('paper-plane-tilt') ?><?= $u['last_login_at'] ? 'Email a password reset link' : 'Resend the invitation' ?></button></form>
                <?php endif; ?>
                <?php if (!$isMe): ?>
                <div class="dropdown__sep"></div>
                <form method="post" action="<?= e(admin_url('team/' . $u['id'] . '/toggle')) ?>"<?= (int) $u['is_active'] ? ' data-confirm="Switch off ' . e($u['name']) . '? They will be signed out and can\'t sign in until you switch them back on."' : '' ?>><?= csrf_field() ?>
                  <button type="submit" class="<?= (int) $u['is_active'] ? 'is-danger' : '' ?>"><?= icon('power') ?><?= (int) $u['is_active'] ? 'Switch off account' : 'Switch account back on' ?></button></form>
                <?php endif; ?>
              </div>
            </details>
            <?php else: ?><span class="muted small" title="Only an administrator can change another administrator"><?= icon('lock', 'ic ic--sm') ?></span><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<div class="panel">
  <div class="panel__head"><h2>What each access level includes</h2><span class="muted">Pick a level when you add someone, or choose Custom access and tick exactly what they need.</span></div>
  <div class="table-wrap">
    <table class="tbl matrix">
      <thead><tr><th>Permission</th><?php foreach (ROLES as $key => $label): if ($key === 'custom') continue; ?><th class="t-center"><?= e($label) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
      <?php foreach (PERMISSION_GROUPS as $group => $perms): ?>
        <tr class="matrix__group"><th colspan="<?= count(ROLES) ?>" scope="colgroup"><?= e($group) ?></th></tr>
        <?php foreach ($perms as $perm => $text): ?>
        <tr>
          <td><?= e($text) ?></td>
          <?php foreach (ROLES as $key => $label): if ($key === 'custom') continue; $has = $key === 'admin' || in_array($perm, ROLE_PRESETS[$key] ?? [], true); ?>
            <td class="t-center"><?= $has ? '<span class="yes">' . icon('check') . '<span class="sr-only">Yes</span></span>' : '<span class="no" aria-label="No">&middot;</span>' ?></td>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
