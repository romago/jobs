<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.roma.la
 * @since      1.0.0
 *
 * @package    Jobs
 * @subpackage Jobs/admin
 */

class Jobs_Admin {

	private $plugin_name;

	private $version;

	protected static $instance = NULL;
	public static function get_instance() {
		if ( NULL === self::$instance )
				self::$instance = new self;

		return self::$instance;
	}

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action('admin_menu', array(&$this, 'jobs_add_settings_page'));

		// Setup form settings
		add_action('admin_init', array($this, 'jobs_import_form_init'));

		// Cron
		add_filter( 'cron_schedules', array($this, 'jobs_import_cron_schedule_12'));
		// Remove old hook from DB
		if ( wp_next_scheduled( 'jobs_import_cron_schedule' ) ){wp_clear_scheduled_hook( 'jobs_import_cron_schedule' );}
		// Schedule an action if it's not already scheduled
		if ( ! wp_next_scheduled( 'jobs_import_cron_schedule_12' ) ) {
				wp_schedule_event( strtotime('08:00:00'), 'daily', 'jobs_import_cron_schedule_12' );
		}

		// Hook into that action that'll fire weekly
		if (get_option( 'auto_sync_jobs' ) == 1) {
			add_action( 'jobs_import_cron_schedule_12', array($this, 'jobs_save_data'));
		}

