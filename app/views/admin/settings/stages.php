<?php
/** @var string $tab @var array $atsCounts @var array $leadCounts */
partial('admin/settings/_tabs', ['tab' => $tab]);
$colorOpts = static function (string $sel): string {
    $h = '';
    foreach (STAGE_COLORS as $c) {
        $h .= '<option value="' . $c . '"' . selected($sel, $c) . '>' . ucfirst($c) . '</option>';
    }
    return $h;
};
$editor = static function (string $which, array $stages, array $counts) use ($colorOpts): void {
    $kinds = $which === 'crm' ? ['open' => 'Open', 'won' => 'Won', 'lost' => 'Lost'] : ['active' => 'In progress', 'hired' => 'Hired', 'pool' => 'Talent pool', 'rejected' => 'Rejected'];
    $rows = array_values($stages);
    $rows[] = ['id' => 0, 'name' => '', 'color' => 'slate', 'kind' => array_key_first($kinds), 'probability' => 0];
    ?>
    <form method="post" action="<?= e(admin_url('settings/stages')) ?>" class="panel">
      <?= csrf_field() ?>
      <input type="hidden" name="pipeline" value="<?= $which ?>">
      <div class="panel__head">
        <div><h2><?= $which === 'crm' ? 'Sales pipeline' : 'Hiring pipeline' ?></h2><p class="muted small"><?= $which === 'crm' ? 'Leads move left to right. Probability weights the forecast.' : 'Applications move left to right. Hired and Rejected end the process.' ?></p></div>
        <button class="btn btn--primary btn--sm" type="submit">Save</button>
      </div>
      <div data-stage-list>
        <?php foreach ($rows as $s): $n = (int) ($counts[$s['id']] ?? 0); ?>
          <div class="srow" data-stage-row>
            <span class="srow__handle"><button type="button" data-move="up" aria-label="Move up">&#9650;</button><button type="button" data-move="down" aria-label="Move down">&#9660;</button></span>
            <input type="hidden" name="id[]" value="<?= (int) $s['id'] ?>">
            <input class="input input--sm" name="name[]" value="<?= e($s['name']) ?>" placeholder="<?= $s['id'] ? '' : 'Add a stage' ?>" aria-label="Stage name" maxlength="60">
            <select class="select select--sm" name="color[]" aria-label="Colour"><?= $colorOpts($s['color']) ?></select>
            <select class="select select--sm" name="kind[]" aria-label="Type"><?= options($kinds, $s['kind']) ?></select>
            <?php if ($which === 'crm'): ?>
              <input class="input input--sm" name="probability[]" type="number" min="0" max="100" value="<?= (int) $s['probability'] ?>" aria-label="Win probability percent" title="Win probability %">
            <?php else: ?>
              <input type="hidden" name="probability[]" value="0"><span class="small muted"><?= $s['id'] ? plural($n, 'application') : '' ?></span>
            <?php endif; ?>
            <?php if ($s['id']): ?>
              <label class="checkbox small" title="<?= $n ? 'Move its ' . $n . ' records first' : 'Delete when saving' ?>"><input type="checkbox" name="delete[]" value="<?= (int) $s['id'] ?>"<?= $n ? ' disabled' : '' ?>>Delete</label>
            <?php else: ?><span></span><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </form>
    <?php
};
?>
<div class="split--even" style="display:grid;gap:18px">
  <?php $editor('ats', ats_stages(), $atsCounts); ?>
  <?php $editor('crm', lead_stages(), $leadCounts); ?>
</div>
