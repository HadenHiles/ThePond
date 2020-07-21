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
								<th scope="col" style="min-width: 180px;">Skill</th>
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
								</tr>
								<?php
							} else {
								foreach($skills as $skill) {
									$name = get_the_title($skill->ID);
									$url = get_post_permalink($skill->ID);
									$puckLevel = intval(get_post_meta($skill->ID, 'puck_level', 1));
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
										<td>
										<?php
										for ($x = 0; $x < $puckLevel; $x++) {
											?>
											<svg class="puck" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1em" height="1em" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 512 512"><path d="M0 160c0-53 114.6-96 256-96s256 43 256 96s-114.6 96-256 96S0 213 0 160zm0 82.2V352c0 53 114.6 96 256 96s256-43 256-96V242.2c-113.4 82.3-398.5 82.4-512 0z" /></svg>
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
