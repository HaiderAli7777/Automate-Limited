<?php /** @var array $rows @var array $p @var string $q */ ?>
<div class="phead">
  <div><h1>Contacts</h1><p class="phead__sub">Everyone who has enquired or that you've added. <?= plural($p['total'], 'contact') ?>.</p></div>
  <?php if (user_can('crm.manage')): ?><div class="phead__actions"><a class="btn btn--primary" href="<?= e(admin_url('contacts/new')) ?>"><?= icon('plus') ?>New contact</a></div><?php endif; ?>
</div>
<form class="filters" method="get" action="<?= e(admin_url('contacts')) ?>">
  <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Name, email, company, phone" aria-label="Search contacts">
  <button class="btn btn--quiet btn--sm" type="submit">Search</button>
</form>
<div class="panel">
  <?php if (!$rows): ?>
    <div class="empty"><?= icon('address-book') ?><h3>No contacts yet.</h3><p>People who use the website contact form are added automatically.</p></div>
  <?php else: ?>
  <div class="table-wrap"><table class="tbl">
    <thead><tr><th>Name</th><th>Company</th><th>Phone</th><th class="num">Open leads</th><th class="num">Won</th><th>Last activity</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $c): ?>
      <tr>
        <td><div class="person"><?= avatar($c['name']) ?><span><a class="t-strong" href="<?= e(admin_url('contacts/' . $c['id'])) ?>"><?= e($c['name']) ?></a><span class="t-sub"><?= e((string) $c['email']) ?></span></span></div></td>
        <td><?= e((string) $c['company']) ?></td>
        <td class="nowrap"><?= e((string) $c['phone']) ?></td>
        <td class="num"><?= (int) $c['open_count'] ?><span class="muted">/<?= (int) $c['lead_count'] ?></span></td>
        <td class="num nowrap"><?= $c['won_value'] ? e(compact_money($c['won_value'])) : '<span class="muted">-</span>' ?></td>
        <td class="muted nowrap"><?= e(time_ago($c['last_activity'] ?: $c['updated_at'])) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?= pager($p) ?>
  <?php endif; ?>
</div>
