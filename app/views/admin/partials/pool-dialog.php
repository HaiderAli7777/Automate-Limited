<?php
/**
 * Keep a candidate in the talent pool: reason, revisit date, optional email.
 * @var string $id  @var string $method 'dialog' (kanban) or 'post'  @var string|null $action  @var int|null $stageId
 */
$method = $method ?? 'dialog';
$pfx = e($id);
?>
<dialog class="modal" id="<?= $pfx ?>" aria-labelledby="<?= $pfx ?>-title">
  <form method="<?= $method === 'post' ? 'post' : 'dialog' ?>"<?= $method === 'post' ? ' action="' . e((string) $action) . '"' : '' ?>>
    <?php if ($method === 'post'): ?><?= csrf_field() ?><input type="hidden" name="stage_id" value="<?= (int) $stageId ?>"><?php endif; ?>
    <div class="modal__head"><h2 id="<?= $pfx ?>-title">Keep in the talent pool</h2></div>
    <div class="modal__body">
      <p class="small muted">They stay on file for this job instead of being rejected, so you can come back to them when the budget or a better-fitting role opens up.</p>
      <div class="field"><label for="<?= $pfx ?>-reason">Why not now?</label><select class="select" id="<?= $pfx ?>-reason" name="reason"><?php foreach (POOL_REASONS as $r): ?><option><?= e($r) ?></option><?php endforeach; ?></select></div>
      <div class="grid-2 grid-tight">
        <div class="field"><label for="<?= $pfx ?>-revisit">Revisit on <span class="opt">(optional)</span></label><input class="input" id="<?= $pfx ?>-revisit" type="date" name="revisit_on" min="<?= e(date('Y-m-d')) ?>"><p class="help">Creates a follow-up task on that date.</p></div>
        <div class="field"><label for="<?= $pfx ?>-note">Note <span class="opt">(optional)</span></label><input class="input" id="<?= $pfx ?>-note" name="note" maxlength="500" placeholder="Asked for PKR 450k; budget is 350k"></div>
      </div>
      <label class="checkbox"><input type="checkbox" name="notify" value="1"><span>Email the candidate that we'll keep their details on file</span></label>
    </div>
    <div class="modal__foot"><button class="btn btn--ghost" type="button" data-close-dialog>Cancel</button><button class="btn btn--primary" type="submit">Keep in pool</button></div>
  </form>
</dialog>
