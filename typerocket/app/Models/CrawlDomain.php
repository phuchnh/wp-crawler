<?php

namespace App\Models;

use \TypeRocket\Models\Model;

class CrawlDomain extends Model {
	/**
	 * @var string
	 */
	protected $resource = 'crawl_domains';

	/**
	 * @var string
	 */
	protected $table = 'wp_crawl_domains';

	/**
	 * @var array
	 */
	protected $fillable = [
		'domain_url',
		'archive_options',
		'single_options',
	];

	/**
	 * @var array
	 */
	protected $cast = [
		'domain_url'      => 'string',
		'archive_options' => 'array',
		'single_options'  => 'array',
	];

	/**
	 * @return \TypeRocket\Models\Model|null
	 */
	public function categories() {
		return $this->hasMany( CrawlCategory::class, 'crawl_domain_id' );
	}
}