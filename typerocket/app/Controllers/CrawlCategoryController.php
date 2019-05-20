<?php /** @noinspection PhpUndefinedFieldInspection */

namespace App\Controllers;

use App\Models\CrawlCategory;
use App\Models\CrawlDomain;
use \TypeRocket\Controllers\Controller;

class CrawlCategoryController extends Controller {

	protected $modelClass = CrawlCategory::class;
	protected $resource = 'crawl_category';

	/**
	 * The index page for admin
	 *
	 * @return mixed
	 */
	public function index() {
		return tr_view( 'crawls.categories.index' );
	}

	/**
	 * The add page for admin
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function add() {
		$form = tr_form( $this->resource, 'create' );

		return tr_view( 'crawls.categories.add', [
			'form' => $form,
		] );
	}

	/**
	 * Create item
	 *
	 * AJAX requests and normal requests can be made to this action
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function create() {

		$options = [
			'category_url' => 'required|unique:category_url:wp_crawl_categories',
		];

		$validator = tr_validator( $options, $this->request->getFields() );

		if ( $validator->getErrors() ) {
			$validator->flashErrors( $this->response );

			return tr_redirect()->toPage( $this->resource, 'add' )
			                    ->withFields( $this->request->getFields() );
		}

		$crawl_domain = new CrawlDomain;
		$crawl_domain->findById( $this->request->getFields( 'crawl_domain_id' ) );

		if ( ! $crawl_domain ) {
			$this->response->flashNext( 'Domain not found', 'error' );
			$this->response->withFields( $this->request->getFields() );

			return tr_redirect()->toPage( $this->resource, 'add' );
		}

		$crawl_category                  = new CrawlCategory;
		$crawl_category->crawl_domain_id = $crawl_domain->id;
		$crawl_category->category_url    = $this->request->getFields( 'category_url' );

		if ( ! $success = (boolean) $crawl_category->save() ) {
			$this->response->flashNext( 'Category create failure', 'error' );

			return tr_redirect()->toPage( $this->resource, 'index' );
		}

		$this->response->flashNext( 'Category created!' );

		return tr_redirect()->toPage( $this->resource, 'index' );
	}

	/**
	 * The edit page for admin
	 *
	 * @param $id
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function edit( $id ) {
		$form = tr_form( $this->resource, 'update', $id );

		return tr_view( 'crawls.categories.edit', [ 'form' => $form ] );
	}

	/**
	 * Update item
	 *
	 * AJAX requests and normal requests can be made to this action
	 *
	 * @param string $id
	 *
	 * @param CrawlCategory $crawl_category
	 *
	 * @return mixed
	 */
	public function update( $id, CrawlCategory $crawl_category ) {

		$options = [
			'category_url' => 'required|unique:category_url:wp_crawl_domains@id:' . $crawl_category->id,
		];

		$validator = tr_validator( $options, $this->request->getFields() );

		if ( $validator->getErrors() ) {
			$validator->flashErrors( $this->response );

			return tr_redirect()->toPage( $this->resource, 'edit', $crawl_category->id )
			                    ->withFields( $this->request->getFields() );
		}
		$crawl_domain = new CrawlDomain;
		$crawl_domain->findById( $this->request->getFields( 'crawl_domain_id' ) );

		if ( ! $crawl_domain ) {
			$this->response->flashNext( 'Domain not found', 'error' );
			$this->response->withFields( $this->request->getFields() );

			return tr_redirect()->toPage( $this->resource, 'edit', $id );
		}

		$crawl_category->category_url    = $this->request->getFields( 'category_url' );
		$crawl_category->crawl_domain_id = $crawl_domain->id;
		$crawl_category->save();
		$this->response->flashNext( 'Category updated!' );

		return tr_redirect()->toPage( $this->resource, 'index' );
	}


	/**
	 * The option page for admin
	 *
	 * @param string $id
	 *
	 * @return mixed
	 */
	public function edit_option( $id ) {
		$form = tr_form( $this->resource, 'update_option', $id );

		return tr_view( 'crawls.categories.option', [ 'form' => $form ] );
	}

	/**
	 * The option page for admin
	 *
	 * AJAX requests and normal requests can be made to this action
	 *
	 * @param CrawlCategory $crawl_category
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function update_option( CrawlCategory $crawl_category ) {
		$crawl_category->category_options = $this->request->getFields( 'category_options' );
		$crawl_category->save();
		$this->response->flashNext( 'Setting updated!' );

		return tr_redirect()->toPage( $this->resource, 'edit_option', $crawl_category->id );
	}

	/**
	 * Destroy item
	 *
	 * AJAX requests and normal requests can be made to this action
	 *
	 * @param \App\Models\CrawlCategory $crawl_category
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function destroy( CrawlCategory $crawl_category ) {
		if ( ! $success = (boolean) $crawl_category->delete() ) {
			return $this->response->flashNext( 'Category deleted failure!', 'error' );
		}
		$this->response->flashNext( 'Category deleted!' );

		return tr_redirect()->toPage( $this->resource, 'index' );
	}
}