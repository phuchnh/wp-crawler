<?php
include_once __DIR__ . '/../shared/form.php';

/** @var $form \TypeRocket\Elements\Form */
$form->useOld();
$domain_url = $form->text( 'domain_url' )->setLabel( 'Domain URL' )->setAttribute( 'readonly', 'readonly' );
$selector   = $form->text( 'selector' )->setLabel( 'Selector' )->setGroup( 'archive_options' );
$pagination = $form->text( 'pagination' )->setLabel( 'Pagination' )->setGroup( 'archive_options' )->setDefault( 'page' );
?>

<div class="wrap">
	<?= set_form_title( $form, __( 'Setting Category Page' ) ) ?>
	<?= $form->open() ?>
	<?= $domain_url ?>
	<?= $selector ?>
	<?= $pagination ?>
	<?= set_form_submit( $form ) ?>
	<?= $form->close() ?>
</div>
