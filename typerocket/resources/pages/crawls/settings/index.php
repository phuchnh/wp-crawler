<?php
/** @noinspection PhpUndefinedVariableInspection */
/**@var $form \TypeRocket\Elements\Form */
$form->useAjax();

$table = tr_tables(25, new \App\Models\CrawlSetting);
$add_url = tr_redirect()->toPage('crawl_setting', 'add')->url;

$table->setColumns('crawl_domain_id', [
    'crawl_domain_id' => [
        'sort' => true,
        'label' => 'Domain URL',
        'actions' => ['edit', 'delete'],
        'callback' => function ($id, \App\Models\CrawlSetting $result) {
            if ($domain = $result->domain()->first()) {
                $url = tr_redirect()->toPage('crawl_setting', 'edit', $result->id)->url;

                return (new \TypeRocket\Html\Generator())->newLink($domain->domain_url, $url);
            }

            return '';
        },
    ],
]);

?>

<div class="wrap">
  <h1 class="wp-heading-inline"><?=__('All Settings')?></h1>
  <a href="<?=$add_url?>" class="page-title-action"><?=__('Add New')?></a>
  <hr class="wp-header-end">
  <br>
  <div class="control-section">
      <?=$form->open(['id' => 'frm_status'])?>
    <label class="switch">
      <input type="checkbox" value="1" name="enable" <?= (bool) $crawl_status ? 'checked' : ''?>
             id="switch_status">
      <span class="slider round"></span>
    </label>
    <strong><?=__('Enable Crawl')?></strong>
      <?=$form->close()?>
  </div>
    <?php $table->render(); ?>
</div>

<script>
  (function($) {
    'use strict';
    $('#switch_status').change(function(e) {
      e.preventDefault();
      $('#frm_status').submit();
    });
  })(jQuery);
</script>