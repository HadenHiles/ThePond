<?php get_header(); ?>
</header>

<!-- section-->
<section class="MainContent testimonialpage">
<div class="row">
		<div class="large-12 columns">
        <h1>What our clients say:</h1>
			<main>
            <ul>
			<?php 
			if(have_posts()):while(have_posts()):the_post();
            $img = wp_get_attachment_url(get_post_thumbnail_id($post->ID),"Full");
			?>
            <li class="large-6 columns">    
						<div class="testimonial">
										
                    
			    	<h4>"<?php the_title();?>test"</h4>
				    <?php the_content();?>
				    <div class="testimage" style="background: url('<?php echo $img;?>')no-repeat;background-size:cover;background-position:center;"></div>
                    <div class="testcontent">
                    <p class="starrating"><?php the_field('star_rating'); ?></p>
                    <p class="author"><strong><?php the_field('testimonial_author'); ?></strong></p>
                    <p class="locationcompany"><?php the_field('location_company'); ?></p>
                    </div>
						
							</div>
			</li>
 
			<?php  endwhile; ?>
            </ul>
			<nav class="navigation paging-navigation" role="navigation">
				<div class="nav-links">
					<?php if ( get_next_posts_link() ) : ?>
					<div class="nav-previous">
					<?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'battle' ) ); ?>
					</div>
					<?php endif; ?>
					<?php if ( get_previous_posts_link() ) : ?>
					<div class="nav-next">
					<?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'battle' ) ); ?>
					</div>
				<?php endif; ?>
				</div>
			<!-- .nav-links -->
			</nav>
			<?php 
			endif;
			wp_reset_query();
			?>
			</main>
		</div>
</div>
</section>

<?php get_footer(); ?>
