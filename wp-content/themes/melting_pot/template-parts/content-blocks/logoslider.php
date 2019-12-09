<?php 
/*To Edit go to the homepage*/
?>
<section class="logo-slider">
<div class="large-1 columns nopad">
<div class="slidertitle">
<h3>Your <br />Clients</h3>
</div>
</div>
    
	<div class="large-11 columns nopad">   
		<div class="logoslide">
			<div class="regular logoslider">
				<?php while( have_rows('logo_slider', 13) ): the_row(); 
				// vars
				$logo = get_sub_field('logo');
				?>
					<div>
						<img src="<?php echo $logo['url']; ?>" alt="<?php echo $logo['alt']; ?>">
					</div>
				<?php endwhile; ?>
			</div>  
		</div>
	</div>
</section>