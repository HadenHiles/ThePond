<?php
function msa_lessonhistory($atts,$content=null) {
  extract(shortcode_atts(array(
    "type" => 'history',
    'status' => '1'
  ), $atts));

  $status = absint($status);
  $course_id = 0;
  $returndata='';
  global $wpdb;
  if ($type=='history') :
    $history=$wpdb->get_results("select * from " . $wpdb->prefix . "lessonlog where `user_id`=" . get_current_user_id() . " order by `viewed` desc limit 20",ARRAY_A);

  elseif ($type=='tracking') :

    $history=$wpdb->get_results("select * from " . $wpdb->prefix . "lessontracker where `user_id`=" . get_current_user_id() . " and `lesson_status`=" . $status . " order by `viewed` desc",ARRAY_A);

  endif;



  $returndata='<ul>';

  foreach($history as $line) {

    if(date("m-d-y") == date("m-d-y", strtotime($line['viewed']))) {
        $time = "Today";
    }
    else if(date("m-d-y", strtotime("-1 day")) == date("m-d-y", strtotime($line['viewed']))) {
        $time = "Yesterday";
    }
    else {
        $time = date("m-d-y", strtotime($line['viewed']));
    }

      if (get_field('course_page_type',$line['lesson_id'])!='standalone') :
        $course_id=wp_get_post_parent_id($line['lesson_id']);
	  endif;

	  if( $course_id == 0 )
        $course_id=$line['lesson_id'];


      if (get_field('course_page_type',$course_id) == 'module') :
        $course_id=wp_get_post_parent_id($course_id);
      endif;

	$post = get_post( $course_id );
	 if ( ! is_object( $post ) ) {
       continue;
    }

      if (has_post_thumbnail($course_id)) :
        $thumb_id = get_post_thumbnail_id($course_id);
        $thumb_url_array = wp_get_attachment_image_src($thumb_id, 'full');
        $thumb_url = $thumb_url_array[0];
        $course_thumb =' style="background: url(' . $thumb_url . ')no-repeat;background-size:cover;background-position:center;"';
      else:
        $course_thumb=' style="background: url('.DEFAULT_IMG. ')no-repeat;background-size:cover;background-position:center;"';
      endif;
    ob_start();
?>
<li class="course-listing training_listing listing_history">

        <a href="<?php echo get_the_permalink($line['lesson_id']); ?>" class="savedvideo course-content" id="<?php echo $course_id; ?>" >

			<div class="coursePrevImage" <?php echo ($course_thumb); ?>></div>

        </a>

	<h4><a href="<?php echo get_the_permalink($line['lesson_id']); ?>"><?php echo get_the_title($line['lesson_id']); ?></a></h4>

			<a href="<?php echo get_the_permalink($line['lesson_id']); ?>" class="BTN">Access</a>

        </li>

<?php

    $returndata .= ob_get_contents();
    ob_end_clean();

    //wp_reset_postdata();
  }
  $returndata.="</ul>";

  return $returndata;
}
add_shortcode("lessonhistory", "msa_lessonhistory");
