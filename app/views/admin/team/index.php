<?php /** @var array $users */ ?>
<div class="phead">
  <div><h1>Team</h1><p class="phead__sub">Who can sign in, and what each person can see.</p></div>
  <div class="phead__actions"><a class="btn btn--primary" href="<?= e(admin_url('team/new')) ?>"><?= icon('user-plus') ?>Add a teammate</a></div>
</div>
<div class="panel">
  <div class="table-wrap">
    <table class="tbl">
      <thead><tr><th>Name</th><th>Role</th><th>Status</th><th>Last sign-in</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><div class="person"><?= avatar($u['name']) ?><span><a class="t-strong" href="<?= e(admin_url('team/' . $u['id'])) ?>"><?= e($u['name']) ?></a><span class="t-sub"><?= e($u['email']) ?><?= $u['title'] ? ' · ' . e($u['title']) : '' ?></span></span></div></td>
          <td><?= badge(role_label($u['role']), ['admin' => 'violet', 'manager' => 'blue', 'recruiter' => 'teal', 'sales' => 'amber', 'interviewer' => 'slate'][$u['role']] ?? 'slate', 'badge--plain') ?></td>
          <td><?= (int) $u['is_active'] ? badge('Active', 'green') : badge('Switched off', 'slate') ?></td>
          <td class="muted"><?= $u['last_login_at'] ? e(time_ago($u['last_login_at'])) : 'Never' ?></td>
          <td class="t-right"><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('team/' . $u['id'])) ?>">Edit</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<div class="panel">
  <div class="panel__head"><h2>What each role can do</h2></div>
  <div class="panel__body"><dl class="dl">
    <?php foreach (ROLES as $key => $label): ?><dt><?= e($label) ?></dt><dd><?= e(ROLE_HELP[$key]) ?></dd><?php endforeach; ?>
  </dl></div>
</div>
