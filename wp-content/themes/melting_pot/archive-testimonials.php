<?php get_header(); ?>
<section class="Banner TestimonialHeader">
    <div class="row">
        <div class="large-12 medium-12 columns">
            <h1>What our clients say</h1>
            <h2><em>Take a look at what some of our clients have to say about us</em></h2>
        </div>
    </div>
</section>
</header>

<!-- section-->
<section class="MainContent testimonialpage">
    <div class="row">
        <div class="large-8 columns">

            <main>
                <ul>
                    <?php
                    if (have_posts()) : while (have_posts()) : the_post();
                            $img = wp_get_attachment_url(get_post_thumbnail_id($post->ID), "Full");
                    ?>
                            <li class="testimonial">


                                <h4>"<?php the_title(); ?>test"</h4>
                                <?php the_content(); ?>
                                <div class="testimage" style="background: url('<?php echo $img; ?>')no-repeat;background-size:cover;background-position:center;"></div>
                                <div class="testcontent">
                                    <p class="starrating"><?php echo get_field('star_rating'); ?></p>
                                    <p class="author"><strong><?php echo get_field('testimonial_author'); ?></strong></p>
                                    <p class="locationcompany"><?php echo get_field('location_company'); ?></p>
                                </div>

                            </li>
                        <?php endwhile; ?>
                </ul>
                <nav class="navigation paging-navigation" role="navigation">
                    <div class="nav-links">
                        <?php if (get_next_posts_link()) : ?>
                            <div class="nav-previous">
                                <?php next_posts_link(__('<span class="meta-nav">&larr;</span> Older posts', 'battle')); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (get_previous_posts_link()) : ?>
                            <div class="nav-next">
                                <?php previous_posts_link(__('Newer posts <span class="meta-nav">&rarr;</span>', 'battle')); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- .nav-links -->
                </nav>
            <?php endif;
                    wp_reset_query(); ?>
            </main>
        </div>
        <div class="large-4 columns">
        </div>
    </div>
</section>

<?php get_footer(); ?>