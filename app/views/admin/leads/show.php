<?php
/** @var array $lead @var array $timeline @var array $others @var array $tasks @var array $templates */
$stages = lead_stages();
$current = $stages[(int) $lead['stage_id']] ?? null;
$reached = true;
$services = array_merge(lead_services(), ['Not sure yet']);
if ($lead['service'] && !in_array($lead['service'], $services, true)) { $services[] = $lead['service']; }
$hasUtm = $lead['utm_source'] || $lead['utm_medium'] || $lead['utm_campaign'] || $lead['referrer'] || $lead['landing_page'];
$followCls = task_due_class($lead['next_follow_up'], $lead['status'] !== 'open' ? 'x' : null);
$edit = user_can('crm.manage');
?>
<a class="crumb" href="<?= e(admin_url('leads')) ?>"><?= icon('arrow-left') ?>Leads</a>
<div class="profile">
  <div>
    <div class="row"><h1><?= e($lead['title']) ?></h1><?= stage_badge($current) ?><?= $lead['priority'] === 'high' ? badge('High priority', 'orange', 'badge--plain') : '' ?></div>
    <div class="profile__meta">
      <?php if ($lead['contact_name']): ?><span><?= icon('user-circle') ?><a class="link" href="<?= e(admin_url('contacts/' . $lead['contact_id'])) ?>"><?= e($lead['contact_name']) ?></a></span><?php endif; ?>
      <?php if ($lead['contact_company']): ?><span><?= icon('buildings') ?><?= e($lead['contact_company']) ?></span><?php endif; ?>
      <?php if ($lead['contact_email']): ?><span><?= icon('envelope-simple') ?><a class="link" href="mailto:<?= e($lead['contact_email']) ?>"><?= e($lead['contact_email']) ?></a></span><?php endif; ?>
      <?php if ($lead['contact_phone']): ?><span><?= icon('phone') ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $lead['contact_phone'])) ?>"><?= e($lead['contact_phone']) ?></a></span>
        <span><?= icon('chat-circle-text') ?><a class="link" href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', (string) $lead['contact_phone'])) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a></span><?php endif; ?>
    </div>
  </div>
  <?php if (!$edit): ?><div class="profile__actions"><span class="chip"><?= icon('eye') ?>View only</span></div><?php else: ?>
  <div class="profile__actions">
    <?php if ($lead['contact_email']): ?><button class="btn btn--quiet" type="button" data-open-dialog="leadEmailDialog"><?= icon('envelope-simple') ?>Email</button><?php endif; ?>
    <a class="btn btn--primary" href="#log"><?= icon('phone-call') ?>Log activity</a>
    <details class="dropdown">
      <summary class="btn btn--quiet btn--icon" aria-label="More actions"><?= icon('dots-three') ?></summary>
      <div class="dropdown__menu">
        <?php if ($lead['contact_id']): ?><a href="<?= e(admin_url('leads/new') . '?contact=' . $lead['contact_id']) ?>"><?= icon('plus') ?>New lead for this contact</a><?php endif; ?>
        <?php if (user_can('data.delete')): ?>
        <div class="dropdown__sep"></div>
        <form method="post" action="<?= e(admin_url('leads/' . $lead['id'] . '/delete')) ?>" data-confirm="Delete this lead and its history?"><?= csrf_field() ?><button type="submit" class="is-danger"><?= icon('trash') ?>Delete lead</button></form>
        <?php endif; ?>
      </div>
    </details>
  </div>
  <?php endif; ?>
</div>

<div class="panel" style="margin-bottom:18px"><div class="panel__body">
  <div class="stepper" role="group" aria-label="Move to stage">
    <?php foreach ($stages as $sid => $s):
        $isCurrent = (int) $sid === (int) $lead['stage_id'];
        $cls = ($isCurrent ? 'is-current' : ($reached && $s['kind'] === 'open' ? 'is-done' : '')) . ($s['kind'] === 'lost' ? ' is-negative' : ($s['kind'] === 'won' ? ' is-positive' : ''));
        if ($isCurrent) { $reached = false; }
    ?>
      <?php if (!$edit): ?>
        <button type="button" class="<?= e($cls) ?>" disabled<?= $isCurrent ? ' aria-current="step"' : '' ?>><?= e($s['name']) ?></button>
      <?php elseif ($s['kind'] === 'lost' && !$isCurrent): ?>
        <button type="button" class="<?= e($cls) ?>" data-open-dialog="lostOneDialog"><?= e($s['name']) ?></button>
      <?php else: ?>
        <form method="post" action="<?= e(admin_url('leads/' . $lead['id'] . '/stage')) ?>"><?= csrf_field() ?><input type="hidden" name="stage_id" value="<?= (int) $sid ?>">
          <button type="submit" class="<?= e($cls) ?>"<?= $isCurrent ? ' aria-current="step" disabled' : '' ?>><?= e($s['name']) ?></button></form>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php if ($lead['status'] === 'lost'): ?><p class="small muted" style="margin-top:10px">Lost<?= $lead['lost_reason'] ? ': ' . e($lead['lost_reason']) : '' ?>, <?= e(fmt_date($lead['lost_at'])) ?>.</p><?php endif; ?>
  <?php if ($lead['status'] === 'won'): ?><p class="small" style="margin-top:10px;color:var(--ok)">Won on <?= e(fmt_date($lead['won_at'])) ?>.</p><?php endif; ?>
