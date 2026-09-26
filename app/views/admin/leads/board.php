<?php
/** @var array $leads */
$stages = lead_stages();
$byStage = array_fill_keys(array_keys($stages), []);
foreach ($leads as $l) { if (isset($byStage[(int) $l['stage_id']])) { $byStage[(int) $l['stage_id']][] = $l; } }
$cur = (string) setting('default_currency', 'PKR');
$openValue = 0.0; $weighted = 0.0;
foreach ($leads as $l) { if ($l['status'] === 'open') { $openValue += (float) $l['value']; $weighted += weighted_value($l); } }
$canMove = user_can('crm.manage');
$stageOptions = '';
foreach ($stages as $s) { $stageOptions .= '<option value="' . (int) $s['id'] . '">' . e($s['name']) . '</option>'; }
?>
<div class="phead">
  <div><h1>Sales pipeline</h1><p class="phead__sub">Open pipeline <?= e(fmt_money($openValue, $cur)) ?>, weighted forecast <?= e(fmt_money($weighted, $cur)) ?>. Closed deals stay visible for 30 days.</p></div>
  <form class="phead__actions" method="get" action="<?= e(admin_url('leads/board')) ?>" data-autosubmit>
    <?php if (user_can('crm.all')): ?><select class="select select--sm" name="owner" aria-label="Owner" style="width:auto"><option value="">Everyone's leads</option><option value="me"<?= selected(input('owner'), 'me') ?>>My leads</option><?php foreach (active_users() as $u): ?><option value="<?= (int) $u['id'] ?>"<?= selected(input('owner'), $u['id']) ?>><?= e($u['name']) ?></option><?php endforeach; ?></select><?php endif; ?>
    <select class="select select--sm" name="service" aria-label="Service" style="width:auto"><option value="">All services</option><?php foreach (array_merge(lead_services(), ['Not sure yet']) as $s): ?><option<?= selected(input('service'), $s) ?>><?= e($s) ?></option><?php endforeach; ?></select>
    <a class="btn btn--quiet btn--sm" href="<?= e(admin_url('leads')) ?>"><?= icon('list') ?>List view</a>
    <?php if ($canMove): ?><a class="btn btn--primary btn--sm" href="<?= e(admin_url('leads/new')) ?>"><?= icon('plus') ?>New lead</a><?php endif; ?>
  </form>
</div>
<p class="board-note"><?= $canMove ? 'Drag a card to change its stage.' : 'View only.' ?> Column totals show the value of the deals in each stage.</p>
<div class="board<?= $canMove ? '' : ' board--readonly' ?>"<?= $canMove ? ' data-board' : ' data-board-static' ?> data-move-url="<?= e(admin_url('leads/{id}/stage')) ?>" data-reason-dialog="lostDialog" data-currency="<?= e($cur) ?>">
  <?php foreach ($stages as $sid => $s): $cards = $byStage[$sid]; ?>
    <section class="bcol" style="--dot:<?= stage_dot($s['color']) ?>" aria-label="<?= e($s['name']) ?>">
      <header class="bcol__head"><span class="bcol__dot"></span><h3 class="bcol__name"><?= e($s['name']) ?></h3><span class="bcol__count"><?= count($cards) ?></span></header>
      <p class="bcol__sum"><span class="bcol__value" data-sum></span><?= $s['kind'] === 'open' ? '<span>' . (int) $s['probability'] . '% win chance</span>' : '' ?></p>
      <div class="bcol__list" data-stage="<?= (int) $sid ?>" data-kind="<?= e($s['kind']) ?>">
        <?php foreach ($cards as $l): $late = $l['status'] === 'open' && $l['next_follow_up'] && (ts($l['next_follow_up']) ?? 0) < time(); ?>
          <article class="kcard" data-id="<?= (int) $l['id'] ?>" data-value="<?= e((string) ((float) $l['value'])) ?>">
            <a class="kcard__title" href="<?= e(admin_url('leads/' . $l['id'])) ?>"><?= e($l['title']) ?></a>
            <div class="kcard__sub"><?= e(implode(' · ', array_filter([$l['contact_name'], $l['contact_company']]))) ?></div>
            <div class="kcard__meta">
              <?php if ($l['value'] !== null): ?><span class="kcard__value"><?= e(compact_money($l['value'], $l['currency'])) ?></span><?php endif; ?>
              <?php if ($l['priority'] === 'high'): ?><?= badge('High', 'orange', 'badge--plain') ?><?php endif; ?>
              <?php if ($l['next_follow_up'] && $l['status'] === 'open'): ?><span class="<?= $late ? 'is-late' : '' ?>" title="Next follow-up"><?= icon('bell') ?><?= e(fmt_day($l['next_follow_up'])) ?></span><?php endif; ?>
              <?php if ($l['owner_id']): ?><span style="margin-left:auto" title="Owner: <?= e(user_name((int) $l['owner_id'])) ?>"><?= avatar(user_name((int) $l['owner_id']), 'sm') ?></span><?php endif; ?>
            </div>
            <?php if ($canMove): ?><div class="kcard__move"><select class="select" aria-label="Move <?= e($l['title']) ?> to stage"><?= str_replace('value="' . $sid . '"', 'value="' . $sid . '" selected', $stageOptions) ?></select></div><?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
</div>
<?php if ($canMove) partial('admin/partials/lost-dialog', ['id' => 'lostDialog', 'method' => 'dialog']); ?>
