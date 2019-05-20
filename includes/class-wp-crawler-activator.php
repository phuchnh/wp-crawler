<?php /** @noinspection SqlNoDataSourceInspection */
/** @noinspection AutoloadingIssuesInspection */

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/phuchnh
 * @since      1.0.0
 *
 * @package    Wp_Crawler
 * @subpackage Wp_Crawler/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Crawler
 * @subpackage Wp_Crawler/includes
 * @author     Phúc Huỳnh <huynhngochoangphuc@gmail.com>
 */
class Wp_Crawler_Activator {

	/**
	 * Wp_Crawler_Activator instance
	 * @var Wp_Crawler_Activator
	 */
	private static $instance;

	/**
	 * WordPress table prefix
	 * @var string
	 */
	private $prefix;

	/**
	 * The database character collate.
	 * @var string
	 */
	private $charset_collate;

	public function __construct() {
		global $wpdb;
		$this->prefix          = $wpdb->prefix;
		$this->charset_collate = $wpdb->get_charset_collate();
	}

	/**
	 * The code that runs during plugin activation.
	 * @return void
	 */
	public static function activate() {
		if ( static::$instance === null ) {
			static::$instance = new Wp_Crawler_Activator;
		}

		static::$instance->setup_db();
		static::$instance->activate_crawl_schedule_event();
	}

	private function activate_crawl_schedule_event() {
		if ( ! wp_next_scheduled( 'crawl_schedule_event' ) ) {
			wp_schedule_event( time(), '30_minutes', 'crawl_schedule_event' );
		}
	}

	public function setup_db() {
		$this->create_table_crawl_domains();
		$this->create_table_crawl_categories();
		$this->create_table_crawl_settings();
		$this->create_table_crawl_links();
	}

	/**
	 * @return void
	 */
	private function create_table_crawl_links() {
		$crawl_links_table = $this->prefix . 'crawl_links';

		$sql = "CREATE TABLE IF NOT EXISTS {$crawl_links_table} (
              id         int(11) UNSIGNED                   NOT NULL AUTO_INCREMENT,
              link       text                               NOT NULL,
              status     boolean  DEFAULT FALSE,
              options    longtext,
              created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
              updated_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (id)
        ) {$this->charset_collate};";

		$this->execute_sql( $sql );
	}

	/**
	 * @return void
	 */
	private function create_table_crawl_settings() {
		$crawl_settings_table = $this->prefix . 'crawl_settings';
		$crawl_domains_table  = $this->prefix . 'crawl_domains';

		$sql = "CREATE TABLE IF NOT EXISTS {$crawl_settings_table} (
              id               int(11) UNSIGNED                   NOT NULL AUTO_INCREMENT,
              crawl_domain_id  int(11) UNSIGNED                   NOT NULL,
              categories       longtext,
              options          longtext,
              status           boolean  DEFAULT FALSE,
              created_at       datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
              updated_at       datetime DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (id),
              FOREIGN KEY (crawl_domain_id) REFERENCES {$crawl_domains_table} (id) ON DELETE CASCADE
        ) {$this->charset_collate};";

		$this->execute_sql( $sql );
	}

	/**
	 * @return void
	 */
	private function create_table_crawl_categories() {
		$crawl_categories_table = $this->prefix . 'crawl_categories';
		$crawl_domains_table    = $this->prefix . 'crawl_domains';

		$sql = "CREATE TABLE IF NOT EXISTS {$crawl_categories_table} (
            id               int(11) UNSIGNED                   NOT NULL AUTO_INCREMENT,
		    crawl_domain_id  int(11) UNSIGNED                   NOT NULL,
		    category_url     VARCHAR(191)                       NOT NULL,
		    created_at       datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		    updated_at       datetime DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,
		    PRIMARY KEY (id),
		    CONSTRAINT unique_category_url UNIQUE (category_url),
		    FOREIGN KEY (crawl_domain_id) REFERENCES {$crawl_domains_table} (id) ON DELETE CASCADE
        ) {$this->charset_collate};";

		$this->execute_sql( $sql );
	}

	/**
	 * @return void
	 */
	private function create_table_crawl_domains() {
		$crawl_domain_table = $this->prefix . 'crawl_domains';

		$sql = "CREATE TABLE IF NOT EXISTS {$crawl_domain_table} (
            id                  int(11) UNSIGNED                   NOT NULL AUTO_INCREMENT,
		    domain_url          VARCHAR(191)                       NOT NULL,
		    archive_options     longtext,
		    single_options      longtext,
		    created_at          datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		    updated_at          datetime DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,
		    PRIMARY KEY (id),
		    CONSTRAINT unique_domain_url UNIQUE (domain_url)
        ) {$this->charset_collate};";

		$this->execute_sql( $sql );
	}

	/**
	 * @param $sql string
	 */
	private function execute_sql( $sql ) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

}
