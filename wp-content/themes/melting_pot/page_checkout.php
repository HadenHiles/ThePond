<?php
/* Template Name: Checkout*/
if( is_user_logged_in() )
	get_header('members');
else
	get_header();
?>
</header>



<section class="<?php the_field('stretch_page'); ?>">
<?php  if ( get_field('stretch_page') == 'stretchsection'): ?>
    
    <div class="row">
		<div class="large-10 large-push-1 columns">
                <?php if(have_posts()): while (have_posts()): the_post();?>
                    <main role="main">
                    <article>
                    <?php the_content();?>
						
						<div class="trustIcons"></div>
                    </article>
                    </main>
                <?php endwhile; endif;?>
	</div>
	</div>
 
<?php else: ?>
    

                <div class="row">
                    <?php if(have_posts()): while (have_posts()): the_post();?>
                        <div class="<?php  if ( get_field('hide_sidebar') == 'hideSidebar'): ?>large-12<?php else: ?>large-9<?php endif; ?> columns">
                        <main role="main">
                        <article>
                        <?php the_content();?>
							
							<div class="trustIcons"></div>
                        </article>
                        </main>
                        </div>
                    <?php endwhile; endif;?>
                    
                <?php  if ( get_field('hide_sidebar') == 'keepSidebar'): ?>
                    <div class="large-3 columns">
                    <?php get_template_part('template-parts/members/member-sidebar'); ?>
                    </div>
                <?php endif; ?>
                </div>
    
<?php endif; ?>   
</section>
<!-- End Main Section -->
<?php
/* Template Name: Member Blank*/
if ( get_field('hide_footer') == 'hidefoot'):
get_footer('blank');
else: 
get_footer('members');
endif;
?>