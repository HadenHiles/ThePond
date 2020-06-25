<?php
get_header("members");
if(has_post_thumbnail()) {
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, ''); 
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
}
?>
</header>
<!-- Main Section -->
<section class="clbHeader">
<div class="row">
<div class="large-8 medium-8 columns">
<h1><?php the_title(); ?></h1>
<?php 
$term_list = wp_get_post_terms(get_the_ID(), 'library_category', array("fields" => "all"));
if($term_list) { 
foreach( $term_list as $term) {
?>

<a class="clCatLink" href="<?php echo get_term_link($term); ?>"><?php echo $term->name; ?></a>
<?php } 
} ?>
</div>

<div class="large-4 medium-4 columns">
<a class="backBTN" href="/content-library/">
<i class="fas fa-angle-left"></i> All Library Items</a>
</div>
</div>
</section>
<section class="memberContent">
<div class="row">
<?php 
if(have_posts()): while (have_posts()): the_post();
?>
<div class="large-8 medium-8 columns">
<main role="main">
<article>
<div class="CourseContent">
<main role="main">
<article>
<?php get_template_part('template-parts/courses/lesson-topic-fields'); ?>
<?php get_template_part('template-parts/courses/lesson-downloads'); ?>
	<div class="cl-history">
<?php get_template_part('template-parts/courses/coursehistory'); ?>	
		</div>
<?php the_content();?>
</article>
</main>	
</div>
</div>

<div class="large-4 medium-4 columns">
<?php  if ( has_post_thumbnail() ) { ?>
<?php the_post_thumbnail('full'); ?>
<?php } ?>


<div class="challenge-scores" id="challenge-scores">
	<div class="ld-section-heading">
		<h2>Your Scores</h2>
	</div>
	<div class="scores" id="scores">
		<i class="fa fa-spinner fa-spin" style="align-self: center; margin: 2% auto; position: relative; z-index: 5;"></i>
	</div>
	<div class="add-score">
		<input type="hidden" name="challenge_id" id="challenge-id" value="<?php echo get_the_ID() ?>" />
		<input type="hidden" name="user_id" id="user-id" value="<?php echo get_current_user_id() ?>" />
		<label for="challenge-score" id="success-message" class="success message">Score added</label>
		<label for="challenge-score" id="error-message" class="error message">Failed to add score</label>
		<input type="number" name="score" id="challenge-score" step="0.01" min="0" placeholder="Add your new best score" />
		<a href="#" class="add-score-button" id="add-score"><i class="fa fa-plus-circle"></i></a>
	</div>
</div>






<div class="relatedFeed">
<h4>More Challenges</h4>
<?php
$term_list = wp_get_post_terms(get_the_ID(), 'library_category', array("fields" => "ids"));
$arg = array(
'post_type' => 'content-library',
'post_per_page' => 5,
'post__not_in' => array(get_the_ID()),
'tax_query' => array(
array(
'taxonomy'=> 'library_category',
'field' => 'id',
'terms' => $term_list,
),
),
);
$newQuery = new WP_Query($arg);
?>
<ul>
<?php
if($newQuery->have_posts()) : while( $newQuery->have_posts()): $newQuery->the_post();
?>
<li><a href="<?php the_permalink(); ?>"><?php the_title();?></a> </li>
<?php endwhile; endif; wp_reset_query();?>
</ul>
</div>
</div>

<?php endwhile; endif;?>
</div>
</section>

<?php  get_footer("members"); ?>