<?php
include_once __DIR__ . '/../shared/form.php';

/** @var $form \TypeRocket\Elements\Form */
$form->useOld();

$domains = $form->select( 'crawl_domain_id' )->setLabel( 'Domain URL' );
$domains->setModelOptions( new \App\Models\CrawlDomain, 'domain_url', 'id' );
$domains->setAttribute( 'class', 'select2' );

$category_url = $form->text( 'category_url' )->setLabel( 'Category URL' );
?>

<div class="wrap">
	<?= set_form_title( $form, __( 'Add Category' ) ) ?>
	<?= $form->open() ?>
	<?= $domains ?>
	<?= $category_url ?>
	<?= set_form_submit( $form ) ?>
	<?= $form->close() ?>
</div>
