<?php 
/**
 *  Template Name: Blank
 */
 get_header( 'blank' ); ?>

</header>

<!-- Main Section -->
<section>
<div class="row">
	<?php 
	if(have_posts()): while (have_posts()): the_post();
	?>
	<div class="large-12columns">
        <main role="main">
        <article>
        <?php the_content();?>	
        </article>
        </main>
    </div>
    <?php endwhile; endif;?>
</div>
</section>
<!-- End Main Section -->
 	
<?php  get_footer( 'blank' ); ?>