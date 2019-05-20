<?php

/**
 * @param $form \TypeRocket\Elements\Form
 * @param $title string
 * @param $add_new_label string
 *
 * @return string
 */
function set_form_title( $form, $title, $add_new_label = 'Add New' ) {
	$add_url = tr_redirect()->toPage( $form->getResource(), 'add' )->url;

	return <<<HTML
<h1 class="wp-heading-inline">{$title}</h1>
<a href="{$add_url}" class="page-title-action">{$add_new_label}</a>
<hr class="wp-header-end">
<br>
HTML;
}


/**
 * @param $form \TypeRocket\Elements\Form
 * @param string $submit_label
 * @param string $back_label
 *
 * @return string
 */
function set_form_submit( $form, $submit_label = 'Save', $back_label = 'Back' ) {
	$list_url = tr_redirect()->toPage( $form->getResource(), 'index' )->url;
	if ( ! $form ) {
		return '';
	}

	$submit = $form->submit( $submit_label )->setRenderSetting( 'raw' );

	return <<<HTML
<div class="control-section typerocket-elements-fields-submit">
	<div class="control">
		{$submit}
		<a class="button" href="{$list_url}">{$back_label}</a>
	</div>
</div>
HTML;
}
