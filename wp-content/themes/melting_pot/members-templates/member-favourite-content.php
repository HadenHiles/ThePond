<?php 

/*Template Name: Member Favorite Content*/ 

global $smof_data;
get_header('members');
if(has_post_thumbnail()) {
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, ''); 
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
$current_user = wp_get_current_user();	
}

?>
</header>
<!-- section-->

<?php get_template_part('template-parts/member-page-header'); ?>


<section class="MemberContent">
<div class="row">
<div class="accountbox">
<div class="content_with_sidebar col-twothird fl"> 
<div class="large-12 columns">
<?php if(have_posts()): while (have_posts()): the_post(); ?>
<?php the_content();?>
<div id="lesson_list" class="SavedContent watchlist">
<h3 class="sectionhead watchhead">My Watchlist</h3>
<div class="watchlist active">
<?php echo do_shortcode('[lessonhistory type="tracking" status="5"]'); ?></div>	
</div>  
<?php endwhile; endif;?>
</div>

<div class="large-8 medium-8 columns">
<div id="lesson_list" class="SavedContent">
<div class="filtered_content history_completed active">
<h3 class="sectionhead"><i class="fa fa-check-circle"></i> Completed</h3>
<?php echo do_shortcode('[lessonhistory type="tracking" status="3"]'); ?></div>
<div class="filtered_content history_bookmarks">
<h3 class="sectionhead"><i class="fas fa-bookmark"></i> Bookmarks</h3>
<?php echo do_shortcode('[lessonhistory type="tracking" status="1"]'); ?></div>
<div class="filtered_content history_favorites">
<h3 class="sectionhead"><i class="fas fa-heart"></i> Favorites</h3>
<?php echo do_shortcode('[lessonhistory type="tracking" status="2"]'); ?></div>
</div> 
</div>

<div class="large-4 medium-4 columns">
<aside>
<nav id="savedContentTabList">
<ul id="tabs">
<li><a class="filter_option active" data-tab="history_completed"><i class="fa fa-check-circle"></i> <?php echo (get_field('lang_tracker_completed','options') ? get_field('lang_tracker_completed','options') : "Completed"); ?></a></li>
<li><a class="filter_option" data-tab="history_bookmarks"><i class="fas fa-bookmark"></i> <?php echo (get_field('lang_tracker_bookmarks','options') ? get_field('lang_tracker_bookmarks','options') : "Bookmarks"); ?></a></li>
<li><a class="filter_option" data-tab="history_favorites"><i class="fas fa-heart"></i> <?php echo (get_field('lang_tracker_favorites','options') ? get_field('lang_tracker_favorites','options') : "Favorites"); ?></a></li>
<li><a href="/account" alt="Account"><i class="fas fa-user-circle"></i> My Account</a></li>
</ul>
</nav>
	
<?php get_template_part('template-parts/member-sidebar'); ?>
		
</aside>
</div>

</div>
</div>
</div><!--row-->
</section>
<?php get_footer('members');?>