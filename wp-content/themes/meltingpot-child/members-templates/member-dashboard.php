<?php
global $smof_data;

/*  Template Name: Member Dashboard*/
get_header('members'); ?>
</header>

<?php
/*
<section class="memberDashWelc">
<div class="row">
	<div class="large-12 medium-12 columns user-points-wrapper" style="font-family: 'Teko', sans-serif; color: #fff; font-size: 28px !important; font-weight: bold;">
		<?php
		echo do_shortcode('[gamipress_user_rank type="skilllevel" prev_rank="no" current_rank="yes" next_rank="no" current_user="yes" user_id="" columns="1" title="yes" link="no" thumbnail="no" excerpt="no" requirements="no" toggle="no" unlock_button="" earners="" earners_limit="0" layout="left"]');
		echo do_shortcode('[gamipress_points type="pondpoints" thumbnail="no" label="yes" current_user="yes" user_id="" period="" period_start="" period_end="" inline="" columns="1" layout="left"]');
		?>
	</div>
</div>
</section>
*/
?>

<section class="memberbenefits dashboardbenefits">
<!-- <div class="row"> -->
<div>
	<!-- Skills progression timeline/stages -->
	<?php 
	/*
	if( have_rows('dashboard_benefits') ): $i = count(get_field('dashboard_benefits')); ?>
		<div class="section-icons large-2 hide-for-medium columns" style="float: right;">
			<ul class="benefit-wrap">
			<?php while( have_rows('dashboard_benefits') ): the_row(); $gridsize = '';

			if($i == 4) {$gridsize = 'large-12 medium-12';} elseif($i == 3){$gridsize ='large-12 medium-12';} elseif($i == 2){$gridsize ='large-12 medium-12';} ?>
			<li class="<?php echo $gridsize; ?> columns">
			<div class="wrap-benefit">
			<a href="<?php echo the_sub_field('link_page')?>">
			<div class="benefitimage">
			<img src="<?php echo the_sub_field('image'); ?>" alt="<? echo the_sub_field('benefit-title'); ?> icon">
			</div>
			<h4><?php echo the_sub_field('benefit-title'); ?></h4>
			<!-- <p><?php echo the_sub_field('benefit-small-text'); ?></p> -->
			<!-- <span class="BTN"><?php echo the_sub_field('button_text')?></span> -->
			</a>
			</div>
			</li>
			<?php endwhile; ?>
			</ul>
		</div>
		<!-- </div> -->
	<?php endif; 
	*/
	?>

	<div class="large-12 columns" style="padding: 0;">
		<?php
		do_shortcode('[ld_courses_by_categories categories="skating,stickhandling,shooting,passing"]');
		?>
	</div>

	<?php if (get_field('content_after_links')): ?>
	<div class="afterMainLinks"><?php the_field('content_after_links'); ?></div>
	<?php else: ?>
	<?php endif; ?>
</div>
</section>

