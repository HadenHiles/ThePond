

<?php 
?>
<section class="recentpost">
<div class="row">
       <div class="posttitle">
    <h3>Recent blog</h3>
    <p>Take a lot at our most recent blog post, full of insightful information.</p>
       </div>
	<?php
	$args = array( 
		'posts_per_page' => 3,
		'orderby' => 'rand',
		'post_type' => 'post',
	);
	$the_query = new WP_Query( $args );
	?>
	<?php if ( $the_query->have_posts() ) : ?>
	<?php while ( $the_query->have_posts() ) : $the_query->the_post(); 
	$img = wp_get_attachment_url(get_post_thumbnail_id($post->ID),"Full"); 
	?>
    
    <div class="large-4 medium-12 columns">
	<div class="blogpreview">
		
        <div class="large-12 medium-12 columns nopad">
			<a href="<?php the_permalink();?>" class="blogprevimage shorterprevimage" style="background: url('<?php echo $img;?>')no-repeat;background-size:cover;background-position:center;"></a>
        </div>

        <div class="large-12 medium-12 columns nopad">
        <div class="blogprevtext">
        <h3>RECENT BLOG POST:</h3>
		<h3><a href="<?php the_permalink();?>" alt="Article - <?php the_title();?>"><?php the_title();?></a></h3>
		<p class="timestamp"><?php  if ( get_field('author')): ?><span><i class="fas fa-user"></i> <?php the_field('author'); ?></span><?php endif; ?> <span><i class="far fa-calendar-alt"></i> <?php echo get_the_date();?></span> <span><i class="far fa-folder"></i> <?php $cat = get_the_category(); echo $cat[0]->name?></span></p>
		<p class="excerpt"><?php $customexerpt = get_the_content(); echo wp_trim_words( $customexerpt , '14' ); ?></p>
        <a href="<?php the_permalink();?>" alt="Article - <?php the_title();?>" class="BTN">Read More</a>
	    </div>
        </div>
    </div>
    </div>

<?php endwhile; endif; wp_reset_postdata(); wp_reset_query(); ?>
</div>
</section>


<?php  if ( get_field('author')): ?>
		
					<?php endif; ?>
