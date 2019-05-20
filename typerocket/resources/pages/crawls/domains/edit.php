<?php
include_once __DIR__ . '/../shared/form.php';

/** @var $form \TypeRocket\Elements\Form */
$form->useOld();
?>
<div class="wrap">
	<?= set_form_title( $form, __( 'Edit Domain' ) ) ?>
	<?= $form->open() ?>
	<?= $form->text( 'domain_url' )->setLabel( 'Domain URL' ) ?>
	<?= set_form_submit( $form ) ?>
	<?= $form->close() ?>
</div>