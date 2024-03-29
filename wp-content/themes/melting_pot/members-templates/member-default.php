<?php
/* Template Name: Member Default*/
if ( get_field('hide_header') == 'hidehead'):
get_header('blank');
else: 
get_header('members');
endif;
?>
</header>


<?php get_template_part('template-parts/members/member-page-header'); ?>

 
<section class="<?php the_field('stretch_page'); ?>">
<?php  if ( get_field('stretch_page') == 'stretchsection'): ?>
    
    
                <?php if(have_posts()): while (have_posts()): the_post();?>
                    <main role="main">
                    <article>
                    <?php the_content();?>
                    </article>
                    </main>
                <?php endwhile; endif;?>
 
<?php else: ?>
    

                <div class="row">
                    <?php if(have_posts()): while (have_posts()): the_post();?>
                        <div class="<?php  if ( get_field('hide_sidebar') == 'hideSidebar'): ?>large-12<?php else: ?>large-8<?php endif; ?> columns">
                        <main role="main">
                        <article>
                        <?php the_content();?>
                        </article>
                        </main>
                        </div>
                    <?php endwhile; endif;?>
                    
                <?php  if ( get_field('hide_sidebar') == 'keepSidebar'): ?>
                    <div class="large-4 columns">
                    <?php get_template_part('template-parts/members/member-sidebar'); ?>
                    </div>
                <?php endif; ?>
                </div>
    
<?php endif; ?>   
</section>
<!-- End Main Section -->
 
<?php 

if ( get_field('hide_footer') == 'hidefoot'):
get_footer('blank');
else: 
get_footer('members');
endif;
?>