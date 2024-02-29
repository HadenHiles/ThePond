<?php 

if ( get_field( 'select_blog' ) ) {
	$testids = get_field( 'select_blog' );
} else {
	$testids = '';
}
$numbercase = get_field('number_of_blog_columns')
?>
<section class="recentpost">
<div class="row <?php if (($numbercase) == '1'): ?>rowNarrow<?php endif; ?>">
       <div class="posttitle">
    <h3><?php the_field('blog_heading'); ?></h3>
    <p><?php the_field('blog_sub_heading'); ?></p>
       </div>
	<?php
	$args = array( 
		'posts_per_page' => $numbercase,
		'orderby' => 'rand',
		'post_type' => 'post',
		'post__in' => $testids
	);
	$the_query = new WP_Query( $args );
	?>
	<?php if ( $the_query->have_posts() ) : ?>
	<?php while ( $the_query->have_posts() ) : $the_query->the_post(); 
	$img = wp_get_attachment_url(get_post_thumbnail_id($post->ID),"Full"); 
	?>
    <div class="<?php if (($numbercase) == '1'): ?>large-12<?php endif;?><?php if (($numbercase) == '2'): ?>large-6<?php endif; ?><?php if (($numbercase) == '3'): ?>large-4<?php endif; ?> medium-12 columns">
	<div class="blogpreview">
		
        <div class="<?php if (($numbercase) == '1'): ?>large-6<?php else: ?>large-12<?php endif; ?> medium-12 columns nopad">
			<a href="<?php the_permalink();?>" class="blogprevimage <?php if (($numbercase) == '1'): ?><?php else: ?>shorterprevimage<?php endif; ?>" style="background: url('<?php echo $img;?>')no-repeat;background-size:cover;background-position:center;"></a>
        </div>

        <div class="<?php if (($numbercase) == '1'): ?>large-6<?php else: ?>large-12<?php endif; ?> medium-12 columns nopad">
        <div class="blogprevtext">
        <h3>BLOG POST:</h3>
		<h3><a href="<?php the_permalink();?>" alt="Article - <?php the_title();?>"><?php the_title();?></a></h3>
		<p class="timestamp"><?php  if ( get_field('author')): ?><span><i class="fa fa-user"></i> <?php the_field('author'); ?></span><?php endif; ?> <span><i class="far fa-calendar-alt"></i> <?php echo get_the_date();?></span> <span><i class="far fa-folder"></i> <?php $cat = get_the_category(); echo $cat[0]->name?></span></p>
		<p class="excerpt"><?php $customexerpt = get_the_content(); echo wp_trim_words( $customexerpt , '14' ); ?></p>
        <a href="<?php the_permalink();?>" alt="Article - <?php the_title();?>" class="BTN">Read More</a>
	    </div>
        </div>
    </div>
    </div>
<?php endwhile; endif; wp_reset_postdata(); wp_reset_query(); ?>
</div>
</section>