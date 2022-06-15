<?php

/**
 * The file that defines the core plugin class
 *
 * @link       https://www.roma.la
 * @since      1.0.0
 *
 * @package    Jobs
 * @subpackage Jobs/includes
 */

/**
 *
 * @since      1.0.0
 * @package    Jobs
 * @subpackage Jobs/includes
 * @author     Roman Golubev
 */
class Jobs {

	protected $loader;

	protected $plugin_name;

	protected $version;

	public function __construct() {
		if ( defined( 'JOBS_VERSION' ) ) {
			$this->version = JOBS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'jobs';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		add_action( 'init', array( $this, 'jobs_register_content' ));
		add_action( 'init', array( $this, 'jobs_register_locations_taxonomy' ));
		add_action( 'init', array( $this, 'jobs_register_teams_taxonomy' ));
		add_action( 'cmb2_admin_init', array( $this, 'jobs_register_meta' ));
		add_action( 'wpseo_opengraph_image', array( $this, 'og_jobs_page'));
		add_action( 'init', array( $this, 'add_custom_roles'));
		add_action( 'admin_init', array( $this, 'add_custom_capabilities' ) );

	}

	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jobs-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-jobs-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-jobs-public.php';

		$this->loader = new Jobs_Loader();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Jobs_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Jobs_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

	/* Register Jobs post type */
	public function jobs_register_content() {
		// creating (registering) the custom type
		register_post_type( 'jobs',
			array(
				'labels'              => array(
					'name'               => __( 'Jobs', 'fl-automator' ),
					'singular_name'      => __( 'Jobs', 'fl-automator' ),
					'all_items'          => __( 'All Jobs', 'fl-automator' ),
					'add_new'            => __( 'Add New Job', 'fl-automator' ),
					'add_new_item'       => __( 'Add New Job', 'fl-automator' ),
					'edit'               => __( 'Edit', 'fl-automator' ),
					'edit_item'          => __( 'Edit Job', 'fl-automator' ),
					'new_item'           => __( 'New Job', 'fl-automator' ),
					'view_item'          => __( 'View Job', 'fl-automator' ),
					'search_items'       => __( 'Search Job', 'fl-automator' ),
					'not_found'          => __( 'Nothing found in the Database.', 'fl-automator' ),
					'not_found_in_trash' => __( 'Nothing found in Trash', 'fl-automator' ),
					'parent_item_colon'  => ''
				),
				'description'         => __( 'Import jobs from GreenHouse', 'fl-automator' ),
				'public'              => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'show_ui'             => true,
				'query_var'           => true,
				'menu_position'       => 8,
				'menu_icon'           => 'dashicons-id-alt',
				'rewrite'             => array( 'slug' => 'jobs', 'with_front' => false ),
				'has_archive'         => true,
				'capability_type' 		=> array('job','jobs'),
				'map_meta_cap'    		=> true,
				'hierarchical'        => true,
				'supports'            => array(
					'title',
					'editor',
					'author',
					'thumbnail',
					'excerpt',
					'custom-fields',
					'revisions',
					'sticky',
					'page-attributes'
				)
			)
		);
	}

	/* Register Jobs locations taxonomies */
	function jobs_register_locations_taxonomy() {
		register_taxonomy( 'jobs_location',
			array( 'jobs' ),
			array(
				'hierarchical'      => true,
				'labels'            => array(
					'name'              => __( 'Jobs Locations', 'fl-automator' ),
					'singular_name'     => __( 'Jobs Location', 'fl-automator' ),
					'search_items'      => __( 'Search Jobs Location', 'fl-automator' ),
					'all_items'         => __( 'All Jobs Locations', 'fl-automator' ),
					'parent_item'       => __( 'Parent Jobs Location', 'fl-automator' ),
					'parent_item_colon' => __( 'Parent Jobs Location:', 'fl-automator' ),
					'edit_item'         => __( 'Edit Jobs Location', 'fl-automator' ),
					'update_item'       => __( 'Update Jobs Location', 'fl-automator' ),
					'add_new_item'      => __( 'Add New Jobs Location', 'fl-automator' ),
					'new_item_name'     => __( 'New Jobs Category Name', 'fl-automator' )
				),
				'show_admin_column' => true,
				'show_ui'           => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'locations', 'with_front' => false),
				'capabilities' => array (
		  			'manage_terms' => 'edit_jobs',
		  			'edit_terms' => 'edit_jobs',
		  			'delete_terms' => 'edit_jobs',
		  			'assign_terms' => 'edit_jobs',
				)
			)
		);
	}

