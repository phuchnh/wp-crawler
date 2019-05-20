<?php
$table   = tr_tables( 25, new \App\Models\CrawlCategory );
$add_url = tr_redirect()->toPage( 'crawl_category', 'add' )->url;
$table->setColumns( 'category_url', [
	'category_url'    => [
		'sort'    => true,
		'label'   => 'Category URL',
		'actions' => [ 'edit', 'delete' ],
	],
	'crawl_domain_id' => [
		'sort'     => true,
		'label'    => 'Domain URL',
		'callback' => function ( $id, \App\Models\CrawlCategory $result ) {
			if ( $domain = $result->domain()->first() ) {
				$url = tr_redirect()->toPage( 'crawl_domain', 'edit', $id )->url;

				return ( new \TypeRocket\Html\Generator() )->newLink( $domain->domain_url, $url );
			}

			return '';
		},
	],
] );
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?= __( 'All Categories' ) ?></h1>
    <a href="<?= $add_url ?>" class="page-title-action"><?= __( 'Add New' ) ?></a>
    <hr class="wp-header-end">
    <br>
	<?php $table->render(); ?>
</div>
