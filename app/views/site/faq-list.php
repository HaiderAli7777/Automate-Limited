<?php /** Accordion of questions. @var array $items  [question, answer] pairs  @var bool|null $openFirst */ ?>
<div class="faq">
  <?php foreach ($items as $i => [$q, $a]): ?>
    <details<?= $i === 0 && !empty($openFirst) ? ' open' : '' ?>><summary><?= e($q) ?><svg class="ic" aria-hidden="true"><use href="#i-plus"/></svg></summary><div class="faq__a"><p><?= e($a) ?></p></div></details>
  <?php endforeach; ?>
</div>
