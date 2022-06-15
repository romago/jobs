<div class="job-wrap">
	<div class="title-col">
		<h3 class="job-title"><a href="https://boards.greenhouse.io/signifyd95/jobs/<?= get_post_meta(get_the_ID(), 'job_id')[0]; ?>" target="_blank"><?= the_title(); ?></a></h3>
	</div>
	<div class="team-col" data-value="<?= $parent_term->term_id; ?>">
			<?php
				$teams = get_the_terms(get_the_ID(), 'jobs_team');
				echo $teams[0]->name;
			?>
	</div>
	<div class="location-col">
			<?php
				$locations = get_the_terms(get_the_ID(), 'jobs_location');
				$locations_keys = array_keys($locations);
				$last_key = end($locations_keys);
				foreach($locations as $key => $value) {
					echo '<span data-value="'. $value->term_id .'">'. $value->name .'</span>';
				}
			?>
	</div>
	<div class="link-col">
		<a href="https://boards.greenhouse.io/signifyd95/jobs/<?= get_post_meta(get_the_ID(), 'job_id')[0]; ?>" target="_blank" class="btn btn-default">Learn More</a>
	</div>
</div>
