<?php

/**
 * @param \TypeRocket\Elements\Form $form
 *
 * @param string $name
 *
 * @return \TypeRocket\Elements\Fields\Select
 */
function get_options_crawl_domain( $form, $name = 'crawl_domain_id' ) {
	$select = $form->select( 'crawl_domain_id' )->setLabel( 'Domain URL' );
	$select->setModelOptions( new \App\Models\CrawlDomain, 'domain_url', 'id' );
	$select->setAttribute( 'id', 'domain' );
	$select->setAttribute( 'class', 'select2' );

	return $select;
}