</div></div>

<div class="split split--wide-side">
  <div class="stack">
    <?php if ($lead['message']): ?>
    <div class="panel">
      <div class="panel__head"><h2>The enquiry<?= $lead['topic'] ? ' <span class="chip chip--xs">' . e($lead['topic']) . '</span>' : '' ?></h2><span class="muted"><?= e(LEAD_SOURCES[$lead['source']] ?? $lead['source']) ?>, <?= e(fmt_datetime($lead['created_at'])) ?></span></div>
      <div class="panel__body"><p class="pre"><?= e($lead['message']) ?></p></div>
    </div>
    <?php endif; ?>

    <div class="panel" id="timeline">
      <div class="panel__head"><h2>Activity</h2><span class="muted">Calls, meetings, emails and notes</span></div>
      <div class="panel__body">
        <?php if ($edit): ?>
        <form method="post" action="<?= e(admin_url('leads/' . $lead['id'] . '/activity')) ?>" class="composer" id="log" style="margin-bottom:22px">
          <?= csrf_field() ?>
          <div class="composer__kinds" role="radiogroup" aria-label="What are you logging?">
            <?php foreach (ACTIVITY_KINDS as $k => $label): ?><label><input type="radio" name="kind" value="<?= $k ?>"<?= $k === 'call' ? ' checked' : '' ?>><?= icon(activity_icon($k)) ?><?= e($label) ?></label><?php endforeach; ?>
          </div>
          <textarea class="textarea" name="body" rows="3" placeholder="What was said, what was agreed, what happens next" aria-label="Details" required></textarea>
          <div class="row">
            <label class="small muted" for="log-follow">Next follow-up</label>
            <input class="input input--sm" id="log-follow" type="datetime-local" name="next_follow_up" style="width:auto">
            <span class="spacer"></span>
            <button class="btn btn--primary btn--sm" type="submit">Save</button>
          </div>
        </form>
        <?php endif; ?>
        <?php partial('admin/partials/timeline', ['items' => $timeline]); ?>
      </div>
    </div>
  </div>

  <div class="stack">
    <?php if (!$edit): ?>
    <div class="panel">
      <div class="panel__head"><h2>Deal</h2></div>
      <div class="panel__body"><dl class="dl">
        <dt>Value</dt><dd><?= $lead['value'] !== null ? e(fmt_money($lead['value'], $lead['currency'])) : '-' ?></dd>
        <dt>Service</dt><dd><?= e($lead['service'] ?: '-') ?><?= $lead['topic'] ? ', ' . e($lead['topic']) : '' ?></dd>
        <dt>Source</dt><dd><?= e(LEAD_SOURCES[$lead['source']] ?? $lead['source']) ?></dd>
        <dt>Priority</dt><dd><?= e(LEAD_PRIORITIES[$lead['priority']] ?? $lead['priority']) ?></dd>
        <dt>Owner</dt><dd><?= $lead['owner_id'] ? e(user_name((int) $lead['owner_id'])) : 'Unassigned' ?></dd>
        <dt>Next follow-up</dt><dd><?= $lead['next_follow_up'] ? e(fmt_datetime($lead['next_follow_up'])) : '-' ?></dd>
        <dt>Expected close</dt><dd><?= $lead['expected_close'] ? e(fmt_date($lead['expected_close'])) : '-' ?></dd>
      </dl></div>
    </div>
    <?php else: ?>
    <form method="post" action="<?= e(admin_url('leads/' . $lead['id'] . '/update')) ?>" class="panel">
      <?= csrf_field() ?>
      <div class="panel__head"><h2>Deal</h2><?php if ($lead['value'] !== null && $current && $current['kind'] === 'open'): ?><span class="muted">Weighted <?= e(fmt_money(weighted_value($lead), $lead['currency'])) ?></span><?php endif; ?></div>
      <div class="panel__body stack-sm">
        <?= fi('title', 'Title', $lead['title'], ['required' => true]) ?>
        <div class="grid-2 grid-tight">
          <?= fi('value', 'Value', $lead['value'] !== null ? (string) (float) $lead['value'] : '', ['attrs' => ['inputmode' => 'decimal']]) ?>
          <?= fs('currency', 'Currency', array_combine(currencies(), currencies()), $lead['currency'] ?: setting('default_currency', 'PKR')) ?>
        </div>
        <div class="grid-2 grid-tight">
          <?= fs('service', 'Service', array_combine($services, $services), (string) $lead['service']) ?>
          <?= fi('topic', 'Module or topic', (string) $lead['topic'], ['optional' => true, 'placeholder' => 'For example Inventory']) ?>
          <?= fs('source', 'Source', LEAD_SOURCES, $lead['source']) ?>
          <?= fs('priority', 'Priority', LEAD_PRIORITIES, $lead['priority']) ?>
          <?= fs('owner_id', 'Owner', user_options($lead['owner_id'] ? (int) $lead['owner_id'] : null, 'Unassigned', 'crm.manage')) ?>
        </div>
        <?= fi('next_follow_up', 'Next follow-up', dt_input($lead['next_follow_up']), ['type' => 'datetime-local', 'class' => $followCls === 'is-overdue' ? 'has-error' : '', 'help' => $followCls === 'is-overdue' ? 'Overdue.' : '']) ?>
        <?= fi('expected_close', 'Expected close', (string) $lead['expected_close'], ['type' => 'date']) ?>
        <div><button class="btn btn--quiet btn--sm" type="submit">Save deal</button></div>
      </div>
    </form>
    <?php endif; ?>

    <?php partial('admin/partials/tasks-panel', ['entityType' => 'lead', 'entityId' => (int) $lead['id'], 'tasks' => $tasks]); ?>

    <?php if ($hasUtm): ?>
    <div class="panel">
      <div class="panel__head"><h2>How they found you</h2></div>
      <div class="panel__body"><dl class="dl small">
        <?php if ($lead['utm_source']): ?><dt>Campaign source</dt><dd><?= e($lead['utm_source']) ?></dd><?php endif; ?>
        <?php if ($lead['utm_medium']): ?><dt>Medium</dt><dd><?= e($lead['utm_medium']) ?></dd><?php endif; ?>
        <?php if ($lead['utm_campaign']): ?><dt>Campaign</dt><dd><?= e($lead['utm_campaign']) ?></dd><?php endif; ?>
        <?php if ($lead['referrer']): ?><dt>Came from</dt><dd><?= e($lead['referrer']) ?></dd><?php endif; ?>
        <?php if ($lead['landing_page']): ?><dt>First page</dt><dd><?= e($lead['landing_page']) ?></dd><?php endif; ?>
      </dl></div>
    </div>
    <?php endif; ?>

    <?php if ($others): ?>
    <div class="panel">
      <div class="panel__head"><h2>Other leads from <?= e(explode(' ', (string) $lead['contact_name'])[0]) ?></h2></div>
      <div class="list"><?php foreach ($others as $o): ?><div class="list__item"><div class="list__main"><a class="list__title" href="<?= e(admin_url('leads/' . $o['id'])) ?>"><?= e($o['title']) ?></a><span class="list__sub"><?= e(fmt_date($o['created_at'])) ?><?= $o['value'] !== null ? ' · ' . e(fmt_money($o['value'], $o['currency'])) : '' ?></span></div><?= stage_badge($stages[(int) $o['stage_id']] ?? null) ?></div><?php endforeach; ?></div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($edit && $lead['contact_email']): ?>
