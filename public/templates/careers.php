<?php
	// Get parent terms for Departments
	$parent_terms_team = get_terms(array('taxonomy' => 'jobs_team', 'parent' => 0, 'orderby' => 'slug'));
	// Check URL for location
	$term = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
	global $post;
?>
<div class="container careers-page <?= ($term == 'careers') ? 'hidden' : ''; ?>">
    <h3 class="block-title">Check out the openings</h3>

	 <!-- Content -->
    <div id="jobs-page">

	<!-- Get parent term -->
	<?php foreach ($parent_terms_team as $parent_term) :
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
		<?php if ($parent_term_query->have_posts()) : while ($parent_term_query->have_posts()) : $parent_term_query->the_post();
				$post_terms = get_the_terms( $post->ID, 'jobs_location' );
				foreach($post_terms as $termin){
					if (stripos($termin->slug, $term) !== FALSE) {
						include( plugin_dir_path( __FILE__ ) . 'archive-jobs-loop.php');
					}
				}
			?>
		<?php endwhile; endif; ?>
		</div>
	<?php
		endforeach;
		$parent_term_query = null;
		wp_reset_postdata();
	?>

  	</div><!-- #primary -->
		<div class="no-jobs hidden"><h2 style="color: #1faade;">Looks like there are no open positions for this office right now.</h2><p>We’re growing! Feel free to check back periodically.</p><a href="/jobs/" class="orange-button">View All Jobs</a></div>
</div><!-- .wrap -->
<script>
	let container = jQuery('#jobs-page');
	container.contents().each(function() {
	    if(this.nodeType === Node.COMMENT_NODE) {
	        jQuery(this).remove();
	    }
	});
	if(jQuery.trim(container.html()) == '') {
		jQuery('.no-jobs').removeClass('hidden');
		jQuery('h3.block-title').addClass('hidden');
	}
	if (jQuery('#signifyd-offices').hasClass('careers')) {
		jQuery('h3.block-title').addClass('hidden');
	}
	jQuery('.team-wrap').each(function(){
		if(jQuery(this).find('.job-wrap').length == 0) {
			jQuery(this).addClass('hidden');
		}
	});
</script>
<div id="signifyd-offices" class="<?= $term ?>">
	<div class="container">
		<h3 class="text-center">Check out our other offices for opportunities</h3>
		<div class="container-location-icon">
			<div class="location-icon san-jose">
				<a href="/careers/san-jose" target="_self" itemprop="url">
					<img class="fl-photo-img" src="<?= plugins_url() ?>/jobs/public/images/san-jose-icon.jpg" alt="San Jose Careers" title="San Jose Careers">
					<p>San Jose</p>
				</a>
			</div>
			<div class="location-icon new-york-city">
				<a href="/careers/new-york-city/" target="_self" itemprop="url">
					<img class="fl-photo-img" src="<?= plugins_url() ?>/jobs/public/images/new-york-icon.jpg" alt="New York Careers" title="New York Careers">
					<p>New York</p>
				</a>
			</div>
			<div class="location-icon denver">
				<a href="/careers/denver/" target="_self" itemprop="url">
					<img class="fl-photo-img" src="<?= plugins_url() ?>/jobs/public/images/denver-icon.jpg" alt="Denver Careers" title="Denver Careers">
					<p>Denver</p>
				</a>
			</div>
			<div class="location-icon belfast">
				<a href="/careers/belfast/" target="_self" itemprop="url">
					<img loading="lazy" class="fl-photo-img" src="<?= plugins_url() ?>/jobs/public/images/belfast-icon.jpg" alt="Belfast Careers" title="Belfast Careers">
					<p>Belfast</p>
				</a>
			</div>
			<div class="location-icon london">
				<a href="/careers/london/" target="_self" itemprop="url">
					<img class="fl-photo-img" src="<?= plugins_url() ?>/jobs/public/images/london-icon.jpg" alt="London Careers" title="London Careers">
					<p>London</p>
				</a>
			</div>
			<div class="location-icon brazil">
				<a href="/careers/sao-paulo/" target="_self" itemprop="url">
					<img class="fl-photo-img" src="<?= plugins_url() ?>/jobs/public/images/brazil-icon.jpg" alt="São Paulo Careers" title="São Paulo Careers">
					<p>São Paulo</p>
				</a>
			</div>
			<div class="location-icon mexico">
				<a href="/careers/mexico-city/" target="_self" itemprop="url">
					<img class="fl-photo-img" src="<?= plugins_url() ?>/jobs/public/images/mexico-icon.jpg" alt="Mexico City Careers" title="Mexico City Careers">
					<p>Mexico City</p>
				</a>
			</div>
		</div>
	</div>
</div>
