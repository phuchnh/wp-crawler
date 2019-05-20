<?php /** @noinspection AutoloadingIssuesInspection */

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/phuchnh
 * @since      1.0.0
 *
 * @package    Wp_Crawler
 * @subpackage Wp_Crawler/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Crawler
 * @subpackage Wp_Crawler/admin
 * @author     Phúc Huỳnh <huynhngochoangphuc@gmail.com>
 */
class Wp_Crawler_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.css' );

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-crawler-admin.css', array(),
			$this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'sweetalert', 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js',
			[ 'jquery' ] );

		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js',
			[ 'jquery' ] );

		wp_enqueue_script( 'loadingoverlay',
			'https://cdnjs.cloudflare.com/ajax/libs/jquery-loading-overlay/2.1.6/loadingoverlay.min.js', [ 'jquery' ] );

		wp_enqueue_script( '_', 'https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.11/lodash.min.js' );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-crawler-admin.js', [ 'jquery' ],
			$this->version, false );
	}

	/**
	 * Register functions of Typerocket
	 */
	public function typerocket_loaded() {
		$setting  = $this->add_menu_crawl_settings();
		$domain   = $this->add_menu_crawl_domain();
		$category = $this->add_menu_crawl_categories();
		$preview  = $this->add_menu_crawl_preview();
		$setting->apply( $domain, $category, $preview );
	}

	private function add_menu_crawl_preview() {
		$resource = 'crawl_preview';
		/** @var \TypeRocket\Register\Page $menu */
		$menu = tr_page( $resource, 'index', __( 'Preview' ) );
		$menu->useController()
		     ->mapAction( 'POST', 'hanlde' )
		     ->removeTitle()
		     ->setArgument( 'menu', __( 'Preview' ) );

		return $menu;
	}

	private function add_menu_crawl_settings() {
		$resource = 'crawl_setting';

		$config = [
			'add'    => [
				'title'       => __( 'Add Setting' ),
				'remove_menu' => true,
				'actions'     => [ 'GET' => 'add', 'POST' => 'create' ]
			],
			'edit'   => [
				'title'       => __( 'Edit Setting' ),
				'remove_menu' => true,
				'actions'     => [ 'GET' => 'edit', 'PUT' => 'update' ]
			],
			'delete' => [
				'title'       => __( 'Delete Setting' ),
				'remove_menu' => true,
				'actions'     => [ 'DELETE' => 'destroy' ]
			]
		];

		$sub_menus = $this->add_sub_menu_pages( $resource, $config );
		/** @var \TypeRocket\Register\Page $menu */
		$menu = tr_page( $resource, 'index', __( 'Crawler Settings' ) );
		$menu->setIcon( 'scissors' )->useController()->removeTitle();
		$menu->apply( $sub_menus )->setArgument( 'menu', __( 'WP Crawler' ) );

		return $menu;
	}

	/**
	 * @return \TypeRocket\Register\Page
	 */
	private function add_menu_crawl_categories() {
		$resource  = 'crawl_category';
		$config    = [
			'add'    => [
				'title'   => __( 'Add Category' ),
				'actions' => [ 'GET' => 'add', 'POST' => 'create' ]
			],
			'edit'   => [
				'title'   => __( 'Edit Category' ),
				'actions' => [ 'GET' => 'edit', 'PUT' => 'update' ]
			],
			'delete' => [
				'title'   => __( 'Delete Category' ),
				'actions' => [ 'DELETE' => 'destroy' ]
			]
		];
		$sub_menus = $this->add_sub_menu_pages( $resource, $config );
		/** @var \TypeRocket\Register\Page $menu */
		$menu = tr_page( $resource, 'index', __( 'All Categories' ) );
		$menu->useController()->removeTitle();
		$menu->apply( $sub_menus )->setArgument( 'menu', __( 'Categories' ) );

		return $menu;
	}

	/**
	 * @return \TypeRocket\Register\Page
	 */
	private function add_menu_crawl_domain() {
		$resource  = 'crawl_domain';
		$config    = [
			'add'     => [
				'title'   => __( 'Add Domain' ),
				'actions' => [ 'GET' => 'add', 'POST' => 'create' ]
			],
			'edit'    => [
				'title'   => __( 'Edit Domain' ),
				'actions' => [ 'GET' => 'edit', 'PUT' => 'update' ]
			],
			'delete'  => [
				'title'   => __( 'Delete Domain' ),
				'actions' => [ 'DELETE' => 'destroy' ]
			],
			'single'  => [
				'title'   => __( 'Single Setting' ),
				'actions' => [ 'GET' => 'single', 'PUT' => 'update' ]
			],
			'archive' => [
				'title'   => __( 'Archive Setting' ),
				'actions' => [ 'GET' => 'archive', 'PUT' => 'update' ]
			],
		];
		$sub_menus = $this->add_sub_menu_pages( $resource, $config );
		/** @var \TypeRocket\Register\Page $menu */
		$menu = tr_page( $resource, 'index', __( 'All Domains' ) );
		$menu->useController()->removeTitle();
		$menu->apply( $sub_menus )->setArgument( 'menu', __( 'Domains' ) );

		return $menu;
	}

	/**
	 * Add sub menu pages
	 *
	 * @param string $resource
	 * @param array $settings
	 *
	 * @return mixed
	 */
	private function add_sub_menu_pages( string $resource, array $settings ) {
		$pages = [];
		foreach ( $settings as $key => $config ) {
			/** @var \TypeRocket\Register\Page $page */
			$page = tr_page( $resource, $key, $config['title'] ?: null );

			if ( ! $config['actions'] ) {
				continue;
			}

			foreach ( $config['actions'] as $method => $action ) {
				$page->mapAction( $method, $action );
			}

			$page->useController()->removeMenu()->removeTitle();

			$pages[] = $page;
		}

		return $pages;
	}

	/**
	 * @return  void
	 */
	public function handle_crawl_schedule_event() {
		( new \App\Commands\CrawlLink() )->exec();
		( new \App\Commands\CrawData() )->exec();
	}

}
