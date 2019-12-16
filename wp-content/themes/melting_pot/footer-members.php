<?php global $smof_data; ?>

</div>


<footer class="footer members">



<div class="row">
 <div class="large-2 columns">

<?php if($smof_data['footer_logo']) { ?>
	<a href="/">
<img src="<?php echo $smof_data['footer_logo']; ?>" class="footerlogo" alt="<?php bloginfo( 'name' ); ?> Logo" /></a>
<?php } ?>
</div>


<div class="large-10 columns">
<div class="members-footer-menu">
 <?php if (is_user_logged_in()): ?>
<?php
if ( has_nav_menu( 'member-footer-menu' ) ) {
	wp_nav_menu ( array (
	'theme_location' => 'member-footer-menu',
	'container' => false ) );
	}
?>
<?php else: ?>
<?php
 if ( has_nav_menu( 'secondary-menu' ) ) {
 wp_nav_menu ( array (
'theme_location' => 'secondary-menu',
'container' => false ) );
}
?>
<?php endif; ?>
</div>
<div class="copyright centered">
<p>&copy; <?php echo date("Y"); ?> <?php bloginfo( 'name' ); ?> | <a href="/privacy-policy">Privacy Policy</a></p>
</div>
</div>


</div>
</footer>

<?php if (is_user_logged_in()): ?>
<?php get_template_part('template-parts/members/members-next-call'); ?>
<?php endif; ?>

<?php wp_footer();?>
<a href="#0" class="cd-top">Top</a>
<?php if( $smof_data['extrafooterscripts']) { echo  $smof_data['extrafooterscripts']; } ?>

</body>


</html>
