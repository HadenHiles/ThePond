<?php
/*
 * Template Name: Youzer Template
 * Description: Youzer Plugin Pages Template.
 */

get_header();

do_action( 'yz_before_youzer_template_content' );
if ( have_posts() ) :
	while ( have_posts() ) : the_post();
    the_content();
	endwhile;
endif;

do_action( 'yz_after_youzer_template_content' );

get_footer("members"); ?>
