<?
/**
 * This file contains the code that displays the course list.
 *
 * @since 2.1.0
 *
 * @package LearnDash\Course
 */
?>


<div class="each-course columns large-4 medium-4">
<a href="<? the_permalink(); ?>">
  <div style="background-image:url('<? the_post_thumbnail_url(); ?>')" class="ld-entry-content entry-content">

    <h2 class="ld-entry-title entry-title"> <? the_title(); ?></h2>
  <?
  	if ( ( isset( $shortcode_atts['show_content'] ) ) && ( $shortcode_atts['show_content'] == 'true' ) ) {
  		global $more; $more = 0;
  	}
  ?>
  </div>
</a>
</div>
