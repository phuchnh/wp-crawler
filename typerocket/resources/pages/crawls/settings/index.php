<?php
$table   = tr_tables( 25, new \App\Models\CrawlSetting );
$add_url = tr_redirect()->toPage( 'crawl_setting', 'add' )->url;
$table->setColumns( 'crawl_domain_id', [
	'crawl_domain_id' => [
		'sort'     => true,
		'label'    => 'Domain URL',
		'actions'  => [ 'edit', 'delete' ],
		'callback' => function ( $id, \App\Models\CrawlSetting $result ) {
			if ( $domain = $result->domain()->first() ) {
				$url = tr_redirect()->toPage( 'crawl_setting', 'edit', $result->id )->url;

				return ( new \TypeRocket\Html\Generator() )->newLink( $domain->domain_url, $url );
			}

			return '';
		},
	],
] );

?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?= __( 'All Settings' ) ?></h1>
    <a href="<?= $add_url ?>" class="page-title-action"><?= __( 'Add New' ) ?></a>
    <hr class="wp-header-end">
    <br>
	<?php $table->render(); ?>
</div>