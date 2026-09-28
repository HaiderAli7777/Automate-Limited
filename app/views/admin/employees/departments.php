<?php /** @var array $rows */ $edit = user_can('hr.manage'); $pay = user_can('payroll.manage'); ?>
<div class="phead">
  <div><h1>Departments</h1><p class="phead__sub">How the team is organised. Payroll can run for one department at a time.</p></div>
</div>
<div class="split">
  <div class="panel">
    <?php if (!$rows): ?>
      <div class="empty"><?= icon('buildings') ?><h3>No departments yet.</h3><p>Add your first one on the right. Departments are also created automatically when you convert a hire whose job has one.</p></div>
    <?php else: ?>
    <div class="table-wrap"><table class="tbl">
      <thead><tr><th>Department</th><th>Head</th><th class="num">People</th><?php if ($pay): ?><th class="num">Monthly basic</th><?php endif; ?><?php if ($edit): ?><th><span class="sr-only">Actions</span></th><?php endif; ?></tr></thead>
      <tbody>
      <?php foreach ($rows as $d): ?>
        <tr>
          <td><a class="t-strong" href="<?= e(admin_url('employees') . '?department=' . $d['id']) ?>"><?= e($d['name']) ?></a></td>
          <td><?= $d['head_name'] ? '<a class="link" href="' . e(admin_url('employees/' . $d['head_employee_id'])) . '">' . e($d['head_name']) . '</a>' : '<span class="muted">-</span>' ?></td>
          <td class="num"><?= (int) $d['headcount'] ?></td>
          <?php if ($pay): ?><td class="num"><?= e(compact_money($d['basic_total'])) ?></td><?php endif; ?>
          <?php if ($edit): ?>
          <td class="t-right">
            <details class="dropdown">
              <summary class="btn btn--ghost btn--icon" aria-label="Edit <?= e($d['name']) ?>"><?= icon('dots-three') ?></summary>
              <div class="dropdown__menu dropdown__menu--form">
                <form method="post" action="<?= e(admin_url('departments/' . $d['id'])) ?>" class="stack-sm" style="padding:8px">
                  <?= csrf_field() ?>
                  <label class="small t-strong" for="dn-<?= (int) $d['id'] ?>">Name</label>
                  <input class="input input--sm" id="dn-<?= (int) $d['id'] ?>" name="name" value="<?= e($d['name']) ?>" required maxlength="80">
                  <label class="small t-strong" for="dh-<?= (int) $d['id'] ?>">Head</label>
                  <select class="select select--sm" id="dh-<?= (int) $d['id'] ?>" name="head_employee_id"><?= employee_options($d['head_employee_id'] ? (int) $d['head_employee_id'] : null) ?></select>
                  <button class="btn btn--primary btn--sm" type="submit">Save</button>
                </form>
                <?php if (!(int) $d['headcount']): ?>
                <div class="dropdown__sep"></div>
                <form method="post" action="<?= e(admin_url('departments/' . $d['id'] . '/delete')) ?>" data-confirm="Delete <?= e($d['name']) ?>?"><?= csrf_field() ?><button type="submit" class="is-danger"><?= icon('trash') ?>Delete department</button></form>
                <?php endif; ?>
              </div>
            </details>
          </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php endif; ?>
  </div>
  <?php if ($edit): ?>
  <form method="post" action="<?= e(admin_url('departments')) ?>" class="panel">
    <?= csrf_field() ?>
    <div class="panel__head"><h2>Add a department</h2></div>
    <div class="panel__body stack-sm">
      <?= fi('name', 'Name', '', ['required' => true, 'placeholder' => 'Odoo delivery']) ?>
      <?= fs('head_employee_id', 'Head', employee_options(null), '', ['optional' => true]) ?>
      <div><button class="btn btn--primary btn--sm" type="submit"><?= icon('plus') ?>Add department</button></div>
    </div>
  </form>
  <?php endif; ?>
</div>
