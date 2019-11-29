<?php global $smof_data; ?>

<footer class="footer">
<div class="row">	
<div class="large-3 columns">
	
<?php if($smof_data['footer_logo']) { ?>
	<a href="/">
<img src="<?php echo $smof_data['footer_logo']; ?>" class="footerlogo" alt="<?php bloginfo( 'name' ); ?> Logo" /></a>
<?php } ?>
	
<p>&copy; <?php echo date("Y"); ?> <a href="/"><?php bloginfo( 'name' ); ?></p>
		
</div>
	
<div class="large-5 columns">
<div class="footer-menu"> 
<?php
 if ( has_nav_menu( 'secondary-menu' ) ) {	
 wp_nav_menu ( array (
'theme_location' => 'secondary-menu',
'container' => false ) ); 
}
?>
</div>
</div>
	
<div class="large-4 columns">
	
<div class="footerRight">
		
<a href="/dashboard" class="BTN">SIGN UP</a>
		
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
	
<p>Website by <a target="_blank" rel="nofollow" href="https://membershipwebsiteslab.com">Membership Websites Lab</a></p>

</div>
	
</div>	
</div>
</footer>


<?php wp_footer();?>
<a href="#0" class="cd-top">Top</a>

<?php if( $smof_data['extrafooterscripts']) { echo  $smof_data['extrafooterscripts']; } ?>
</div>
</body>
</html>
