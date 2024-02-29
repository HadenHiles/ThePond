<?php

/***  Template Name: Member Support */
if (get_field('hide_header') == 'hidehead') :
	get_header('blank');
else :
	get_header('members');
endif;
?>
</header>


<?php get_template_part('template-parts/members/member-page-header'); ?>

<section class="<?php echo get_field('stretch_page'); ?>">
	<?php if (get_field('stretch_page') == 'stretchsection') : ?>

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

				<div class="row">
					<div class="large-12 columns">
						<?php get_template_part('template-parts/members/member-support'); ?>
					</div>
				</div>

		<?php endwhile;
		endif; ?>

	<?php else : ?>


		<div class="row">
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
					<div class="<?php if (get_field('hide_sidebar') == 'hideSidebar') : ?>large-12<?php else : ?>large-8<?php endif; ?> columns">

						<?php get_template_part('template-parts/members/member-support'); ?>
					</div>
			<?php endwhile;
			endif; ?>

			<?php if (get_field('hide_sidebar') == 'keepSidebar') : ?>
				<div class="large-4 columns">
					<div class="sidebarcontent">
						<?php echo get_field('sidebar_content'); ?>
					</div>
					<?php get_template_part('template-parts/members/member-sidebar'); ?>
				</div>
			<?php endif; ?>
		</div>

	<?php endif; ?>
</section>
<!-- End Main Section -->
<?php
/* Template Name: Member Blank*/
if (get_field('hide_footer') == 'hidefoot') :
	get_footer('blank');
else :
	get_footer('members');
endif;
?>