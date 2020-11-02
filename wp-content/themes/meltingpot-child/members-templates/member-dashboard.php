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

<section class="profile Dashboard">
	<div class="row">

		<div class="sectionHeader">
			<div class="large-12 columns" id="courses">
				<?=do_shortcode('[ld_profile course_points_user="false" show_quizzes="false"]');?>

				<?php
				/*
				$hthMeprUser = new HthMeprUser();
				$user_id = get_current_user_id();
				$membershipIds = $hthMeprUser->get_memberships($user_id);
				// get the 'coach' membership ID
				$coachMembershipId = null;
				if ($posts = 
					get_posts(
						array( 
							'name' => 'coach', 
							'post_type' => 'memberpressproduct',
							'post_status' => 'publish',
							'posts_per_page' => 1
						)
					)
				) {
					$coachMembershipId = $posts[0]->ID;
				}

				if(in_array($coachMembershipId, $membershipIds)) {
					?>
					<div class="clearfix"></div>
					<br />
					<br />
					<br />
					<h2>Your Team (Sub Accounts)</h2>
					<br />
					<div class="bootstrap-styles coach-team">
						<div class="skills-list">
							<?php
							$subscriptionObjects = $hthMeprUser->get_subscriptions($user_id);

							foreach ($subscriptionObjects as $key => $value) {
								if ($value->product_id != $coachMembershipId) {
									unset($subscriptionObjects[$key]);
									$subscriptionObjects = array_values($subscriptionObjects);
								}
							}

							global $wpdb;
							$table_name = $wpdb->prefix . "mepr_corporate_accounts";
							$corporateAccounts = array();
							foreach ($subscriptionObjects as $subscriptionObject) {
								$query =    "SELECT uuid FROM $table_name
											WHERE user_id = %d
											AND obj_id = %d
											AND obj_type = %s
											AND `status` = %s";
								$results = $wpdb->get_results( $wpdb->prepare(
									$query,
									$user_id,
									$subscriptionObject->subscription_id,
									'subscriptions',
									'enabled')
								);
								array_push($corporateAccounts, $results[0]);
							}

							$hasASubAccount = false;
							if (class_exists('MPCA_Corporate_Account')) {
								foreach($corporateAccounts as $corporateAccount) {
									$ca = MPCA_Corporate_Account::find_by_uuid($corporateAccount->uuid);
			
									$perpage = 15;
									$currpage = 1;
									$search = '';
									$res = $ca->sub_account_list_table('last_name','ASC',$currpage,$perpage,$search);
									$subAccounts = $res['results'];

									foreach ($subAccounts as $subAcc) {
										$hasASubAccount = true;
										?>
										<div class="card skill no-hover" style="width: 100%;">
											<div class="card-body content">
												<span class="title">
													<b><?=$subAcc->user_login?></b> (<?=$subAcc->first_name?> <?=$subAcc->last_name?>)
													<br />
													<span style="font-size: 0.8em;"><?=$subAcc->user_email?></span>
												</span>
												<div class="right">
													<span class="pond-points" data-toggle="tooltip" title="Pond Points">
														<?=do_shortcode('[mycred_my_balance user_id="'  . $subAcc->ID . '"]')?>
														<span class="fa-stack fa-1x" style="float: right; top: -10px; left: 5px;">
															<i class="fas fa-square fa-stack-1x square"></i>
															<i class="fab fa-pied-piper-pp fa-stack-1x fa-inverse pp"></i>
														</span>
													</span>
												</div>
											</div>
										</div>
										<?php
									}
								}

								if (sizeof($corporateAccounts) <= 0 || !$hasASubAccount) {
									?>
									<div class="card skill no-hover" style="width: 100%;">
										<div class="card-body content" style="justify-content: center;">
											<span class="title" style="padding: 2.5px;">
												You currently have no players on your team.
											</span>
										</div>
									</div>
									<?php
								}
							}
							?>
						</div>
					</div>
					<div style="display: flex; justify-content: center;">
						<a class="BTN" href="/account/?action=subscriptions" style="float: none;">Manage</a>
					</div>
					<?php
				} else {
				}
				*/
				?>
			</div>
		</div>
	</div>
</section>

