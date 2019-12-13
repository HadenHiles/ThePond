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

<?php if( $smof_data['googletagmanagernoscript']) { echo  $smof_data['googletagmanagernoscript']; } ?>

<header class="header">
<div class="MemberheaderWrap">
  <div class="row">
    <div class="large-12 columns">
      <div class="headerlogo">
        <?php if($smof_data['member_dashboard_logo']) {
			if( $smof_data['member_homepage_url'] )
				$home_page_url = $smof_data['member_homepage_url'];
			else
				$home_page_url = "/members-dashboard/";

		 ?>
        <a href="<?php echo $home_page_url; ?>"><img class="memberlogo" src="<?php echo $smof_data['member_dashboard_logo']; ?>" alt="<?php bloginfo( 'name' ); ?> Logo" /></a>
        <?php } else if( $smof_data['header_logo']) : ?>
        <a href="/"><img class="logo" src="<?php echo $smof_data['header_logo']; ?>" alt="<?php bloginfo( 'name' ); ?> Logo" /></a>
        <?php endif;?>
      </div>
      <?php if (is_user_logged_in()) : ?>
		<div class="topmemberNav">
	   <?php
	   if ( has_nav_menu( 'member-account-menu' ) ) {
				wp_nav_menu ( array (
					'theme_location' => 'member-account-menu',
					'menu_class' => 'LoggedIn',
					'container' => false
					)
				);

		}
			?>


      </div>
      <?php else : ?>
      <?php endif;?>
      <div class="NavOuter">
        <div class="NavBTN">
          <div class="bar1"></div>
          <div class="bar2"></div>
          <div class="bar3"></div>
        </div>
        <nav class="MainNav">
          <?php
			if( is_user_logged_in() ) {
				$theme_location = 'member-menu';
			} else {
				$theme_location = 'primary-menu';
			}
			?>
			  <?php
		 if ( has_nav_menu( $theme_location ) ) {

				wp_nav_menu ( array (
					'theme_location' => $theme_location,
					'menu_class' => 'LoggedIn',
					'container' => false
					)
				);

		}
			?>
        </nav>
      </div>
    </div>
  </div>
</div>
<div id="search" class="">
<button type="button" class="close">×</button>
<form role="search" method="get" action="<?php echo home_url();?>">
<input type="search" class="search-field" placeholder="click to enter your keywords..." value="" name="s" title="Search the Academy:" autocomplete="off">
<input type="submit" class="btn btn-primary" value="Search">
</form>
</div>
