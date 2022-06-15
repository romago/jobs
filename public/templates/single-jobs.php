<?php get_header(); ?>

<?php if(have_posts()) : while(have_posts()) : the_post(); ?>
	<?php
		// Get meta fields
		$terms_col = get_the_terms( get_the_ID(), 'jobs_location' );
    if ( $terms_col && ! is_wp_error( $terms_col ) ) {
      $term_links = [];
      foreach ( $terms_col as $term_col ) {
          $term_links[] = $term_col->name;
      }
      $all_terms = join( ' • ', $term_links );
		}
		$post_date = get_the_date( 'Y-m-d' );
		$valid_date = date("Y-m-d", strtotime("+2 month", strtotime($post_date)));
		$content = get_the_content();
		$clear_text = wp_strip_all_tags($content);
		$clear_text = str_replace(array("\n", "\r"), '', $clear_text);
		$clear_text = str_replace(array('&nbsp;', '&#039;', '&amp;', '*', '"', "'"), ' ', $clear_text);

	?>
	<div class="container container-job">
		<div id="wrapper-list">
			<div class="breadcrumbs">
				<a href="/jobs/" class="breadcrumb-item">All Import jobs</a>
			</div>
			<div class="title-wrap">
				<h1><?php the_title(); ?></h1>
				<a href="#" class="button-main anchor-form">Apply now</a>
			</div>
			<div class="lacation"><?= $all_terms ?></div>
			<?php the_content(); ?>

			<!-- Create JSON LD for Google Structure Data -->
			<script type="application/ld+json">
			  {
					"@context":"schema.org",
					"@type":"JobPosting",
					"hiringOrganization":{
						"@type":"Organization",
						"name":"Signifyd",
						"logo":"https://recruiting.cdn.greenhouse.io/external_greenhouse_job_boards/logos/000/012/551/resized/Primary-Medium-White_Background-TEST.png?1620240934"},
						"title":"<?= the_title(); ?>",
						"datePosted":"<?= $post_date; ?>",
						"employmentType": "Full-time",
						"baseSalary": {
						  "@type": "MonetaryAmount",
						  "currency": "USD",
						  "value": {
						    "@type": "QuantitativeValue",
						    "value": 0.00,
						    "unitText": "HOUR"
						  }
						},
						"jobLocation":{
							"@type":"Place",
							"address":{
								"@type": "PostalAddress",
								"addressLocality": "San Jose",
								"streetAddress": "2540 North First Street",
								"addressRegion": "CA",
								"addressCountry": "US",
								"postalCode": 95131
							}
						},
						"validThrough": "<?= $valid_date; ?>",
						"description":"<?= $clear_text; ?>"
					}
			</script>
			<!-- iFrame GH -->
			<div id="scroll-top"></div>
			<div id="grnhse_app"></div>
			<div id="spiner">
				<p>Loading, please  wait <span class="loading"></span></p>
				<svg xml:space="preserve" style="height:80px;width:80px;background:#fff;shape-rendering:auto" viewBox="0 0 35.7 32" y="0" x="0" xmlns="http://www.w3.org/2000/svg" id="signifyd-logo" version="1.1" width="68" height="68">
					<g class="ldl-scale" style="transform-origin:50% 50%;transform:rotate(0deg) scale(.8,.8)">
						<g class="ldl-ani" style="transform-origin:17.85px 16px;transform:rotate(0deg);animation:2.38095s linear 0s infinite normal forwards running spin-logo">
							<g class="ldl-layer">
								<path d="M35.5 15.6L27 .9V.8c-.1-.1-.1-.1-.2-.1H9.4c-.3 0-.5.1-.6.4L.2 15.6v.5l4.2 7.3v.1L8.7 31c.1.2.4.4.6.4h17c.3 0 .5-.1.6-.4l4.3-7.4 4.2-7.3c.3-.2.3-.4.1-.7zm-13.8 7h-6.8l3.4-5.9h6.8l-3.4 5.9zM10.2 16l3.4-5.9L17 16l-3.4 5.9-3.4-5.9zm8.1-.7l-3.4-5.9h6.8l3.4 5.9h-6.8zm3.4-7.4h-6.8L18.3 2h6.8l-3.4 5.9zM9.8 2h6.8L13 8.2l-4 7H2.1L9.8 2zm-.9 14.7l3.4 5.9H5.5l-3.4-5.9h6.8zM9.8 30l-3.4-5.9h15.3l3.4 5.9H9.8zm16.6-.7L23 23.4l3.4-5.9 3.4 5.9-3.4 5.9zm4.2-7.4L23 8.6l3.4-5.9L34 16l-3.4 5.9z" style="fill:#00a3e0"/>
							</g>
						</g>
					</g>
					<style id="spin-logo">@keyframes spin-logo{0%{animation-timing-function:cubic-bezier(.5856,.0703,.4143,.9297);transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>
				</svg>
			</div>
		</div>
	</div>
<?php endwhile; endif; ?>
<?php get_footer(); ?>
