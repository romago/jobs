<?php get_header(); ?>
<?php
	// Get parent terms for Departments
	$parent_terms_team = get_terms(array('taxonomy' => 'jobs_team', 'parent' => 0, 'orderby' => 'slug'));
	$terms_location = get_terms(array('taxonomy' => 'jobs_location'));
	$terms_loc_array = array_column($terms_location, 'name');
	$terms_team_array = array_column($parent_terms_team, 'name');
	// Check URL for parametrs
	$url = $_SERVER['REQUEST_URI'];
	$url_components = parse_url($url);
	$empty_result_loc = null;
	$empty_result_dep = null;
	$location_url = null;
	if (array_key_exists('query', $url_components)) {
		parse_str($url_components['query'], $params);
		$team_url = array_key_exists('team', $params) ? strtolower($params['team']) : null;
		$location_url = array_key_exists('location', $params) ? strtolower($params['location']) : null;
		$post_not_found = array_key_exists('404', $params) ? '1' : null;
		// Prepare array for search
		function nestedTrim($value) {
				if (is_array($value)) {
						return array_map('nestedTrim', $value);
				}
				$value = str_replace(' ', '', $value);
				return strtolower($value);
		}
		$terms_loc_array = array_map('nestedTrim', $terms_loc_array);
		$terms_team_array = array_map('nestedTrim', $terms_team_array);

		$location_url_rep = str_replace(' ', '', $location_url);
		$team_url_rep = str_replace(' ', '', $team_url);
		if(!in_array($location_url_rep, $terms_loc_array) && $location_url) {
			$empty_result_loc = 1;
		}
		if(!in_array($team_url_rep, $terms_team_array) && $team_url) {
			$empty_result_dep = 1;
		}
	}


	/**
	 * Funtion to get post count from given term or terms and its/their children
	 *
	 */
	function get_term_post_count( $taxonomy = 'category', $term = '', $args = [] ){
	    // Lets first validate and sanitize our parameters, on failure, just return false
	    if ( !$term )
	        return false;

	    if ( $term !== 'all' ) {
	        if ( !is_array( $term ) ) {
	            $term = filter_var(       $term, FILTER_VALIDATE_INT );
	        } else {
	            $term = filter_var_array( $term, FILTER_VALIDATE_INT );
	        }
	    }

	    if ( $taxonomy !== 'category' ) {
	        $taxonomy = filter_var( $taxonomy, FILTER_SANITIZE_STRING );
	        if ( !taxonomy_exists( $taxonomy ) )
	            return false;
	    }

	    if ( $args ) {
	        if ( !is_array )
	            return false;
	    }

	    // Now that we have come this far, lets continue and wrap it up
	    // Set our default args
	    $defaults = [
	        'posts_per_page' => 1,
	        'fields'         => 'ids'
	    ];

	    if ( $term !== 'all' ) {
	        $defaults['tax_query'] = [
	            [
	                'taxonomy' => $taxonomy,
	                'terms'    => $term
	            ]
	        ];
	    }
	    $combined_args = wp_parse_args( $args, $defaults );
	    $q = new WP_Query( $combined_args );

	    // Return the post count
	    return $q->found_posts;
	}

?>
<div class="container">

		<!-- Header -->
    <header>
        <p class="careers">Signifyd Careers</p>
        <h1 class="page-title">Job Openings</h1>
    </header><!-- .page-header -->

		<!-- Empty result message -->
		<?php
		if(isset($post_not_found)) {
			echo "<div id='post-404' class='no-result'><h2>The job you are looking for is not here. <br />Here are some other jobs.</h2></div>";
		}
		if ($empty_result_dep || $empty_result_loc) {
			echo "<div class='no-result'><h2>Looks like there are no open positions for ";
		}
		if($empty_result_dep) {
			$dep = $params['team'];
			echo "<span class='token-message'>". $dep ."</span>";
		}
		if ($empty_result_dep && $empty_result_loc) {
			echo ' and ';
		}
		if($empty_result_loc) {
			$loc = $params['location'];
			echo "<span class='token-message'>". $loc ."</span>";
		}
		if ($empty_result_dep || $empty_result_loc) {
			echo " right now.</h2><p>We’re growing! Feel free to check back periodically, and here are jobs in other departments.</p></div>";
		}
	 ?>

	 <!-- Content -->
    <div id="jobs-page" class="content-area">

			<!-- Filters -->
			<div class="filters">
				<div class="form-group">
					<select id="team-select">
						<option value="0">All Departments</option>
						<?php foreach ($parent_terms_team as $term_team): ?>
							<?php $count_departments = get_term_post_count( 'jobs_team', $term_team->term_id ); ?>
							<option value="<?= $term_team->term_id; ?>" data-name="<?= $term_team->name; ?>" <?= (strtolower($term_team->name) == $team_url) ? 'selected' : null ?>><?= $term_team->name; ?> (<?= $count_departments ?>)</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group">
					<select id="location-select">
						<option value="0">All Locations</option>
						<?php foreach ($terms_location as $term_location): ?>
							<?php $count_location = get_term_post_count( 'jobs_location', $term_location->term_id ); ?>
							<option value="<?= $term_location->term_id; ?>" data-name="<?= $term_location->name; ?>" <?= (strtolower($term_location->name) == $location_url) ? 'selected' : null ?>><?= $term_location->name; ?> (<?= $count_location ?>)</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group">
					<a id="reset-filters">Reset filters</a>
				</div>
			</div>
			<!-- END Filters -->

			<!-- Get parent term -->
      <?php
      	foreach ($parent_terms_team as $parent_term) :
          $parent_term_query = new WP_Query(array(
              'post_type' => 'jobs',
							'posts_per_page' => -1,
              'tax_query' => array(
                  array(
                      'taxonomy' => 'jobs_team',
                      'field' => 'slug',
                      'terms' => array($parent_term->slug),
                      'operator' => 'IN'
                  )
              )
        	));
      ?>

		<div id="team-<?= $parent_term->term_id; ?>" class="team-wrap<?= ($parent_term->slug == 'general-applications') ? ' general-applications' : ''; ?>">
				<h2><?= $parent_term->name; ?></h2>
				<?php if ($parent_term->slug == 'general-applications') : ?>
					<p>If you’re interested in joining Signifyd but don't see an opportunity that you'd like to apply to, please send us your resume.</p>
				<?php else : ?>
					<div class="team-header">
						<div class="title-col">JOB TITLE</div>
						<div class="team-col">TEAM</div>
						<div class="location-col">LOCATION</div>
						<div class="link-col"></div>
					</div>
				<?php endif; ?>
		<?php if ($parent_term_query->have_posts()) : while ($parent_term_query->have_posts()) : $parent_term_query->the_post(); ?>
			<?php include( plugin_dir_path( __FILE__ ) . 'archive-jobs-loop.php'); ?>
		<?php endwhile; endif; ?>
		</div>
	<?php
		endforeach;
		$parent_term_query = null;
		wp_reset_postdata();
	?>

  	</div><!-- #primary -->
</div><!-- .wrap -->

<?php get_footer(); ?>
