<?php

namespace App\Models;

use \TypeRocket\Models\Model;

class CrawlCategory extends Model {
	/**
	 * @var string
	 */
	protected $resource = 'crawl_categories';

	/**
	 * @var string
	 */
	protected $table = 'wp_crawl_categories';

	/**
	 * @var array
	 */
	protected $fillable = [
		'crawl_domain_id',
		'category_url',
	];

	/**
	 * @var array
	 */
	protected $cast = [
		'category_url' => 'string',
	];

	/**
	 * @return \App\Models\CrawlCategory|null
	 */
	public function domain() {
		return $this->belongsTo( CrawlDomain::class, 'crawl_domain_id' );
	}
}