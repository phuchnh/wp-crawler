<?php

namespace App\Commands;

libxml_use_internal_errors( true );

use \TypeRocket\Console\Command;
use phpQuery;

class CrawData extends Command {

	protected $domain_url;

	protected $command = [
		'app:crawl-data',
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
		$links  = tr_get_model( 'CrawlLink' );
		$links  = $links->where( 'status', '<>', true );
		$chunks = array_chunk( (array) $links->get(), 50 );


		foreach ( $chunks as $chunk ) {
			$this->update( $chunk );
		}

		// When command executes
		$this->success( 'Execute!' );
	}

	/**
	 * @param array $rows
	 *
	 * @throws \Exception
	 */
	private function update( array $rows ) {
		$total = count( $rows );

		$result = [];
		$ids    = [];

		$index = 0;
		do {
			/**@var $model \App\Models\CrawlLink */
			$model = $rows[ $index ];
			preg_match( '/(https?:\/\/)(.*?)\//i', $model->link, $match );

			if ( ! is_array( $match ) || count( $match ) === 0 ) {
				continue;
			}

			$this->domain_url = $match[0];

			try {
				$html = phpQuery::newDocumentFile( $model->link );
			} catch ( \Exception $exception ) {
				continue;
			}


			if ( ! $html ) {
				continue;
			}

			$item = [];

			foreach ( $model->options as $option ) {
				$value    = null;
				$title    = array_get( $option, 'title' );
				$type     = array_get( $option, 'type' );
				$selector = array_get( $option, 'selector' );

				try {
					/**@var  $element \phpQueryObject */
					$element = pq( $selector );
				} catch ( \Exception $exception ) {
					continue;
				}

				if ( $element === null ) {
					$item[ $title ] = $value;
					continue;
				}

				if ( $type === 'image' ) {
					$value = $this->link( $element->filter( 'img' )->attr( 'src' ) );
				}

				if ( $type === 'text' ) {
					$value = trim( $element->text() );
				}

				if ( $type === 'html' ) {
					$value = $element->html();
				}

				$item[ $title ] = $value;
			}

			// TODO: Do something from data
			$result[] = $item;

			// Update table wp_crawl_links after get data
			$ids[] = $model->id;

			// Next loop
			$index ++;
		} while ( $index < $total );

		tr_get_model( 'CrawlLink' )->where( 'id', 'IN', $ids )->update( [ 'status' => true ] );
		$this->putCSV( [], $result );
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

		$scheme = parse_url( $this->domain_url, PHP_URL_SCHEME );
		$host   = parse_url( $this->domain_url, PHP_URL_HOST );

		return sprintf( '%s://%s/%s', $scheme, $host, trim( $url, '/' ) );
	}

	private function putCSV( $headers, $rows ) {
		$output = fopen( WP_CONTENT_DIR . '/crawl.csv', 'ab' );

		$data = fgetcsv( $output );
		if ( ! $data ) {
			fputcsv( $output, $headers );
		}

		foreach ( $rows as $row ) {
			// Add a new row with data
			fputcsv( $output, array_values( $row ) );
		}

		fclose( $output );
	}
}