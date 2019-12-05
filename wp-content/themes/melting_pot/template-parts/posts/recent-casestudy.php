<?php ?>
<section class="recentpost casestudy">
<div class="row">
     <div class="posttitle">
        <h3>Case Study</h3>
    <p>We've worked with a lot of people, here's a detailed look at one of our recent clients.</p>
    </div>
	<?php
	$args = array( 
		'posts_per_page' => 3,
		'orderby' => 'ASC',
		'post_type' => 'enable_case_study',
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
        <h3>CASE STUDY:</h3>
		<h3><a href="<?php the_permalink();?>" alt="Article - <?php the_title();?>"><?php the_title();?></a></h3>
		<p class="timestamp">
        <span><i class="far fa-walking"></i><?php the_field('client_name'); ?></span>
        <?php  if ( get_field('company')): ?><span><i class="far fa-building"></i> <?php the_field('company'); ?></span><?php endif; ?>
        <span><i class="fas fa-map-marker-alt"></i> <?php the_field('location'); ?></span></p>
		 <p class="excerpt"><?php $customexerpt = get_the_content(); echo wp_trim_words( $customexerpt , '14' ); ?></p>
        <a href="<?php the_permalink();?>" alt="Case Study - <?php the_title();?>" class="BTN">Learn More</a>
	    </div>
        </div>
    </div>
</div>

<?php endwhile; endif; wp_reset_postdata(); wp_reset_query(); ?>

</div>
</section>





					