	/* Register Jobs Teams taxonomies */
	function jobs_register_teams_taxonomy() {
		register_taxonomy( 'jobs_team',
			array( 'jobs' ),
			array(
				'hierarchical'      => true,
				'labels'            => array(
					'name'              => __( 'Jobs Teams', 'fl-automator' ),
					'singular_name'     => __( 'Jobs Team', 'fl-automator' ),
					'search_items'      => __( 'Search Jobs Team', 'fl-automator' ),
					'all_items'         => __( 'All Jobs Teams', 'fl-automator' ),
					'parent_item'       => __( 'Parent Jobs Team', 'fl-automator' ),
					'parent_item_colon' => __( 'Parent Jobs Team:', 'fl-automator' ),
					'edit_item'         => __( 'Edit Jobs Team', 'fl-automator' ),
					'update_item'       => __( 'Update Jobs Team', 'fl-automator' ),
					'add_new_item'      => __( 'Add New Jobs Team', 'fl-automator' ),
					'new_item_name'     => __( 'New Jobs Category Name', 'fl-automator' )
				),
				'show_admin_column' => true,
				'show_ui'           => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'team', 'with_front' => false),
				'capabilities' => array (
		  			'manage_terms' => 'edit_jobs',
		  			'edit_terms' => 'edit_jobs',
		  			'delete_terms' => 'edit_jobs',
		  			'assign_terms' => 'edit_jobs',
				)
			)
		);
	}

	/* Register Jobs meta */
	public function jobs_register_meta() {

		// Box for meta fields
		$cmb = new_cmb2_box( array(
			'id'            => 'jobs_metabox',
			'title'         => __( 'Additional fields', 'cmb2' ),
			'object_types'  => array( 'jobs' ),
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true,
		) );

		// Job ID
		$cmb->add_field( array(
			'name'       => __( 'ID', 'cmb2' ),
			'desc'       => __( 'ID job', 'cmb2' ),
			'id'         => 'job_id',
			'type'       => 'text_small',
		) );

	}

	public function og_jobs_page() {
	    if( is_post_type_archive( 'jobs' ) ) {
		    echo '
				<meta property="twitter:title" content="Jobs for engineers, developers, marketing &amp; more | Signifyd" />
				<meta property="twitter:description" content="Join us as we create a new market that is changing the way ecommerce retailers manage fraud and build better customer experiences." />
				<meta property="twitter:image" content="https://www.roma.la/wp-content/uploads/2018/12/Social-Sharing-Careers.png" />
				';
	    }
	}

	function add_custom_roles() {
    if (get_option('manager_roles') !== 1) {
        add_role( 'hr_role', 'People Manager',
					array(
						"read" => true
	        )
				);
        add_option( 'manager_roles', 1 );
    }
	}

	// Custom capabilities of custom post types
	private static $customCaps = array(
			[ 'singular' => 'job', 'plural' => 'jobs' ],
	);

	// Add custom capabilities for admin and HR
	public static function add_custom_capabilities() {

			$role = get_role( 'hr_role' );
			$roleAdmin = get_role( 'administrator' );

			foreach( self::$customCaps as $cap ){

					$singular = $cap['singular'];
					$plural = $cap['plural'];

					// HR role
					$role->add_cap( "edit_{$singular}" );
					$role->add_cap( "edit_{$plural}" );
					$role->add_cap( "edit_others_{$plural}" );
					$role->add_cap( "publish_{$plural}" );
					$role->add_cap( "read_{$singular}" );
					$role->add_cap( "read_private_{$plural}" );
					$role->add_cap( "delete_{$singular}" );
					$role->add_cap( "delete_{$plural}" );
					$role->add_cap( "delete_private_{$plural}" );
					$role->add_cap( "delete_others_{$plural}" );
					$role->add_cap( "edit_published_{$plural}" );
					$role->add_cap( "edit_private_{$plural}" );
					$role->add_cap( "delete_published_{$plural}" );
					$role->add_cap( "manage_options_{$plural}" );
					$role->add_cap( "manage_options_{$singular}" );
					$role->add_cap('upload_files');
					
					// Administrator role
					$roleAdmin->add_cap( "edit_{$singular}" );
					$roleAdmin->add_cap( "edit_{$plural}" );
					$roleAdmin->add_cap( "edit_others_{$plural}" );
					$roleAdmin->add_cap( "publish_{$plural}" );
					$roleAdmin->add_cap( "read_{$singular}" );
					$roleAdmin->add_cap( "read_private_{$plural}" );
					$roleAdmin->add_cap( "delete_{$singular}" );
					$roleAdmin->add_cap( "delete_{$plural}" );
					$roleAdmin->add_cap( "delete_private_{$plural}" );
					$roleAdmin->add_cap( "delete_others_{$plural}" );
					$roleAdmin->add_cap( "edit_published_{$plural}" );
					$roleAdmin->add_cap( "edit_private_{$plural}" );
					$roleAdmin->add_cap( "delete_published_{$plural}" );
					$roleAdmin->add_cap( "manage_options_{$plural}" );
					$roleAdmin->add_cap( "manage_options_{$singular}" );

			}
	}

}
