<?php

$table   = tr_tables( 25, new \App\Models\CrawlDomain );
$add_url = tr_redirect()->toPage( 'crawl_domain', 'add' )->url;

$table->setColumns( 'domain_url', [
	'domain_url' => [
		'sort'    => true,
		'label'   => 'Domain URL',
		'actions' => [ 'edit', 'delete' ],
	],
	'id'         => [
		'label'    => 'Setting',
		'callback' => function ( $id, \App\Models\CrawlDomain $result ) {
			if ( $result ) {
				$route   = [
					'archive' => tr_redirect()->toPage( 'crawl_domain', 'archive', $id )->url,
					'single'  => tr_redirect()->toPage( 'crawl_domain', 'single', $id )->url,
				];
				$archive = ( new \TypeRocket\Html\Generator() )->newLink( 'Archive', $route['archive'] );
				$single  = ( new \TypeRocket\Html\Generator() )->newLink( 'Single', $route['single'] );

				return $single . ' | ' . $archive;
			}

			return '';
		},
	],
] );

?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?= __( 'All Domains' ) ?></h1>
    <a href="<?= $add_url ?>" class="page-title-action"><?= __( 'Add New' ) ?></a>
    <hr class="wp-header-end">
    <br>
	<?php $table->render(); ?>
</div>