<!-- Skills Vault -->
<section class="skillsVault Dashboard" id="skills-vault">
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
								'post_type' => 'skills',
								'posts_per_page' => -1
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
									$pros = get_field('skill_pros', $skill->ID);
									$cons = get_field('skill_cons', $skill->ID);
									$whenToUseIt = get_field('when_to_use_it', $skill->ID);
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
											if ((!empty($pros) && sizeof($pros) > 0) || (!empty($cons) && sizeof($cons) > 0) || !empty($whenToUseIt)) {
												$breakdownDetails = '';
												//Pros
												if (sizeof($pros) > 0 && !empty($pros)) {
													$breakdownDetails .=	
													'<div class="card">'.
														'<div class="card-body">
															<div class="ListWithHeading">';
																$breakdownDetails .= 
																'<h2>Pros</h2>'.
																'<ol class="list">';
																	// Loop through rows.
																	foreach($pros as $pro) {
																		$breakdownDetails .=
																		'<li class="item">'.
																			'<div class="title">' . $pro['title'] . '</div>'.
																			'<div class="content">' . $pro['content'] . '</div>'.
																		'</li>';
																	}
																$breakdownDetails .= '</ol>';
															$breakdownDetails .= '</div>'.
														'</div>'.
													'</div>';
												}
												//Cons
												if (sizeof($cons) > 0 && !empty($cons)) {
													$breakdownDetails .=	
													'<div class="card">'.
														'<div class="card-body">
															<div class="ListWithHeading">';
																$breakdownDetails .= 
																'<h2>Cons</h2>'.
																'<ol class="list">';
																	// Loop through rows.
																	foreach($cons as $con) {
																		$breakdownDetails .=
																		'<li class="item">'.
																			'<div class="title">' . $con['title'] . '</div>'.
																			'<div class="content">' . $con['content'] . '</div>'.
																		'</li>';
																	}
																$breakdownDetails .= '</ol>';
															$breakdownDetails .= '</div>'.
														'</div>'.
													'</div>';
												}
												//When to use it
												if (!empty($whenToUseIt)) {
													$breakdownDetails .= 
													'<div class="card">'.
														'<div class="card-body">'.
															'<h2>When To Use It</h2>'.
															$whenToUseIt .
														'</div>'.
													'</div>';
												}
											}
											if (!empty($videoCode)) {
												?>
												<a class="BTN action-button"
													data-title="<?=$name?>"
													data-url="<?=$url?>"
													data-button="Full Breakdown"
													data-video="<?=htmlspecialchars($videoCode)?>"
													data-side="<?=htmlspecialchars($breakdownDetails)?>">
														<i class="fa fa-play"></i>&nbsp;&nbsp;Breakdown
												</a>
												<?php
											}
											if (sizeof($skillExamples) > 0) {
												?>
												<?php /*<a href="<?=get_post_permalink($skillExamples[0]->ID)?>" class="BTN action-button custom-icon">*/ ?>
												<?php
												$examplesList = get_field('examples', $skillExamples[0]->ID);
												$exampleVideoCode = get_field('video_code', $skillExamples[0]->ID);
												$examplesList = array_slice($examplesList, 0, 4);
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
												
												if (sizeof($examplesList) > 0) {
													?>
													<a class="BTN action-button custom-icon" 
														data-title="<?=get_the_title($skillExamples[0]->ID)?>"
														data-url="<?=$url?>" <?php /* <?=get_post_permalink($skillExamples[0]->ID)?> */ ?>
														data-button="More Examples"
														<?php
														$vidCode = htmlspecialchars(get_field('video_code', $skillExamples[0]->ID, false));
														$sideOnly = "all";
														if (!empty($exampleVideoCode)) {
															$sideOnly = "side";
															?>
															data-video="<?=$vidCode?>"
															<?php
														}
														?>
														data-<?=$sideOnly?>="<?=htmlspecialchars($examplesListHtml)?>">
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

<section class="challenges-dashboard Dashboard" id="challenges">
	<?=do_shortcode('[list_challenges title="Latest Challenges" btn="All Challenges" btn-url="/challenges"]')?>
</section>

<!-- End Main Section -->
<?php  get_footer("members"); ?>