<!-- Skills Vault -->
<section class="skillsVault Dashboard">
<div class="row">
	<div class="sectionHeader">
		<div class="large-12 columns">
			<h3>Skills Vault</h3>
			<div class="bootstrap-styles">
				<ul class="nav nav-pills nav-fill mb-3" id="skill-types-filter">
					<?php
					$skillTypes = get_terms( array(
						'taxonomy' => 'skill-type',
						'hide_empty' => false,
					) );

					$x = 0;
					foreach($skillTypes as $skillType) {
						?>
						<li class="nav-item">
							<a class="nav-link" href="#<?=$skillType->slug?>"><?=$skillType->name?></a>
						</li>
						<?php
					}
					?>
				</ul>
				<div class="skills-vault-table">
					<a href="#" class="search-button" id="search-button"><i class="fa fa-search"></i></a>
					<table class="table table-striped skills-vault-table" id="skills-vault-table" width="100%">
						<thead>
							<tr>
								<th scope="col" class="title">Skill</th>
								<th class="actions" style="min-width: 75px;">Resources</th>
								<th scope="col" style="min-width: 110px;">Frequency</th>
								<th scope="col" style="min-width: 100px;">Level</th>
								<th scope="col" style="min-width: 100px;">Type(s)</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$skillsQuery = new WP_Query( array(
								'post_type' => 'skills'
							) );
							$skills = $skillsQuery->get_posts();

							if(sizeof($skills) <= 0) {
								?>
								<tr>
									<td colspan="4" class="center-text text-center">There are no skills yet.</td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
								</tr>
								<?php
							} else {
								foreach($skills as $skill) {
									$name = get_the_title($skill->ID);
									$url = get_post_permalink($skill->ID);
									$puckLevel = intval(get_post_meta($skill->ID, 'puck_level', 1));
									$skillExamples = get_field('skill_examples', $skill->ID);
									$videoCode = get_field('video_code', $skill->ID);
									$skillTypes = get_the_terms( $skill->ID, 'skill-type' ); 
									$skillTypeString = '';
									if(sizeof($skillTypes) > 0) {
										$count = 0;
										foreach($skillTypes as $skillType) {
											if (++$count > 1 && $count <= sizeof($skillTypes)) {
												$skillTypeString .= ', ';
											}
											$skillTypeString .= $skillType->name;
										}
									}
									$performanceLevels = get_the_terms( $skill->ID, 'performance-level' ); 
									$performanceLevelString = '';
									if(sizeof($performanceLevels) > 0) {
										$count = 0;
										foreach($performanceLevels as $performanceLevel) {
											if (++$count > 1 && $count <= sizeof($performanceLevels)) {
												$performanceLevelString .= ', ';
											}
											$performanceLevelString .= $performanceLevel->name;
										}
									}
									?>
									<tr>
										<td><a href="<?=$url?>"><?=$name?></a></td>
										<td class="<?=sizeof($skillExamples) > 0 ? 'multiple-actions' : ''?>">
											<?php
											if (!empty($videoCode)) {
												?>
												<a class="BTN action-button"
													data-title="<?=$name?>"
													data-url="<?=$url?>"
													data-button="Full Tutorial"
													data-video="<?=htmlspecialchars($videoCode)?>">
														<i class="fa fa-play"></i>&nbsp;&nbsp;Tutorial
												</a>
												<?php
											}
											if (sizeof($skillExamples) > 0) {
												?>
												<?php /*<a href="<?=get_post_permalink($skillExamples[0]->ID)?>" class="BTN action-button custom-icon">*/ ?>
												<?php
												$examplesList = get_field('examples', $skillExamples[0]->ID);
												$examplesList = array_slice($examplesList, 0, 2);
												$examplesListHtml = '';
												foreach($examplesList as $example) {
													// Load sub field values.
													$gif = $example['gif'];
													$video = $example['video_code'];
													$description = $example['description'];
													$examplesListHtml .= '<div class="example">';
													if (!empty($gif)) {
														$examplesListHtml .= '<img src="' . $gif . '" alt="' . $description . '" data-enlargable />';
													} else if (!empty($video)) {
														$examplesListHtml .= '<div class="videoWrapper">' . $video . '</div>';
													}
													$examplesListHtml .= '</div>';
												}
												?>
												<a class="BTN action-button custom-icon" 
													data-title="<?=get_the_title($skillExamples[0]->ID)?>"
													data-url="<?=get_post_permalink($skillExamples[0]->ID)?>"
													data-button="More"
													data-video="<?=htmlspecialchars(get_field('video_code', $skillExamples[0]->ID, false))?>"
													data-side="<?=htmlspecialchars($examplesListHtml)?>">
														<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 223 232.1" style="enable-background:new 0 0 223 232.1;" xml:space="preserve">
															<path d="M183.7,203.1c1.6-3.2,1.6-3.2,3.6-3.7c-1.4-0.8-2.8-1.8-4.3-2.5c-1.5-0.7-3.1-1-4.6-1.7c-11.6-5.8-22.2-13-32.4-21
																c-0.9-0.7-1.9-1.4-2.8-2.3c-2-1.9-4.4-3-6.9-4c-2.4-1-4.4-2.6-6.3-4.4c-3.5-3.3-7.1-6.5-10.7-9.9c-0.3,0.7-0.6,1.3-0.9,1.8
																c-1.6-0.3-3.2-0.5-4.8-1c-0.6-0.2-1.1-1-1.5-1.6c-0.4-0.6-0.9-1-1.6-1.2c-0.7-0.2-1.6-0.7-1.9-1.2c-0.6-1.2-1.5-1.6-2.6-2
																c-1.9-0.6-1.9-0.6-1.7-2.7c0.3-2.4,0.9-4.6,2.1-6.7c0.4-0.7,0.4-1.1-0.2-1.6c-2.8-2.8-5.6-5.6-8.4-8.4c-0.5-0.5-1-0.6-1.6-0.3
																c-1.8,0.9-3.7,1.7-5.1,3.4c-0.2,0.3-0.9,0.5-1.4,0.4c-2.2-0.3-3.8,0.5-5.2,2.3c-2.4,3-4.8,5.9-6,9.6c-0.2,0-0.2,0.1-0.3,0.1
																c-1.9,0.1-2.1,0-2.4,1.8c-0.5,2.9-1.7,4.8-4.8,5.2c-0.6,0.1-0.9,0.5-0.8,1.2c0.3,1.9-0.4,3.5-1.2,5.1c-2.3,4.7-4.6,9.5-8.2,13.4
																c-1.4,1.5-2,2.9-1.7,4.9c0.1,0.9-0.2,1.5-0.9,2c-1.3,0.9-2.5,1.8-3.7,2.8c-0.2,0.1-0.3,0.4-0.4,0.6c-1,2.8-2.9,4.8-5,6.8
																c-1.9,1.9-3.8,3.8-5.7,5.6c-0.3,0.3-0.9,0.5-1.4,0.6c-2.1,0.4-3.8,1.3-5.3,2.8c-1.5,1.6-3.3,2.3-5.5,2.2c-1.8,0-3.6-0.2-5.3,1
																c-0.8,0.5-2.3,0.1-3.8,0.1c1.1-1.3,1.9-2.4,2.8-3.5c0.1-0.2,0.6-0.2,0.9-0.1c0.6,0.1,1.3,0.4,2.2,0.7c-1-1.7-1.9-3.1-2.8-4.4
																c-0.4-0.6-0.4-1,0.1-1.6c1.7-2.2,3.9-3.7,6.4-4.9c3.5-1.6,6-4.3,8.3-7.3c0.5-0.6,0.6-1.7,0.7-2.5c0.3-1.9,0.4-3.8,1-5.6
																c2-6.2,4.1-12.3,6.3-18.4c0.8-2.4,2.3-4.3,4.1-6.1c0.6-0.6,0.7-1.7,0.8-2.6c0.4-2.3,0.6-4.7,1.2-6.9c1.2-4.2,3.9-7.3,7.7-9.4
																c1.7-0.9,3-2.1,3.9-3.9c1-2.1,1.4-4.1-0.2-6.1c0.9-1.4,1.7-2.8,2.6-4.2c0.7-1,0.6-2,0.7-3.1c0.1-0.7,0.3-1.6,0.7-2.1
																c1.5-2,3.2-3.9,4.7-5.9c0.6-0.7,0.7-2.7,0.2-3.5C69.4,92.4,64.2,85.2,59.1,78c-0.1,0-0.3-0.1-0.3,0c-1.3,1.1-2.6,0.7-4,0.2
																c-1.4-0.5-2.9-0.8-4.4-1.2c-1.5-0.3-2.8-0.9-3.4-2.4c-0.9-2.5-2.6-4.5-4.5-6.3c-0.6-0.5-0.8-1.1-0.8-1.9c0.1-1.4,0.1-2.9,0-4.3
																c-0.1-2.1,0.7-3.8,2.3-5c1.4-1.1,3-2.2,4.5-3.1c0.8-0.5,1.2-1.1,1.1-2.1c-0.2-2.3,0.8-4.2,2.3-5.8c0.3-0.3,0.7-0.7,1-0.8
																c1.4-0.2,1.7-1.2,1.8-2.3c0.1-0.4,0.2-0.8,0.3-1.2c0.3-2,1.3-3.2,3.2-4.2c1.9-0.9,3.5-2.6,5.1-4c2-1.7,3.9-3.4,5.9-5
																c0.4-0.3,1.2-0.4,1.6-0.2c2,0.9,3.7,0.7,5.6-0.4c2.4-1.5,5.1-1.6,7.7-0.4c1.4,0.7,2.7,0.6,4.1,0.1c1.2-0.4,2.4-0.7,3.6-1.1
																c1-0.3,1.9-0.3,2.8,0.3c0.2,0.2,0.5,0.2,0.8,0.2c1.7,0.2,3.5,0.4,5.2,0.6c0.9,0.1,1.5,0,1.8-0.9c0.2-0.4,0.4-0.8,0.6-1.3
																c0.6-1.3,1.2-2.4,3-2.6c1-0.1,2-0.9,2.9-1.6c2-1.6,4.2-2.3,6.8-2.2c1,0,2,0,3,0c0.6,0,1.2,0.1,1.7,0.4c1.7,0.9,3.2,2.1,4.9,2.9
																c2.2,0.9,2.9,2.7,3.2,4.7c0.5,2.7,1,5.5,1.4,8.2c0.3,2.1-0.4,3.8-1.5,5.5c-0.5,0.7-1.1,1.4-1.6,2.1c2.9,4.2,6,8.3,8.7,12.5
																c3,4.8,5.8,9.7,8.7,14.6c0.4,0.8,0.6,1.8,0.7,2.7c0.1,2.1,0.1,4.2,0,6.4c0,0.8,0.2,1.4,0.9,1.8c2.2,1.2,2.5,3.1,2.3,5.5
																c-0.2,3.4-0.1,6.8-1.2,10.2c-0.3,0.9-0.2,2.1-0.1,3.1c0.1,0.7,0.3,1.4,0.7,2c0.6,0.8,0.5,1.6,0.3,2.5c-0.7,2.7-1.5,5.3-3.2,7.7
																c-1.1,1.5-1.6,3.3-1.5,5.2c0.1,1.9,0,3.7,0,5.6c0,0.5,0.1,1.2,0.3,1.6c0.8,1.4,0.7,2.8,0.6,4.3c-0.3,4.4,0.5,8.7,1.2,12.9
																c0.2,1.3,0.9,2.2,2.2,2.5c0.3,0.1,0.7,0.3,0.9,0.5c0.6,1,1.3,0.9,2.3,0.6c2.2-0.9,4.6-0.8,6.8,0c4.9,1.9,9.8,3.1,15.1,2.7
																c0.1,0,0.3,0,0.4,0c1.7,1.1,3-0.1,4.1-1.1c2-1.6,4.2-0.9,6.3-0.9c0.4,0,0.8,0.6,1.2,0.9c1.6,1.5,0.9,1.4,2.8,0.2
																c0.4-0.3,0.8-0.6,1.3-1c1.2,1.5,1.8,3.3,2.1,5c0.4,2.2,0.5,4.4,0.8,6.6c0.4,3,0.9,6,1.3,9c0.6,4.3,0.4,8.5-0.7,12.7
																c-0.4,1.3-1.5,2.4-2.4,3.8c-0.6-0.8-1-1.3-1.4-1.7c0.3-0.4,0.5-0.8,0.8-1.3c-1.6,0-3-0.1-4.4,0c-1.6,0.1-2.8-0.7-3.6-1.9
																c-0.7-1.1-1.1-2.4-1.6-3.6c-0.8-2.2-1.5-4.6-2.5-6.7c-0.6-1.3-1.8-2.4-2.8-3.5c-0.2-0.2-0.8-0.4-1-0.3c-2.2,1-4.5,0.8-6.8,0.7
																c-1.4-0.1-2.5,0.1-3.8,0.8c-1.7,0.9-3.7,1.2-5.6,1.5c-2.8,0.5-5.7,0.8-8.5,1.2c-0.2,0-0.3,0.1-0.5,0.1c-2.1,0.6-2.2,1-0.7,2.5
																c4.8,4.8,10.6,8.1,16.1,12c3,2.2,6.1,4.2,9.3,6.1c3.5,2.2,7.6,3,11.4,4.5c3,1.2,6,1.5,9.2,1.4c6.2-0.1,12.3,0,18.5,0
																c0.8,0,1.2,0.2,1.4,1.1c0.3,1.2,0.7,2.3,1,3.4c0.2,0.6,0,1-0.4,1.4c-1.5,1.6-3.2,2.2-5.4,2.1c-3.1-0.2-6.2,0-9.3-0.1
																c-1.1,0-1.8,0.3-2.3,1.2c-0.4,0.8-1,1-1.9,1c-2.3-0.1-4.6-0.1-6.8,0C186.6,205.1,185,204.7,183.7,203.1z M76.8,96.3
																c1.7-2,3.3-3.9,4.9-5.8c0.3-0.3,0.4-1,0.2-1.4c-0.6-1.8-1.3-3.5-2.1-5.2c-0.3-0.6-0.2-1,0.3-1.5c1.1-1,2.1-2,3.1-3.1
																c0.3-0.4,0.5-0.9,0.6-1.4c0.3-1.4-0.2-2.5-1.5-3.3c-0.6-0.4-1.2-0.9-1.7-1.4c-0.4-0.4-0.7-1-0.7-1.5c-0.1-1.8,0-3.6-0.1-5.4
																c0-0.5-0.3-1-0.6-1.5c-0.8-1.5-1.8-2.8-2.4-4.3c-0.7-1.7-1-3.6-1.4-5.4c-0.8-3.2-0.8-3.2-4-3.1c-0.4,0-0.9,0.3-1.1,0.7
																c-1,1.9-2,3.8-3,5.7c-0.2,0.3-0.3,0.7-0.4,1.1c-0.4,3-0.9,5.9-1.2,8.9c-0.2,1.3-0.6,2.3-1.6,3.2c-1,0.8-1.8,1.8-2.7,2.7
																C66.1,82,71.1,89.4,76.8,96.3z M110.1,135.3c0.9-1.3,1.8-2.5,2.6-3.7c0.2-0.4,0.3-0.9,0.2-1.4c-0.3-3.2-2.8-5.6-6.4-6.3
																c-2.2-0.4-3.9,0.3-4.9,2.1C104.5,129.1,107.3,132.1,110.1,135.3z M189,159.1c0.6-0.2,1.4-0.3,1.6-0.6c0.9-1.3,0-3.6-1.6-4.5
																C189,155.7,189,157.3,189,159.1z M191.3,174.6c-0.8,1-0.3,2-0.4,2.9C192.1,177.6,192.1,177.1,191.3,174.6z"/>
														</svg>
														Examples
												</a>
												<?php
											}
											?>
										</td>
										<td data-order="<?=$puckLevel?>">
										<?php
										$puckRemainder = 5 - $puckLevel;
										for ($x = 0; $x < $puckLevel; $x++) {
											?>
											<svg class="puck" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1em" height="1em" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 512 512"><path d="M0 160c0-53 114.6-96 256-96s256 43 256 96s-114.6 96-256 96S0 213 0 160zm0 82.2V352c0 53 114.6 96 256 96s256-43 256-96V242.2c-113.4 82.3-398.5 82.4-512 0z" /></svg>
											<?php
										}
										for ($x = 0; $x < $puckRemainder; $x++) {
											?>
											<svg class="puck faded" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1em" height="1em" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 512 512"><path d="M0 160c0-53 114.6-96 256-96s256 43 256 96s-114.6 96-256 96S0 213 0 160zm0 82.2V352c0 53 114.6 96 256 96s256-43 256-96V242.2c-113.4 82.3-398.5 82.4-512 0z" /></svg>
											<?php
										}
										?>
										</td>
										<td><?=$performanceLevelString?></td>
										<td><?=$skillTypeString?></td>
									</tr>
									<?php
								}
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Modal for skills vault popups -->
<div class="bootstrap-styles transparent-modal">
	<div class="modal fade skills-vault-modal" id="skillsVaultModal" tabindex="-1" role="dialog" aria-labelledby="skillVaultModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h2 class="modal-title" id="skillVaultModalLabel">Skill</h2>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="medium-8 columns video">
						<div class="videoWrapper"></div>
					</div>
					<div class="medium-4 columns side"></div>
				</div>
				<div class="modal-footer">
					<a class="BTN action" href="#">More</a>
				</div>
			</div>
		</div>
	</div>
</div>
</section>

<?php
global $wpdb;
$history=$wpdb->get_results("select DISTINCT lesson_id from " . $wpdb->prefix . "lessontracker where `user_id`=" . get_current_user_id() . " and `lesson_status` IN ( 5, 2, 1) order by `viewed` desc LIMIT 3",ARRAY_A);

if(count($history) > 0 ) :
?>
<section class="savedContent Dashboard">
<div class="row">

<div class="sectionHeader">
<div class="large-8 medium-7 columns">
<h3>Continue where you left off...</h3>

</div>

<div class="large-4 medium-5 columns">

<a href="/saved-content" class="BTN">View Your Saved Content</a>

</div>
</div>

<div id="lesson_list" class="SavedContent watchlist">
<ul>
<?php foreach($history as $line) {
if (get_field('course_page_type',$line['lesson_id'])!='standalone') :
$course_id=wp_get_post_parent_id($line['lesson_id']);
endif;

if( $course_id == 0 )
$course_id=$line['lesson_id'];


if (get_field('course_page_type',$course_id) == 'module') :
$course_id=wp_get_post_parent_id($course_id);
endif;

if (has_post_thumbnail($course_id)) :
$thumb_id = get_post_thumbnail_id($course_id);
$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'full');
$thumb_url = $thumb_url_array[0];
$course_thumb =' style="background: url(' . $thumb_url . ')no-repeat;background-size:cover;background-position:center;"';
else:
$course_thumb=' style="background: url('.DEFAULT_IMG. ')no-repeat;background-size:cover;background-position:center;"';
endif;

?>
<li class="course-listing training_listing listing_history large-4 medium-6 columns">

<a href="<?php echo get_the_permalink($line['lesson_id']); ?>" class="savedvideo course-content" id="<?php echo $course_id; ?>" >

<div class="coursePrevImage" <?php echo ($course_thumb); ?>></div>

</a>

<h4><a href="<?php echo get_the_permalink($line['lesson_id']); ?>"><?php echo get_the_title($line['lesson_id']); ?></a></h4>

<a href="<?php echo get_the_permalink($line['lesson_id']); ?>" class="BTN">Access</a>

</li>

<?php } ?>
</ul>
</div>
</div>
</section>
<?php endif; ?>
<?php if( get_field('hide_course_feed') == false ) {

$columnOption = $smof_data['course_page_column'];
$columClass= 'large-4';
if( $columnOption == 'one')
	$columClass= 'large-12';
if( $columnOption == 'two')
	$columClass= 'large-6';
if( $columnOption == 'four')
	$columClass= 'large-3';


?>

<? /*
<section class="courses">
<div class="row">
<div class="sectionHeader">
<div class="large-8 medium-7 columns">
<h3>Latest Courses</h3>

</div>

<div class="large-4 medium-5 columns">
<a href="/courses" class="BTN">View All Courses</a>
</div>

</div>
<?php
$course_feed = new WP_Query( array(
'post_type' => array('sfwd-courses'),
'posts_per_page' => 3, // put number of posts that you'd like to display
'meta_query' => array(
	array(
		'key'     => 'available',
		'value'   => true,
		'compare' => '=',

	),
	array(
		'key'=>'hide_from_feed',
		'value'=> true,
		'compare'=>'!=',
     )
)
) );
if( $course_feed->have_posts() ): while( $course_feed->have_posts() ): $course_feed->the_post();
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, '');
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
if( empty($img[0]) )
$img[0] = DEFAULT_IMG;

$course_id = get_the_ID();
$user_id =  get_current_user_id();

$course_status = learndash_course_status( $course_id, $user_id );
$course_steps_count = learndash_get_course_steps_count( $course_id );
$completed = learndash_course_get_completed_steps( $user_id, $course_id );

$ld_course_steps_object = LDLMS_Factory_Post::course_steps( $course_id );
$total                  = $ld_course_steps_object->get_steps_count();

if ( $total > 0 ) {
$percentage = intval( $completed * 100 / $total );
$percentage = ( $percentage > 100 ) ? 100 : $percentage;
} else {
$percentage = 0;
}


?>
<div class="<?php echo $columClass;?> medium-4 columns end">
<div class="blogpreview">
<div class="coursePrevImage" style="background-image: url('<? echo $img[0]; ?>');">
<?php if( get_field('available') ){ ?>
<a href="<?php the_permalink();?>" class="courseImgOver"></a>
<div class="courseProgress">
<?php if($course_status == 'In Progress' ) {?>
<p><?php echo $percentage .'%'; ?></p>
<?php } else { ?>
<p>
<?php  echo $percentage .'%'; ?>
</p>
<?php } ?>
</div>
<?php echo do_shortcode('[learndash_course_progress]') ?> </div>
<?php }else {  ?>
<div class="CourseSoon">Coming Soon</div>
</div>
<?php } ?>
<?php if( get_field('available') ){ ?>
<div class="blogprevtext">
<h5><a href="<?php the_permalink();?>">
<?php the_title();?>
</a></h5>
<p>
<?php the_field('course_description'); ?>
</p>
<a href="<?php the_permalink();?>" class="BTN">
<?php if($course_status == 'In Progress' ) {
echo "Continue course";
}else {
echo "Start Course" ;
}
?>
</a>
<?php }else{ ?>
<div class="blogprevtext nolink">
<h5>
<?php the_title();?>
</h5>
<p>
<?php the_field('course_description'); ?>
</p>
<span class="BTN nolink ">Coming Soon</span>
<?php } ?>
</div>
</div>
</div>

<?php endwhile; endif; ?>

</div>
</section>
*/
?>

<?php } ?>
<!-- Main Section -->


