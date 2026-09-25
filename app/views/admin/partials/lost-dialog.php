<?php /** @var string $id @var string $method 'dialog' for the board, 'post' for a page @var string|null $action @var int|null $stageId */ ?>
<dialog class="modal" id="<?= e($id) ?>" aria-labelledby="<?= e($id) ?>-t">
  <form method="<?= e($method) ?>"<?= !empty($action) ? ' action="' . e($action) . '"' : '' ?>>
    <?php if ($method === 'post'): ?><?= csrf_field() ?><input type="hidden" name="stage_id" value="<?= (int) $stageId ?>"><?php endif; ?>
    <div class="modal__head"><h2 id="<?= e($id) ?>-t">Why was this lost?</h2></div>
    <div class="modal__body">
      <div class="field"><label for="<?= e($id) ?>-r">Reason</label><select class="select" id="<?= e($id) ?>-r" name="reason"><?php foreach (LOST_REASONS as $r): ?><option><?= e($r) ?></option><?php endforeach; ?></select><p class="help">The CRM dashboard shows which reasons come up most.</p></div>
    </div>
    <div class="modal__foot"><button class="btn btn--ghost" type="button" data-close-dialog>Cancel</button><button class="btn btn--primary" type="submit">Mark as lost</button></div>
  </form>
</dialog>
