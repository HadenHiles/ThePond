<?php
//avatar
add_shortcode('user_avatar', 'display_user_avatar');
function display_user_avatar($atts = [], $content = null, $tag = '') {
  $tmp_atts = array_change_key_case((array)$atts, CASE_LOWER);

  // override default attributes with user attributes
  $atts = shortcode_atts([
    'size' => '50',
    'url' => '/account'
  ], $tmp_atts, $tag);

  $user_id = get_current_user_id();
  ?>
  <a href="<?=$atts['url']?>" class="avatar" style="width: <?=$atts['size'];?>px; height: <?=$atts['size'];?>px;">
    <?=get_wp_user_avatar($user_id, $atts['size'])?>
  </a>
  <?php
}

//courses for dashboard
add_shortcode('ld_courses_by_categories', 'learndash_courses_by_categories');
function learndash_courses_by_categories($atts = [], $content = null, $tag = ''){
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
    'orderby' => 'order',
    'order' => 'ASC',
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

  ?>
  <div class="course-container-wrapper">
    <div class="fadeout-left"></div>
    <div class="fadeout-right"></div>
    <a id="scroll-left-btn"><i class="fa fa-angle-left"></i></a>
    <a id="scroll-right-btn"><i class="fa fa-angle-right"></i></a>
    <div class="course-container">
      <?php
      foreach ($courses_by_category as $key => $value) {
        $courseCategoryTerm = get_term_by('slug', $key, 'ld_course_category');
        $courseCategoryTermId = $courseCategoryTerm->term_id;
        $categoryIcon = get_field('category_icon', 'ld_course_category_' . $courseCategoryTermId);
        ?>
        <div class="category-icon-wrapper">
          <h6 class="title"><?=$key?></h6>
          <img src="<?=$categoryIcon?>" class="category-icon" />
        </div>
        <div class="course-wrapper">
          <?php
          foreach ($courses_by_category[$key] as $c) {
            ?>
            <a class="course-item" href="<?=$c->post_url?>" style="background-image: url('<?=$c->img[0]?>')">
              <div class="overlay"></div>
              <div class="title">
                <?php
                if ($c->percentage >= 100) {
                  ?>
                  <h6 class="percentage">100%</h6>
                  <span class="complete fa-stack fa-1x">
                    <i class="fa fa-check fa-stack-1x"></i>
                    <i class="fa fa-circle fa-stack-1x icon-background"></i>
                  </span>
                  <div class="progress-bar" style="width: <?=$c->percentage?>%"><?=$c->percentage?>%</div>
                  <?php
                } else if (empty($c->percentage) || $c->percentage == 0) {
                  ?>
                  <span class="incomplete fa-stack fa-1x">
                    <i class="fa fa-check fa-stack-1x"></i>
                    <i class="fa fa-circle fa-stack-1x icon-background"></i>
                  </span>
                  <?php
                } else {
                  ?>
                  <h6 class="percentage"><?=$c->percentage?></h6>
                  <span class="incomplete fa-stack fa-1x">
                    <i class="fa fa-check fa-stack-1x"></i>
                    <i class="fa fa-circle fa-stack-1x icon-background"></i>
                  </span>
                  <div class="progress-bar" style="width: <?=$c->percentage?>%"><?=$c->percentage?>%</div>
                  <?php
                }
                ?>
                <div class="progress-bar-small" style="width: <?=$c->percentage?>%">&nbsp;</div>
                <h5><?=$c->post_title?></h5>
              </div>
            </a>
            <?php
          }
          ?>
        </div>
        <div class="clear"></div>
        <?php
      }
      ?>
    </div>
  </div>
  <?php
}

//challenges
add_shortcode('list_challenges', 'display_challenge_resources');
function display_challenge_resources($atts = [], $content = null, $tag = '') {
  $tmp_atts = array_change_key_case((array)$atts, CASE_LOWER);

  // override default attributes with user attributes
  $atts = shortcode_atts([
    'title' => 'Challenges',
    'content' => '',
    'btn' => 'All Challenges',
    'btn-url' => null,
    'limit' => 3
  ], $tmp_atts, $tag);

  $title = $atts['title'];
  $limit = intval($atts['limit']);
  $content = $atts['content'];
  $btn = $atts['btn'];
  $btnUrl = $atts['btn-url'];

  ?>
    <div class="challenges-wrapper">
      <div class="row" style="padding-top: 75px;">
          <div class="large-12 columns">
              <h1><?=$title?></h1>
              <?php
              if (!empty($btnUrl)) {
                ?>
                <a href="<?=$btnUrl?>" class="BTN all-challenges"><?=$btn?></a>
                <?php
              }
              ?>
              <?=$content?>
          </div>
      </div>
      <div class="clearfix"></div>
      <div class="bootstrap-styles challenges">
          <?php
          $challengesQuery = new WP_Query( array(
              'posts_per_page' => $limit,
              'post_status'    => 'publish',
              'post_type' => 'content-library',
              'order' => 'desc',
              'orderby' => 'post_date',
              'suppress_filters' => true,
              'tax_query' => array(
                  array(
                      'taxonomy' => 'library_category',
                      'field' => 'slug',
                      'terms' => 'challenges', //pass your term name here
                      'include_children' => true
                  )
              )
          ));
          $challenges = $challengesQuery->get_posts();
          
          while($challengesQuery->have_posts()) {
              $challengesQuery->the_post();
              $thumbnail = get_the_post_thumbnail_url(get_the_id(), 'full');
              if(empty($thumbnail)) {
                  $thumbnail = '/wp-content/themes/meltingpot-child/images/placeholder.png';
              }
              ?>
              <a href="<?=the_permalink()?>" class="card shadow challenge">
                  <div class="card-img-top" style="background-image: url('<?=$thumbnail?>');"></div>
                  <div class="card-body">
                      <h4><?=get_the_title()?></h4>
                      <p class="card-text"><?=get_field('description_short')?></p>
                  </div>
              </a>
              <?php
          }
          ?>
      </div>
    </div>
  <?php
}
?>
