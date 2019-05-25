<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace App\Controllers;

use App\Models\CrawlSetting;
use \TypeRocket\Controllers\Controller;

class CrawlSettingController extends Controller {

	protected $modelClass = CrawlSetting::class;

	protected $resource = 'crawl_setting';

	/**
	 * The index page for admin
	 *
	 * @return mixed
	 */
	public function index() {
        $form = tr_form('crawl_setting', 'enable');
		return tr_view( 'crawls.settings.index' , [
		    'form' => $form,
		    'crawl_status' => $this->get_crawl_status()
        ]);
	}

	/**
	 * The add page for admin
	 *
	 * @return mixed
	 */
	public function add() {
		$form = tr_form( $this->resource, 'create' );

		return tr_view( 'crawls.settings.add', [ 'form' => $form ] );
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
			'crawl_domain_id' => 'required|unique:crawl_domain_id:wp_crawl_settings',
		];

		$validator = tr_validator( $options, $this->request->getFields() );

		if ( $validator->getErrors() ) {
			$validator->flashErrors( $this->response );

			return tr_redirect()->toPage( $this->resource, 'add' )
			                    ->withFields( $this->request->getFields() );
		}

		$crawl_setting                  = new CrawlSetting();
		$crawl_setting->crawl_domain_id = $this->request->getFields( 'crawl_domain_id' );
		$crawl_setting->categories      = $this->request->getFields( 'categories' );
		// $crawl_setting->options         = $this->request->getFields('options');
		$crawl_setting->save();
		$this->response->flashNext( 'Success!' );

		return tr_redirect()->toPage( $this->resource, 'index' );
	}

	/**
	 * The edit page for admin
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public function edit( $id ) {
		$form = tr_form( $this->resource, 'update', $id );

		return tr_view( 'crawls.settings.edit', [ 'form' => $form ] );
	}

	/**
	 * Update item
	 *
	 * AJAX requests and normal requests can be made to this action
	 *
	 * @param \App\Models\CrawlSetting $crawl_setting
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function update( CrawlSetting $crawl_setting ) {

		$options = [
			'crawl_domain_id' => 'required|unique:crawl_domain_id:wp_crawl_settings@id:' . $crawl_setting->id,
		];

		$validator = tr_validator( $options, $this->request->getFields() );

		if ( $validator->getErrors() ) {
			$validator->flashErrors( $this->response );

			return tr_redirect()->toPage( $this->resource, 'edit', $crawl_setting->id )
			                    ->withFields( $this->request->getFields() );
		}

		$crawl_setting->crawl_domain_id = $this->request->getFields( 'crawl_domain_id' );
		$crawl_setting->categories      = $this->request->getFields( 'categories' );
		// $crawl_setting->options         = $this->request->getFields('options');
		$crawl_setting->save();
		$this->response->flashNext( 'Success!' );

		return tr_redirect()->toPage( $this->resource, 'index' );
	}

	/**
	 * Destroy item
	 *
	 * AJAX requests and normal requests can be made to this action
	 *
	 * @param \App\Models\CrawlSetting $crawl_setting
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function destroy( CrawlSetting $crawl_setting ) {
		if ( ! $success = (boolean) $crawl_setting->delete() ) {
			return $this->response->flashNext( 'Setting deleted failure!', 'error' );
		}
		$this->response->flashNext( 'Setting deleted!' );

		return tr_redirect()->toPage( $this->resource, 'index' );
	}

    /**
     * Enable crawler
     */
    public function enable() {
        $enable = isset($_POST['enable']) ? true : false;
        if (!$enable) {
            wp_clear_scheduled_hook('crawl_link_schedule_event');
            wp_clear_scheduled_hook('crawl_data_schedule_event');
            $this->response->setMessage('Success');
            return $this->response;
        }
        if (! wp_next_scheduled('crawl_link_schedule_event')) {
            wp_schedule_event(time(), '30_minutes', 'crawl_link_schedule_event');
        }

        if (! wp_next_scheduled('crawl_data_schedule_event')) {
            wp_schedule_event(time(), '30_minutes', 'crawl_data_schedule_event');
        }
        
        $this->response->setMessage('Success');
        return $this->response;
    }

    private function get_crawl_status() {
        $crawl_link = is_object(wp_get_scheduled_event('crawl_link_schedule_event'));
        $craw_data = is_object(wp_get_scheduled_event('crawl_data_schedule_event'));
        return $crawl_link && $craw_data;
    }
}