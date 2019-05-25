<?php
/** @noinspection PhpUndefinedFunctionInspection */

/** @noinspection PhpUndefinedClassInspection */

namespace App\Controllers;

use mysql_xdevapi\Exception;
use phpQuery;
use App\Models\CrawlDomain;
use App\Models\CrawlPreview;
use \TypeRocket\Controllers\Controller;

class CrawlPreviewController extends Controller {

	protected $modelClass = CrawlPreview::class;
	protected $resource = 'crawl_preview';
	protected $domain_url;

	public function index() {
		$form = tr_form( $this->resource, 'handle' );

		return tr_view( 'crawls.previews.index', [ 'form' => $form ] );
	}

	/**
	 * @return \TypeRocket\Http\Response
	 * @throws \Exception
	 */
	public function handle() {
		libxml_use_internal_errors( true );

		/**
		 * @var $domain \App\Models\CrawlDomain
		 */
		$domain = new CrawlDomain();
		$domain = $domain->findById( $this->request->getFields( 'domain_id' ) );
		if ( $domain === null ) {
			$this->response->setMessage( 'Domain not found' );
			$this->response->exitNotFound();

			return $this->response;
		}

		$this->domain_url = $domain->domain_url;

		/**
		 * @var $category \App\Models\CrawlCategory
		 */
		$category = $domain->categories()->findById( $this->request->getFields( 'category_id' ) );
		if ( $category === null ) {
			$this->response->setMessage( 'Category not found' );
			$this->response->exitNotFound();

			return $this->response;
		}

		// Load HTML from URL
		try {
			phpQuery::newDocumentFileHTML( $category->category_url );
		} catch ( Exception $exception ) {
			$this->response->setMessage( 'Opp! Something went wrong.' );
			$this->response->exitNotFound();

			return $this->response;
		}

		// Get categories links
		$link_selector = $domain->archive_options['selector'];

		$links = pq( $link_selector )->map( function ( \DOMElement $element ) {
			return $this->link( $element->getAttribute( 'href' ) );
		} );

		$links = $links->elements;

		$data = [];
		$idx  = 0;

		do {
			// Load HTML
			$html = phpQuery::newDocumentFileHTML( $links[ $idx ] );
			if ( ! $html ) {
				continue;
			}

			$item = [];
			foreach ( $domain->single_options as $option ) {
				$value       = null;
				$key         = $option['title'];
				$type        = $option['type'];
				$selector    = $option['selector'];
				$item['URL'] = [
					'value' => $links[ $idx ],
					'type'  => 'link',
				];

				/**@var  $element \phpQueryObject */
				$element = pq( $selector );

				if ( $element === null ) {
					$item[ $key ] = [ 'value' => $value, 'type' => $type ];
					continue;
				}

				if ( $type === 'image' ) {
					$value = $this->link( $element->filter( 'img' )->attr( 'src' ) );
				}

				if ( $type === 'text' ) {
					$value = $element->text();
				}

				if ( $type === 'html' ) {
					$value = $element->html();
				}

				$item[ $key ] = [ 'value' => $value, 'type' => $type ];
			}

			$data[] = $item;
			$idx ++;

		} while ( $idx < 2 );

		$this->response->setData( 'resource', $data );
		$this->response->setMessage( 'Success' );
		$this->response->exitJson( 200 );

		return $this->response;
	}

	/**
	 * @param $url string
	 *
	 * @return string
	 */
	private function link( $url ) {
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
