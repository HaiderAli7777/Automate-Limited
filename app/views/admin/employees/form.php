<?php
/** @var array|null $emp @var bool $isNew @var array|null $source @var string $code */
$e = $emp ?? [];
$pay = user_can('payroll.manage');
$val = static fn (string $k, $default = '') => $e[$k] ?? $default;
?>
<a class="crumb" href="<?= e($isNew ? admin_url('employees') : admin_url('employees/' . $e['id'])) ?>"><?= icon('arrow-left') ?><?= $isNew ? 'Employees' : e(employee_name($e)) ?></a>
<div class="phead">
  <div>
    <h1><?= $isNew ? ($source ? 'Convert ' . e(candidate_name($source)) . ' to an employee' : 'Add an employee') : 'Edit ' . e(employee_name($e)) ?></h1>
    <p class="phead__sub"><?= $source ? 'Details from their application for ' . e($source['job_title']) . ' are filled in. Check them, add the rest, and save.' : 'Job and contact details, pay and bank details, and an emergency contact.' ?></p>
  </div>
</div>

<form method="post" action="<?= e($isNew ? admin_url('employees/new') : admin_url('employees/' . $e['id'] . '/edit')) ?>" class="split">
  <?= csrf_field() ?>
  <?php if ($isNew && !empty($e['application_id'])): ?><input type="hidden" name="application_id" value="<?= (int) $e['application_id'] ?>"><?php endif; ?>
  <div class="stack">
    <div class="panel"><div class="panel__body">
      <fieldset class="fieldset">
        <legend>Job</legend>
        <div class="grid-2">
          <?= fi('first_name', 'First name', $val('first_name'), ['required' => true]) ?>
          <?= fi('last_name', 'Last name', $val('last_name')) ?>
          <?= fi('employee_code', 'Employee code', $isNew ? $code : $val('employee_code'), ['help' => 'Shown on payslips. Leave as suggested or use your own.']) ?>
          <?= fi('designation', 'Designation', $val('designation'), ['placeholder' => 'For example Odoo Functional Consultant']) ?>
          <?= fs('department_id', 'Department', '<option value="">No department</option>' . options(departments(), fval('department_id', $val('department_id')))) ?>
          <?= fs('manager_id', 'Reports to', employee_options(($m = fval('manager_id', $val('manager_id'))) !== '' ? (int) $m : null, 'Nobody', $isNew ? null : (int) $e['id'])) ?>
          <?= fs('employment_type', 'Employment type', EMPLOYMENT_TYPES, $val('employment_type', 'full_time')) ?>
          <?= fi('work_location', 'Work location', $val('work_location'), ['optional' => true, 'placeholder' => 'Lahore office, remote']) ?>
          <?= fi('join_date', 'Joining date', $val('join_date', today()), ['type' => 'date', 'required' => true]) ?>
          <?= fi('probation_end', 'Probation ends', $val('probation_end'), ['type' => 'date', 'optional' => true]) ?>
          <?= fs('status', 'Status', EMPLOYEE_STATUSES, $val('status', 'probation'), ['attrs' => ['data-status-select' => true]]) ?>
        </div>
        <div class="grid-2" data-exit-fields<?= fval('status', $val('status', 'probation')) === 'left' ? '' : ' hidden' ?>>
          <?= fi('exit_date', 'Last working day', $val('exit_date'), ['type' => 'date']) ?>
          <?= fs('exit_reason', 'Reason for leaving', ['' => 'Choose a reason'] + array_combine(EXIT_REASONS, EXIT_REASONS), $val('exit_reason')) ?>
        </div>
      </fieldset>
      <fieldset class="fieldset">
        <legend>Contact and personal</legend>
        <div class="grid-2">
          <?= fi('email', 'Email', $val('email'), ['type' => 'email', 'help' => 'Payslips are sent here.']) ?>
          <?= fi('phone', 'Phone', $val('phone'), ['type' => 'tel']) ?>
          <?= fi('cnic', 'CNIC or national ID', $val('cnic'), ['optional' => true, 'placeholder' => '35202-1234567-1']) ?>
          <?= fi('date_of_birth', 'Date of birth', $val('date_of_birth'), ['type' => 'date', 'optional' => true]) ?>
          <?= fs('gender', 'Gender', GENDERS, $val('gender')) ?>
          <?= fs('marital_status', 'Marital status', MARITAL_STATUSES, $val('marital_status')) ?>
          <?= fi('city', 'City', $val('city'), ['optional' => true]) ?>
        </div>
        <?= ft('address', 'Home address', $val('address'), ['optional' => true, 'attrs' => ['rows' => 2]]) ?>
      </fieldset>
      <fieldset class="fieldset">
        <legend>Emergency contact</legend>
        <div class="grid-3">
          <?= fi('emergency_name', 'Name', $val('emergency_name'), ['optional' => true]) ?>
          <?= fi('emergency_relation', 'Relation', $val('emergency_relation'), ['optional' => true, 'placeholder' => 'Spouse, parent']) ?>
          <?= fi('emergency_phone', 'Phone', $val('emergency_phone'), ['optional' => true, 'type' => 'tel']) ?>
        </div>
      </fieldset>
    </div></div>
  </div>

  <div class="stack">
    <?php if ($pay): ?>
    <div class="panel">
      <div class="panel__head"><h2>Pay</h2><span class="muted small">Only people with payroll access see this</span></div>
      <div class="panel__body stack-sm">
        <div class="grid-2 grid-tight">
          <?= fi('basic_salary', 'Monthly basic salary', $val('basic_salary') !== '' ? (string) (float) $val('basic_salary') : '', ['attrs' => ['inputmode' => 'decimal'], 'help' => 'Payroll uses this every month.']) ?>
          <?= fs('currency', 'Currency', array_combine(currencies(), currencies()), $val('currency', setting('default_currency', 'PKR'))) ?>
        </div>
        <?= fi('bank_name', 'Bank', $val('bank_name'), ['optional' => true, 'placeholder' => 'Meezan Bank']) ?>
        <?= fi('bank_account_title', 'Account title', $val('bank_account_title'), ['optional' => true]) ?>
        <?= fi('bank_iban', 'IBAN or account number', $val('bank_iban'), ['optional' => true]) ?>
        <?= fi('tax_number', 'Tax number (NTN)', $val('tax_number'), ['optional' => true]) ?>
        <p class="help">Recurring allowances and deductions (house rent, fuel, loan repayment) are set on the profile after saving.</p>
      </div>
    </div>
    <?php endif; ?>
    <div class="panel">
      <div class="panel__head"><h2>Notes</h2></div>
      <div class="panel__body"><?= ft('notes', 'Private HR notes', $val('notes'), ['optional' => true, 'attrs' => ['rows' => 4]]) ?></div>
    </div>
    <div class="form-actions" style="margin-top:0">
      <button class="btn btn--primary" type="submit"><?= icon('check') ?><?= $isNew ? 'Create employee' : 'Save changes' ?></button>
      <a class="btn btn--ghost" href="<?= e($isNew ? admin_url('employees') : admin_url('employees/' . $e['id'])) ?>">Cancel</a>
    </div>
  </div>
</form>
