<?php

namespace App\Models;

use \TypeRocket\Models\Model;

class CrawlLink extends Model {
	/**
	 * @var string
	 */
	protected $resource = 'crawl_links';

	/**
	 * @var string
	 */
	protected $table = 'wp_crawl_links';

	/**
	 * @var array
	 */
	protected $fillable = [
		'link',
		'status',
		'options',
	];

	/**
	 * @var array
	 */
	protected $cast = [
		'link'    => 'string',
		'status'  => 'boolean',
		'options' => 'array',
	];
}