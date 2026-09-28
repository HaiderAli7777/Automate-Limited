<?php
/**
 * Hiring pipeline board. @var array $apps  rows with stage_id, candidate fields, job_title (optional)
 * @var bool|null $showJob  @var bool|null $recentOnly  terminal columns limited to recent moves
 */
$stages = ats_stages();
$byStage = array_fill_keys(array_keys($stages), []);
foreach ($apps as $a) {
    if (isset($byStage[(int) $a['stage_id']])) {
        $byStage[(int) $a['stage_id']][] = $a;
    }
}
$canMove = user_can('ats.manage');
$stageOptions = '';
foreach ($stages as $s) {
    $stageOptions .= '<option value="' . (int) $s['id'] . '">' . e($s['name']) . '</option>';
}
?>
<?php if (!$canMove): ?><p class="board-note"><?= icon('eye', 'ic ic--sm') ?> View only. Your access doesn't include moving candidates.</p><?php else: ?>
<p class="board-note">Drag a card to move it. Moving someone to <?= e(implode(' or ', array_column(array_filter($stages, static fn ($s) => $s['kind'] === 'rejected'), 'name')) ?: 'a rejected stage') ?> asks for a reason.</p>
<?php endif; ?>
<div class="board<?= $canMove ? '' : ' board--readonly' ?>"<?= $canMove ? ' data-board data-dialog-pool="poolDialog"' : '' ?> data-move-url="<?= e(admin_url('applications/{id}/stage')) ?>" data-reason-dialog="rejectDialog">
  <?php foreach ($stages as $sid => $s): $cards = $byStage[$sid]; ?>
    <section class="bcol" style="--dot:<?= stage_dot($s['color']) ?>" aria-label="<?= e($s['name']) ?>">
      <header class="bcol__head"><span class="bcol__dot"></span><h3 class="bcol__name"><?= e($s['name']) ?></h3><span class="bcol__count"><?= count($cards) ?></span></header>
      <div class="bcol__list" data-stage="<?= (int) $sid ?>" data-kind="<?= e($s['kind']) ?>">
        <?php foreach ($cards as $a):
            $name = candidate_name($a);
            $days = days_since($a['stage_changed_at']);
            $late = $s['kind'] === 'active' && $days >= 7;
        ?>
          <article class="kcard" data-id="<?= (int) $a['id'] ?>">
            <div class="kcard__top">
              <?= avatar($name, 'sm') ?>
              <div style="min-width:0">
                <a class="kcard__title" href="<?= e(admin_url('applications/' . $a['id'])) ?>"><?= e($name) ?></a>
                <div class="kcard__sub"><?= !empty($showJob) ? e((string) $a['job_title']) : e((string) ($a['current_title'] ?: $a['email'])) ?></div>
              </div>
            </div>
            <div class="kcard__meta">
              <span class="<?= $late ? 'is-late' : '' ?>" title="Time in this stage"><?= icon('clock') ?><?= $days === 0 ? 'Today' : $days . 'd' ?></span>
              <?php if (!empty($a['avg_rating'])): ?><span title="Average interview score"><?= icon('star') ?><?= e(number_format((float) $a['avg_rating'], 1)) ?></span><?php endif; ?>
              <?php if (!empty($a['next_interview'])): ?><span title="Next interview"><?= icon('calendar-dots') ?><?= e(fmt_day($a['next_interview'])) ?></span><?php endif; ?>
            </div>
            <?php if ($canMove): ?><div class="kcard__move"><select class="select" aria-label="Move <?= e($name) ?> to stage"><?= str_replace('value="' . $sid . '"', 'value="' . $sid . '" selected', $stageOptions) ?></select></div><?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
</div>

<?php if ($canMove): ?>
<dialog class="modal" id="rejectDialog" aria-labelledby="rejectTitle">
  <form method="dialog">
    <div class="modal__head"><h2 id="rejectTitle">Why isn't this moving forward?</h2></div>
    <div class="modal__body">
      <div class="field"><label for="rj-reason">Reason</label><select class="select" id="rj-reason" name="reason"><?php foreach (REJECTION_REASONS as $r): ?><option><?= e($r) ?></option><?php endforeach; ?></select></div>
      <label class="checkbox"><input type="checkbox" name="notify" value="1" checked><span>Email the candidate the "Not moving forward" message</span></label>
    </div>
    <div class="modal__foot"><button class="btn btn--ghost" type="button" data-close-dialog>Cancel</button><button class="btn btn--primary" type="submit">Move</button></div>
  </form>
</dialog>
<?php partial('admin/partials/pool-dialog', ['id' => 'poolDialog', 'method' => 'dialog']); ?>
<?php endif; ?>
