<?php

namespace App\Commands;

libxml_use_internal_errors( true );

use phpQuery;
use \TypeRocket\Console\Command;

class CrawlLink extends Command {
	/**
	 * @var string
	 */
	protected $domain_url;

	/**
	 * @var string
	 */
	protected $pagination;

	protected $command = [
		'app:craw-link',
		'Short description here',
		'Longer help text goes here.',
	];

	protected function config() {
		// If you want to accept arguments
		// $this->addArgument('arg', self::REQUIRED, 'Description');
	}

	/**
	 * @throws \Exception
	 */
	public function exec() {
		$settings = $this->getCategorySettings();
		if ( count( $settings ) > 0 ) {
			$cateIds       = array_pluck( $settings, 'category_id' );
			$cateUrls      = $this->getCategoryUrls( $cateIds );
			$cateSelectors = $this->getCategorySelectors();
			$cateSettings  = $this->mapSelectors( $cateUrls, $cateSelectors );
			$cateSettings  = array_chunk( $this->mapPagination( $settings, $cateSettings ), 50 );
			foreach ( $cateSettings as $setting ) {
				$this->crawl( $setting );
			}
		}

		$settings = tr_get_model( 'CrawlSetting' );
		$settings->where( 'status', '<>', true )->update( [ 'status' => true ] );
		// When command executes
		echo 'Success';
	}

	/**
	 * Start crawl links
	 *
	 * @param array $categories
	 */
	protected function crawl( array $categories ) {
		$index = 0;
		do {
			$url              = array_get( $categories[ $index ], 'category_url' );
			$selector         = array_get( $categories[ $index ], 'selector' );
			$single_options   = array_get( $categories[ $index ], 'single_options' );
			$this->domain_url = array_get( $categories[ $index ], 'domain_url' );

			try {
				$html = phpQuery::newDocumentFileHTML( $url );
			} catch ( \Exception $exception ) {
				continue;
			}

			if ( ! $html ) {
				continue;
			}

			try {
				$elements = pq( $selector )->elements;
			} catch ( \Exception $exception ) {
				continue;
			}

			$links = [];
			if ( count( $elements ) > 0 ) {
				foreach ( $elements as $element ) {
					/**@var $element \DOMElement */
					$link    = $this->link( $element->getAttribute( 'href' ) );
					$links[] = sprintf( "('%s', '%s')", $link, esc_sql( $single_options ) );
				}
			}

			$this->createMany( $links );
			$this->deleteDuplicateRows();

			// Next
			$index ++;
		} while ( $index < count( $categories ) );
	}

	/**
	 * Create links by pagination
	 *
	 * @param array $settings
	 * @param array $categories
	 *
	 * @return array
	 */
	protected function mapPagination( array $settings, array $categories ) {
		$result = [];
		$index  = 0;
		while ( $index < count( $categories ) ) {
			$cate = $categories[ $index ];

			// Set default pagination if not setting
			$pagination = empty( $cate['pagination'] ) ? 'page' : $cate['pagination'];

			$matched = array_search( $cate['category_id'], array_column( $settings, 'category_id' ), true );

			if ( $matched > - 1 ) {
				$limit = (int) array_get( $settings[ $matched ], 'page', 1 );
				$links = [];
				for ( $i = 1; $i <= $limit; $i ++ ) {
					$value                 = $categories[ $matched ];
					$value['category_url'] = $value['category_url'] . "?{$pagination}=" . $i;
					$links[]               = $value;
				}
				$result[] = $links;
			}
			$index ++;
		}

		return array_merge( ...$result );
	}

	/**
	 * @param array $cateUrls
	 * @param array $cateSelectors
	 *
	 * @return array
	 */
	protected function mapSelectors( array $cateUrls, array $cateSelectors ) {
		$result = [];
		foreach ( $cateUrls as $key => $value ) {
			$haystack = array_column( $cateSelectors, 'id' );
			$needle   = $value['crawl_domain_id'];
			$matched  = array_search( $needle, $haystack, true );
			if ( $matched > - 1 ) {
				$value['selector']       = array_get( $cateSelectors[ $matched ], 'archive_options.selector' );
				$value['pagination']     = array_get( $cateSelectors[ $matched ], 'archive_options.pagination' );
				$value['single_options'] = array_get( $cateSelectors[ $matched ], 'single_options' );
				$value['domain_url']     = array_get( $cateSelectors[ $matched ], 'domain_url' );
				$result[]                = $value;
			}
		}

		return $result;
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	protected function getCategorySelectors() {
		$result  = [];
		$domains = tr_get_model( 'CrawlDomain' )
			->findAll()
			->select( 'id', 'archive_options', 'single_options', 'domain_url' )
			->get();

		foreach ( (array) $domains as $domain ) {
			$value['id']              = $domain->id;
			$value['domain_url']      = $domain->domain_url;
			$value['archive_options'] = $domain->archive_options;
			$value['single_options']  = array_get( $domain->getPropertiesUnaltered(), 'single_options' );
			$result[]                 = $value;
		}

		return $result;
	}

	/**
	 * @param array $cateIds
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected function getCategoryUrls( array $cateIds = [] ) {
		$result     = [];
		$categories = tr_get_model( 'CrawlCategory' )
			->where( 'id', 'IN', $cateIds )
			->select( 'crawl_domain_id', 'category_url', 'id' )
			->get();

		foreach ( $categories as $cate ) {
			$value['crawl_domain_id'] = data_get( $cate, 'crawl_domain_id' );
			$value['category_url']    = data_get( $cate, 'category_url' );
			$value['category_id']     = data_get( $cate, 'id' );
			$result[]                 = $value;
		}

		return $result;
	}

	/**
	 * Insert multiple row
	 *
	 * @param array $links
	 */
	protected function createMany( array $links ) {
		global $wpdb;
		$crawl_link_table = $wpdb->prefix . 'crawl_links';
		if ( count( $links ) > 0 ) {
			$sql = "INSERT INTO {$crawl_link_table} (`link`, `options`) VALUES " . implode( ',', $links ) . ';';
			$wpdb->query( $sql );
		}
	}

	protected function deleteDuplicateRows() {
		global $wpdb;
		$crawl_link_table = $wpdb->prefix . 'crawl_links';
		$sql              = "DELETE t1 FROM {$crawl_link_table} t1 INNER JOIN {$crawl_link_table} t2 WHERE t1.id < t2.id AND t1.link = t2.link;";
		$wpdb->query( $sql );
	}


	/**
	 * @return array
	 * @throws \Exception
	 */
	protected function getCategorySettings() {
		// Get all crawl settings
		$settings = tr_get_model( 'CrawlSetting' );
		$settings = (array) $settings->where( 'status', '<>', true )
		                             ->select( 'categories' )
		                             ->get();

		/**
		 * Map category_id and pagination
		 * @example ['category_id' => '5', 'page' => '1']
		 */
		$result = [];
		array_walk( $settings, function ( $setting ) use ( &$result ) {
			$result = array_merge( $result, $setting->categories );
		} );

		return $result;
	}

	/**
	 * @param $url string
	 *
	 * @return string
	 */
	private function link( string $url ) {
		if ( preg_match( '/^(https?:\/\/).*$/i', $url ) > 0 ) {
			return $url;
		}

		if ( preg_match( '/^(\/\/).*$/i', $url ) > 0 ) {
			return $url;
		}

		$scheme = parse_url( $this->domain_url, PHP_URL_SCHEME );
		$host   = parse_url( $this->domain_url, PHP_URL_HOST );

		return sprintf( '%s://%s/%s', $scheme, $host, trim( $url, '/' ) );
	}
}