<section>
<div class="row">
<?php
if(have_posts()): while (have_posts()): the_post();
?>

<?php if( get_field('hide_content_feed') == false ) { ?>

<div class="large-8 columns">
<div class="memberLatestContent">
<?php if (get_field('content_before_title')): ?>
<h3><?php the_field('content_before_title'); ?></h3>
<?php else: ?>
<h3>Latest Content</h3>
<?php endif; ?>

<?php $content_to_pull_through = get_field('content_to_pull_through'); ?>

<?php
$new_loop = new WP_Query( array(
'post_type' => array('content-library', 'post','sfwd-courses','sfwd-lessons','sfwd-topic'),
'posts_per_page' => 5, // put number of posts that you'd like to display
'orderby'          => 'date',
'order'            => 'DESC',
) );
?>
<?php if ( $new_loop ) : ?>
<ul class="contentfeed">
<?php while ( $new_loop->have_posts()) : $new_loop->the_post(); ?>
<li>
<div class="medium-3 columns nopad">
<?php
$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
if( empty($image[0]))
$image[0] = DEFAULT_IMG;
?>
<div id="FeatureImage" style="background: url('<?php echo $image[0]; ?>')no-repeat;background-size:cover;background-position:center;">
<span class="postLabel"><?php
$obj = get_post_type_object(get_post_type() );
echo $obj->labels->singular_name;
?></span>

</div>
</div>
<div class="medium-9 columns nopad">
<div class="contenttext">
<h5>
<a href="<?php the_permalink();?>"><?php the_title(); ?></a>
</h5>
<?php if( get_post_type() == 'post')
            the_excerpt();
       if( get_post_type() == 'content-library')
            echo '<p>'.wp_trim_words(get_post_meta( get_the_ID(), 'description_short', true) ).'</p>';
       if( get_post_type() == 'sfwd-lessons' || get_post_type() == 'sfwd-topic' )
            echo '<p>'. wp_trim_words(get_post_meta( get_the_ID(), 'above_media', true) ).'</p>';
        if( get_post_type() == 'sfwd-courses' )
            echo '<p>'.wp_trim_words(get_post_meta( get_the_ID(), 'course_description', true) ).'</p>';
  ?>
<a class="BTN" href="<?php the_permalink();?>">Read more</a> </div>
</div>
</li>
<?php endwhile;?>
</ul>
<?php else: ?>
<?php endif; ?>
<?php wp_reset_query(); ?>

</div>
</div>
<?php } // else condtion ?>
<div class="large-4 columns">
<aside>
<?php get_template_part('template-parts/members/member-sidebar'); ?>
</aside>
</div>
<?php endwhile; endif;?>
</div>
</section>


<?php if(get_field('show_forum_posts')) : ?>

<section class="forumFeed">
	<div class="row">
	<div class="forumPosts">
	<?php if (get_field('community_header')): ?>
	<h3><?php the_field('community_header'); ?></h3>
	<?php else: ?>
		<h3>Latest Community Discussions </h3>
	<?php endif; ?>

	<?php echo do_shortcode('[ipbtopics limit="'.get_field('number_of_posts_to_show').'"]'); ?>
	</div>
</div>
</section>

<?php endif; ?>



<!-- End Main Section -->
<?php  get_footer("members"); ?>
