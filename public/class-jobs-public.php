<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.roma.la
 * @since      1.0.0
 *
 * @package    Jobs
 * @subpackage Jobs/public
 */

class Jobs_Public {

	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_filter( 'template_include', array($this, 'jobs_archive_template'));
		add_filter( 'single_template', array($this, 'jobs_single_template'));
		add_filter( 'fl_after_post_content', array($this, 'jobs_careers_template'));
		add_filter( 'template_redirect', array($this, 'jobs_url_alias'));

	}

	public function enqueue_styles() {
		global $template;
		if ( basename( $template ) === 'archive-jobs.php' || basename( $template ) === 'single-jobs.php'|| strpos($_SERVER['REQUEST_URI'], 'careers') !== false ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/jobs-public.min.css', array(), $this->version, 'all' );
		}
	}

	public function enqueue_scripts() {
		global $template;
		if ( basename( $template ) === 'archive-jobs.php' ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/jobs-public.js', array( 'jquery' ), $this->version, false );
		}
		if ( basename( $template ) === 'single-jobs.php' ) {
			wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/jobs-iframe.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( $this->plugin_name);
			$id_job = get_post_meta( get_the_ID(), 'job_id', true );
			wp_localize_script( $this->plugin_name, 'jobs_ajax_object', array('id_job' => $id_job));
		}
	}

	public function jobs_archive_template( $archive ) {
		global $post;
    if( is_post_type_archive('jobs') ) {
        $archive = plugin_dir_path( __FILE__ ) . 'templates/archive-jobs.php';
    }
    return $archive;
	}

	public function jobs_single_template( $single ) {
		global $post;
    if ( $post->post_type == 'jobs' ) {
				$single = plugin_dir_path( __FILE__ ) . 'templates/single-jobs.php';
        if ( $single ) {
            return $single;
        }
    }
    return $single;
	}

	// Careers pages
	public function jobs_careers_template( $careers ) {
		if(strpos($_SERVER['REQUEST_URI'], 'careers') !== false){
			include plugin_dir_path( __FILE__ ) . 'templates/careers.php';
		}
	}

	// Redirect jobs by id and alias
	public function jobs_url_alias() {
		global $wpdb;
		$parts = parse_url($_SERVER['REQUEST_URI']);
		$path_parts = explode('/', $parts['path']);
		$id = (array_key_exists('2', $path_parts)) ? $path_parts[2] : '';
		$parts = implode(" ",$parts);

		if(strpos($parts, 'jobs') !== false){
			if(is_numeric($id)) {
				$results = $wpdb->get_results( "select post_id, meta_key from $wpdb->postmeta where meta_value = $id", ARRAY_A );
				if(!empty($results)) {
					wp_redirect('https://boards.greenhouse.io/signifyd95/jobs/'.$id, 301);
				} else {
					wp_redirect(get_site_url( null, 'jobs/' ));
				}
			} else {
				if (is_singular('jobs')) {
					wp_redirect('https://boards.greenhouse.io/signifyd95/jobs/'.get_post_meta(get_the_ID(), 'job_id' )[0], 301);
				}
				if (is_404()) {
					wp_redirect(get_site_url( null, 'jobs/?404' ));
				} 
			}

		}

	}
}
