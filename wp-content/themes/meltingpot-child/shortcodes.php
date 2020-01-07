<?php
add_shortcode('ld_course_tags', 'learndash_course_tags');
function learndash_course_tags($atts = [], $content = null, $tag = ''){
  $tmp_atts = array_change_key_case((array)$atts, CASE_LOWER);

  // override default attributes with user attributes
  $atts = shortcode_atts([
    'categories' => 'skating,stickhandling,shooting,passing',
  ], $tmp_atts, $tag);

  $categories = explode(',', $atts['categories']);

  $courses_by_category = array();
  foreach ($categories as $category) {
    $courses_by_category += [$category => array()];
  }

  $output = '';

  $args = array(
    'post_type' => array('sfwd-courses'),
		'meta_query' => array(
      'relation' => 'AND',
			array(
				'key' => 'available',
				'value' => true,
				'compare' => '=',
			),
			array(
				'key' =>'hide_from_feed',
				'value' => true,
				'compare' =>'!=',
		  )
		),
    'tax_query' => array(
      'relation' => 'OR',
      array(
        'taxonomy' => 'ld_course_category',
        'field' => 'slug',
        'terms' => $categories,
        'operator' => 'IN',
      )
    ),
  );
  $course_feed = new WP_Query( $args );

	if ($course_feed->have_posts()) {
		while ($course_feed->have_posts()) {
			$course_feed->the_post();
      $course = get_post(get_the_ID());
			$imgID  = get_post_thumbnail_id($post->ID);
			$img    = wp_get_attachment_image_src($imgID,'full', false, '');
			$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
			if (empty($img[0])) {
				$img[0] = DEFAULT_IMG;
			}

      $course->img = $img;
      $course->imgAlt = $imgAlt;
			$course->course_id = get_the_ID();
			$course->user_id =  get_current_user_id();
      $course->post_url = get_permalink($course);

			$course->course_status = learndash_course_status( $course->course_id, $course->user_id );
			$course->course_steps_count = learndash_get_course_steps_count( $course->course_id );
			$course->completed = learndash_course_get_completed_steps( $course->user_id, $course->course_id );

			$ld_course_steps_object = LDLMS_Factory_Post::course_steps( $course->course_id );
			$total = $ld_course_steps_object->get_steps_count();

			if ($total > 0) {
		     $percentage = intval( $course->completed * 100 / $total );
			   $percentage = ( $percentage > 100 ) ? 100 : $percentage;
			} else {
			     $percentage = 0;
			}
      $course->percentage = $percentage;

      $course_category_list = get_the_terms($post, 'ld_course_category');
      $course_category_slugs = array();
      foreach ($course_category_list as $course_category_item) {
        array_push($course_category_slugs, $course_category_item->slug);
      }

      foreach ($courses_by_category as $key => $value) {
        if (in_array($key, $course_category_slugs)) {
          array_push($courses_by_category[$key], $course);
        }
      }
		}
	}

  $output .=  '<div class="course-container-wrapper">'.
                '<div class="fadeout-left"></div>'.
                '<div class="fadeout-right"></div>'.
                '<a id="scroll-left-btn"><i class="fa fa-angle-left"></i></a>'.
                '<a id="scroll-right-btn"><i class="fa fa-angle-right"></i></a>'.
              '<div class="course-container">';
  foreach ($courses_by_category as $key => $value) {
    $courseCategoryTerm = get_term_by('slug', $key, 'ld_course_category');
    $courseCategoryTermId = $courseCategoryTerm->term_id;
    $categoryIcon = get_field('category_icon', 'ld_course_category_' . $courseCategoryTermId);
    $output .= '<div class="category-icon-wrapper"><img src="' . $categoryIcon . '" class="category-icon" /></div>';
    $output .= '<div class="course-wrapper">';
    foreach ($courses_by_category[$key] as $c) {
      $output .=  '<a class="course-item" href="' . $c->post_url . '" style="background-image: url(\''. $c->img[0] . '\')">'.
                    '<h5>' . $c->post_title . '</h5>';
      $output .=    '<div class="overlay"></div>';
      if ($c->percentage >= 100) {
        $output .=  '<h6 class="percentage">100%</h6>'.
                    '<span class="complete fa-stack fa-1x">'.
                      '<i class="fa fa-check fa-stack-1x"></i>'.
                      '<i class="fa fa-circle-thin fa-stack-1x icon-background"></i>'.
                    '</span>';
      } else {
        $output .=  '<div class="progress-bar" style="width: ' . $c->percentage . '%">' . $c->percentage . '%</div>';
      }
      $output .=    '<div class="progress-bar-small" style="width: ' . $c->percentage . '%">&nbsp;</div>';
      $output .=  '</a>';
    }
    $output .= '</div><div class="clear"></div>';
  }
  $output .= '</div></div>';

  echo $output;
}
?>