<dialog class="modal modal--wide" id="leadEmailDialog" aria-labelledby="leadEmailTitle">
  <form method="post" action="<?= e(admin_url('leads/' . $lead['id'] . '/email')) ?>">
    <?= csrf_field() ?>
    <div class="modal__head"><h2 id="leadEmailTitle">Email <?= e((string) $lead['contact_name']) ?></h2><button class="btn btn--ghost btn--icon" type="button" data-close-dialog aria-label="Close"><?= icon('x') ?></button></div>
    <div class="modal__body">
      <div class="field"><label for="le-tpl">Start from a template</label><select class="select" id="le-tpl" data-template-picker data-preview-url="<?= e(admin_url('leads/' . $lead['id'] . '/email-preview')) ?>"><option value="">Choose a template</option><?php foreach ($templates as $t): ?><option value="<?= e($t['tkey']) ?>"><?= e($t['name']) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label for="le-subject">Subject</label><input class="input" id="le-subject" name="subject" required value="Re: your enquiry with <?= e(company_name()) ?>"></div>
      <div class="field"><label for="le-body">Message</label><textarea class="textarea textarea--tall" id="le-body" name="body" required></textarea><p class="help">Replies go to <?= e(auth_user()['email']) ?>. The email is saved to the activity log.</p></div>
    </div>
    <div class="modal__foot"><button class="btn btn--ghost" type="button" data-close-dialog>Cancel</button><button class="btn btn--primary" type="submit" data-busy="Sending…"><?= icon('envelope-simple') ?>Send</button></div>
  </form>
</dialog>
<?php endif; ?>
<?php if ($edit) partial('admin/partials/lost-dialog', ['id' => 'lostOneDialog', 'method' => 'post', 'action' => admin_url('leads/' . $lead['id'] . '/stage'), 'stageId' => first_stage_id($stages, 'lost')]); ?>
