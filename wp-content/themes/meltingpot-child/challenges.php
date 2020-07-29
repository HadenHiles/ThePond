<?php
/**
* Template Name: Challenges
*
* @package WordPress
* @subpackage Meltingpot-child
* @since Twenty Twenty
*/
global $smof_data;
get_header('members'); ?>
</header>

<section class="memberbenefits dashboardbenefits" style="min-height: 80vh;">
<?=do_shortcode('[list_challenges title="' . get_the_title() . '" content="' . htmlspecialchars(get_the_content()) . '" limit="-1"]');?>
</section>

<!-- End Main Section -->
<?php  get_footer("members"); ?>