		add_action('trashed_post', array($this, 'jobs_skip_trash'));

	}

	public function jobs_skip_trash($post_id) {
	    if (get_post_type($post_id) == 'jobs') {
	        wp_delete_post( $post_id, true );
	    }
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/settings-page-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/settings-page-admin.js', array( 'jquery' ), $this->version, false );
	}

	// CURL request to GreenHouse
	private function data_request($link) {

	  $url = $link;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
				echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);
	  $data = json_decode($response);
	  return $data;

	}

	// Get info from API
	private function jobs_info() {
	  $url = 'https://boards-api.greenhouse.io/v1/boards/signifyd95/jobs';
	  $dataRequest = $this->data_request($url);
	  return $dataRequest;
	}

	// Build Teams departments
	private function jobs_team_terms() {
		// Import Team Department
		$urlDepartments = 'https://boards-api.greenhouse.io/v1/boards/signifyd95/departments';
		$dataDepartments = $this->data_request($urlDepartments);
		$team = 'jobs_team';
		$parents_array = [];
		$all_dept_collection = [];
		$all_term_collection = [];

		// Collect all parent
		foreach($dataDepartments->departments as $dept) {
			$parentId = $dept->parent_id;
			if(!$parentId) {
				$id = $dept->id;
				array_push($parents_array, $id);
			}
		}

		// Insert/Update term and remove old
		$d = 0;
		foreach($dataDepartments->departments as $department) {
			$id = $department->id;
			$name = $department->name;

			$parent_source_id = ($department->parent_id) ? $department->parent_id : '0';
			$parent_term = 0;
			$term_slug = get_term_by('slug', $id, $team);
			$term_id = ($term_slug) ? $term_slug->term_id : '';

			if ($parent_source_id != 0) {
				$keyName = array_search($parent_source_id, $parents_array);
				$parent_term = get_term_by('slug', $parents_array[$keyName], $team);
			}

			$parent_id = ($parent_term != 0) ? $parent_term->term_id : 0;

			if(!$term_slug && $parent_id == 0) {
				wp_insert_term($name, $team,
					array(
						'slug' => $id,
						'parent' => 0
					)
				);

			} elseif (!$term_slug && $parent_id != 0) {
				wp_insert_term($name, $team,
					array(
						'slug' => $id,
						'parent' => $parent_id
					)
				);
			} elseif ($term_slug) {
				wp_update_term($term_id, $team,
					array(
						'parent' => $parent_id
					)
				);
			}
			// Collect all new terms
			$all_dept_collection[$d]['name'] = $name;
			$all_dept_collection[$d]['slug'] = $id;
			$d++;
		}

		// Get all existing terms and build an array for compare
		$all_terms = get_terms( 'jobs_team', array(
				'hide_empty' => false,
		) );
		$i = 0;
		foreach($all_terms as $term) {
			$all_term_collection[$i]['name'] = $term->name;
			$all_term_collection[$i]['slug'] = $term->slug;
			$i++;
		}
		// Compare and remove if it not existing in the source
		foreach ($all_term_collection as $value_term) {
			$slug = $value_term['slug'];
			$result = in_array($value_term, $all_dept_collection);
			if(!$result){
				$old_term = get_term_by('slug', $slug, $team);
				wp_delete_term($old_term->term_id, $team);
			}
		}
	}

	// Get data from G2 (reviews)
	public function jobs_import_data($link) {

	  // Request to API
		$urlJobs = "https://boards-api.greenhouse.io/v1/boards/signifyd95/jobs/". $link;
		$dataJob = $this->data_request($urlJobs);

	  // WordPress import post
	  require_once( ABSPATH . 'wp-admin/includes/post.php' );

    // Check if ID exist
    $id = $dataJob->id;
	$title = $dataJob->title;
	$content = $dataJob->content;
	$date = $dataJob->updated_at;
	$location = $dataJob->location->name;
	$location_array = explode(';', $location);
	// Departments
	$departments_array = array();
	foreach($dataJob->departments as $department) {
		$departments_array[] = $department->name;
	}
	if(empty($departments_array)) {
		$departments_array[] = 'General Applications';
	}

	// Custom query to check meta if exist
	$args = array(
		'post_type' => 'jobs',
		'meta_query' => array(
			array(
				'key' => 'job_id',
				'value' => $id
			)
		)
	);
    $my_query = new WP_Query( $args );

    if($my_query->have_posts()) {
			while ($my_query->have_posts()) {
				$my_query->the_post();
				$post_id = get_the_ID();

				$update_post_args = array(
					'post_title'   => $title,
					'post_content' => html_entity_decode($content)
				);
				// Update post
				wp_update_post($update_post_args);

				wp_set_object_terms( $post_id, $location_array, 'jobs_location' );
				wp_set_object_terms( $post_id, $departments_array, 'jobs_team' );
			}

  	} else {

			$post = array(
				'post_title' => $title,
				'post_content' => html_entity_decode($content),
				'post_status' => 'publish',
				'post_author' => 2,
				'post_date' => $date,
				'post_type' => 'jobs',
				'comment_status' => 'closed',
				'meta_input' => array(
					'job_id' => $id
				)
			);

			// Insert new post
			$postId = wp_insert_post( $post, true );
			wp_set_object_terms( $postId, $location_array, 'jobs_location', true );
			wp_set_object_terms( $postId, $departments_array, 'jobs_team', true );

		}
		wp_reset_postdata();

	}


	// Add link to Jobs post type
	public function jobs_add_settings_page() {
			add_submenu_page(
		    'edit.php?post_type=jobs',
		    __( 'Jobs Sync Content', 'menu-jobs' ),
		    __( 'Sync Content', 'menu-jobs' ),
		    'manage_options_jobs',
		    'jobs_sync',
				array(&$this, 'jobs_render_settings_page')
			);
	}

	public function jobs_import_form_init() {
	    register_setting('import_jobs', 'import_jobs_data');
	    add_settings_section( 'import_section_id', 'Import Content', array(&$this, 'jobs_import_section_callback'), 'jobs_sync' );
	    add_settings_field( 'import_field_name_id', 'Auto-sync', array(&$this, 'jobs_import_field_callback'), 'jobs_sync', 'import_section_id' );
	}

	public function jobs_import_section_callback() {
	    echo "Auto-sync run twice a day, 12 AM and 12 PM Pacific Time.";
	}

	public function jobs_import_field_callback() {
	  echo '<input type="checkbox" name="auto_sync_jobs" value="1" ' . checked( 1, get_option( 'auto_sync_jobs' ), false ) . '/>';
	}

	// Render setting page
	public function jobs_render_settings_page() {

		// Get JSON data
		$data = $this->jobs_info();

	  // General check for user permissions.
	  if (!current_user_can('manage_options_jobs'))  {
	    wp_die( __('You do not have sufficient pilchards to access this page.')    );
	  }
		// Check whether the button has been pressed AND also check the nonce
		if (isset($_POST['sync_content']) && check_admin_referer('sync_content_clicked')) {

			// Get select option form
			$checkbox = $_POST['auto_sync_jobs'];
			update_option('auto_sync_jobs', $checkbox);

			// the button has been pressed AND we've passed the security check
			$this->jobs_save_data();

			// Message successful
			echo '<div id="message" class="updated fade"><p>Content is synced successful.</p></div>';

		}

	  // Start building the page
	  echo '<div id="jobs-admin" class="wrap">';

	  echo '<h1>Jobs Sync Content</h1>
					<p>The plugin provides the ability to import content from the GreenHouse website.</p>
					<p>The unique identifier is the content ID from the source.</p><hr><br>';

		// G2 info
	  echo '<span class="label">Content count for import:</span> '. $data->meta->total .'<br>';

	  // Get number of published posts
	  $count_posts = wp_count_posts('jobs');
	  $total_posts = $count_posts->publish;

	  // Display published info
	  echo '<span class="label">Posts published: </span> '. $total_posts .'<br>';
	  echo '<span class="label">Last sync date: </span> '. get_option( 'jobs_sync_time' ) .'<br><br><hr><br>';


	  // Build form
	  echo '<form action="edit.php?post_type=jobs&page=jobs_sync" method="post">';

	  // Settings fields
	  settings_fields( 'import_jobs' );
	  do_settings_sections( 'jobs_sync' );

	  // this is a WordPress security feature - see: https://codex.wordpress.org/WordPress_Nonces
	  wp_nonce_field('sync_content_clicked');

	  echo '<input type="hidden" value="true" name="sync_content" />';
	  submit_button('Sync & Save');
	  echo '</form>';
	  echo '</div>';
	  // End form

	}

	// Save sync info to DB
	public function jobs_save_data() {

		$collect_ids = [];
		$data_info = $this->jobs_info();

		// Build Team
		$this->jobs_team_terms();
		// Loop for request all posts
		foreach($data_info->jobs as $job) {
			$this->jobs_import_data($job->id);
			array_push($collect_ids, $job->id);
		}

		// Get postmeta from DB
		global $wpdb;
		$db_meta = $wpdb -> get_results("SELECT * FROM wp_postmeta WHERE meta_key='job_id'");
		$db_meta = json_decode(json_encode($db_meta), true);

		// Loop for search old post
		foreach ($db_meta as $key => $value) {
			$meta_val = $value['meta_value'];
			$val = intval($meta_val);
			$result = array_search($val, $collect_ids);

			if(!$result && $result !== 0){
				$id = $db_meta[$key]['post_id'];
				// Remove old post
				wp_delete_post($id);
			}
		}

	  // Get counf of post from source
	  $synced_post = $data_info->meta->total;
	  // Save sync date
	  $tz_time = new DateTimeZone('America/Los_Angeles');
	  $date_time = new DateTime();
	  $date_time->setTimezone($tz_time);
	  $date_synced = $date_time->format('m\/d\/Y h:i A');
	  update_option('jobs_sync_time', $date_synced);

	}
	// Cron for auto-sync
	function jobs_import_cron_schedule_12( $schedules ) {
	    $schedules[ 'every_twelve_hours' ] = array( 'interval' => 43200, 'display' => __( 'Every 12 hours', 'devhub_12' ) );
	    return $schedules;
	}
}
