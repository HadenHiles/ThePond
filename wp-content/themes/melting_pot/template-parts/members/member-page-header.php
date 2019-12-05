<?php ?>
			
<?php if( get_field('hide_title') == 'keeptitle' ) : ?>
<section class="memberDashWelc">
	<div class="row">
		<div class="large-12 columns">
			<h1><?php the_title(); ?></h1>
		</div>
	</div>
</section>
<?php endif; ?>