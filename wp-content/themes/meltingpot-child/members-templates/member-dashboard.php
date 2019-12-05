<?php
global $smof_data;

/*  Template Name: Member Dashboard*/
get_header('members'); ?>
</header>



<section class="memberDashWelc">
<div class="row">
<div class="large-8 medium-7 columns">
<h1>
<?php if (get_field('welcome_message')): ?>
<?php the_field('welcome_message'); ?>
<?php else: ?>
Welcome
<?php endif; ?>
<?php echo  do_shortcode('[mepr-account-info field="first_name"]'); ?></h1>
<p>
<?php if (get_field('welcome_submessage')): ?>
<?php the_field('welcome_submessage'); ?>
<?php else: ?>
To your dashboard
<?php endif; ?>
</p>
</div>
<div class="large-4 medium-5 columns">


<?php if (get_field('welcome_bar_link_text')): ?>
<a href="<?php the_field('welcome_bar_link'); ?>" class="BTN dashBTN">
<?php the_field('welcome_bar_link_text'); ?>
<?php else: ?>
<?php endif; ?>


</a> </div>
</div>
</section>


<section class="memberbenefits dashboardbenefits">
<div class="row">
<?php if( have_rows('dashboard_benefits') ): $i = count(get_field('dashboard_benefits')); ?>

<ul class="benefit-wrap">
<?php while( have_rows('dashboard_benefits') ): the_row(); $gridsize = '';

if($i == 4) {$gridsize = 'large-3 medium-6';} elseif($i == 3){$gridsize ='large-4 medium-4';} elseif($i == 2){$gridsize ='large-6 medium-6';} ?>
<li class="<?php echo $gridsize; ?> columns">
<div class="wrap-benefit">
<a href="<?php echo the_sub_field('link_page')?>">
<div class="benefitimage">
<img src="<?php echo the_sub_field('image'); ?>" alt="<? echo the_sub_field('benefit-title'); ?> icon">
</div>
<h4><?php echo the_sub_field('benefit-title'); ?></h4>
<p><?php echo the_sub_field('benefit-small-text'); ?></p>
<span class="BTN"><?php echo the_sub_field('button_text')?></span>
</a>
</div>
</li>
<?php endwhile; ?>
</ul>
<?php endif; ?>

<?php if (get_field('content_after_links')): ?>
<div class="afterMainLinks"><?php the_field('content_after_links'); ?></div>
<?php else: ?>
<?php endif; ?>


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
