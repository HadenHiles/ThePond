<?php global $smof_data; ?>

<footer class="footer">
<div class="row">
<div class="ctabox formbox">
<h3>Stay up to date!</h3>
<?php echo do_shortcode('[gravityform id="2" title="false" description="false"]'); ?>    
<p>By completing the above form you are agreeing to our privacy policy.</p>
</div>
			<?php if($smof_data['footer_logo']) { ?>
			<img src="<?php echo $smof_data['footer_logo']; ?>" class="logo" alt="Company Logo" />
			<?php } ?>



				<div class="SocialFooterSide">
					<?php if($smof_data['facebook_link']) : ?>
					<a href="<?php echo $smof_data['facebook_link'];?>" target="_blank" rel="nofollow"><i class="fab fa-facebook-f"></i></a>
					<?php endif;?>
					<?php if($smof_data['twitter_link']) : ?>
					<a href="<?php echo $smof_data['twitter_link'];?>" target="_blank" rel="nofollow"><i class="fab fa-twitter"></i></a>
					<?php endif;?>
					<?php if($smof_data['youtube_link']) : ?>
					<a href="<?php echo $smof_data['youtube_link']; ?>" target="_blank" rel="nofollow"><i class="fab fa-youtube"></i></a>
					<?php endif; ?>
					<?php if($smof_data['google_link']) : ?>
					<a href="<?php echo $smof_data['google_link']; ?>" target="_blank" rel="nofollow"><i class="fab fa-google-plus-g"></i></a>
					<?php endif;?>
					<?php if($smof_data['linkedin_link']) : ?>
					<a href="<?php echo $smof_data['linkedin_link']; ?>" target="_blank" rel="nofollow"><i class="fab fa-linkedin-in"></i></a>
					<?php endif;?>
					<?php if($smof_data['insta_link']) : ?>
					<a href="<?php echo $smof_data['insta_link']; ?>" target="_blank" rel="nofollow"><i class="fab fa-instagram"></i></a>
					<?php endif;?>
				</div>

</div>
</footer>


<div class="remodal bigremodal" data-remodal-id="testpopup" role="dialog" aria-labelledby="modal1Title" aria-describedby="modal1Desc">
	<button data-remodal-action="close" class="remodal-close" aria-label="Close"></button>
		<div>
			This is a test remodal popup   
		</div>
</div>


<?php wp_footer();?>
<a href="#0" class="cd-top">Top</a>

<?php if( $smof_data['extrafooterscripts']) { echo  $smof_data['extrafooterscripts']; } ?>

</body>
</html>
