<?php global $smof_data; ?>
<!doctype html>
<html class="no-js" lang="en">
<head>

<?php if( $smof_data['googletagmanager']) { echo  $smof_data['googletagmanager']; } ?>
	
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<?php wp_head();?>

<?php if( $smof_data['head_script']) { echo  $smof_data['head_script']; } ?>    
<script>
  window.FontAwesomeConfig = {
    searchPseudoElements: true
  }
</script>
</head>

<body class="vidFull fitvidcontiner">

<div id="sitecontainer" <?php body_class(); ?>  >
	
	<div class="MAXHEIGHT">
<?php if( $smof_data['googletagmanagernoscript']) { echo  $smof_data['googletagmanagernoscript']; } ?>
	
<header class="header">
	<div class="topStrip">
		<div class="row">
			<p class="strapline"><?php echo  $smof_data['strapline']; ?></p>
		</div>
	</div>
	<div class="headerWrap">
		<div class="row">
			<div class="large-12 columns">
				<div class="headerlogo">
					<?php if( $smof_data['header_logo']) : ?>	
					<a href="/"><img class="logo" src="<?php echo $smof_data['header_logo']; ?>" alt="<?php bloginfo( 'name' ); ?> Logo" /></a>
					<?php endif;?>
				</div>
				<div class="NavOuter">
					<div class="NavBTN">
						<div class="bar1"></div>
						<div class="bar2"></div>
						<div class="bar3"></div>
					</div>
					<nav class="MainNav">
						<?php
					 if ( has_nav_menu( 'primary-menu' ) ) {	
							wp_nav_menu ( array (
							'theme_location' => 'primary-menu',
							'menu_class' => 'LoggedOut',
							'container' => false 
							) );
					}		
						?>
					</nav>
				</div>
			</div>
		</div> 
	</div>