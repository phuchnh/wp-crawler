<?php
include_once __DIR__ . '/../shared/form.php';

/** @var $form \TypeRocket\Elements\Form */
$form->useOld();
$select = $form->select( 'type' );
$select->setOptions( [ 'Text' => 'text', 'Image' => 'image', 'HTML' => 'html' ] );
$select->setLabel( 'Type' );
$select->setAttribute( 'class', 'select2' );

$repeater = $form->repeater( 'single_options' );
$repeater->setFields( [
	$form->row( $form->text( 'title' )->setLabel( 'Title' ), $select ),
	$form->text( 'selector' )->setLabel( 'Selector' ),
] );

$repeater->setLabel( 'Options' );
?>

<div class="wrap">
	<?= set_form_title( $form, __( 'Setting Detail Page' ) ) ?>
	<?= $form->open() ?>
	<?= $form->text( 'domain_url' )->setLabel( 'Domain URL' )->setAttribute( 'readonly', 'readonly' ) ?>
	<?= $repeater ?>
	<?= set_form_submit( $form ) ?>
	<?= $form->close() ?>
</div>

<script>
  (function($) {
    'use strict';
    $(function() {
      TypeRocket.repeaterCallbacks.push(function($template) {
        // noinspection JSUnresolvedFunction
        var $select = $template.find('select').select2({width: '100%'});
        // Remove unused element after re-init select2
        $select.last().next().next().remove();
      });
    });
  })(jQuery);
</script>

