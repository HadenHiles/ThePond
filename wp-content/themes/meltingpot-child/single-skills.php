<?php
global $smof_data;
get_header("members");
if(has_post_thumbnail()) {
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, ''); 
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
}
?>
<section class="memberDashWelc skill-header">
	<div class="row">
		<div class="large-12 columns">
            <div class="large-8 medium-6 small-12 columns title-content-left">
                <h3>
                    <?php 
                    $parentTitle = get_the_title( $post->post_parent );
                    if (!empty($parentTitle) && $parentTitle != get_the_title($post->ID)) {
                        ?>
                        <span style="font-size: 0.7em; position: relative; top: -2px;"><a href="<?=get_post_permalink($post->parent_post)?>"><?=$parentTitle?></a> > </span>
                        <?php
                    }
                    echo the_title(); 
                    ?>
                </h1>
                <?php
                $skillTypes = get_the_terms( $post->ID, 'skill-type' ); 
                if(sizeof($skillTypes) > 0) {
                    ?>
                    <p>
                    <?php
                    $count = 0;
                    foreach($skillTypes as $skillType) {
                        if (++$count > 1 && $count <= sizeof($skillTypes)) {
                            echo ', ';
                        }
                        echo $skillType->name;
                    }
                    ?>
                    </p>
                    <?php
                }
                ?>
                <div class="clearfix"></div>
                <?php
                if(current_user_can('memberpress_authorized')) {
                    ?>
                    <a href="/member-dashboard" class="BTN"><i class="fa fa-caret-left"></i> Dashboard</a>
                    <?php
                }
                ?>
            </div>
            <div class="large-4 medium-6 small-12 columns puck-rating title-content-right">
                <h3>Frequency</h3>
                <?php
                $puckLevel = intval(get_field('puck_level'));
                $puckRemainder = 5 - $puckLevel;
                for ($x = 0; $x < $puckRemainder; $x++) {
                    ?>
                    <svg class="puck faded" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1em" height="1em" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 512 512"><path d="M0 160c0-53 114.6-96 256-96s256 43 256 96s-114.6 96-256 96S0 213 0 160zm0 82.2V352c0 53 114.6 96 256 96s256-43 256-96V242.2c-113.4 82.3-398.5 82.4-512 0z" /></svg>
                    <?php
                }
                for ($x = 0; $x < $puckLevel; $x++) {
                    ?>
                    <svg class="puck" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1em" height="1em" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 512 512"><path d="M0 160c0-53 114.6-96 256-96s256 43 256 96s-114.6 96-256 96S0 213 0 160zm0 82.2V352c0 53 114.6 96 256 96s256-43 256-96V242.2c-113.4 82.3-398.5 82.4-512 0z" /></svg>
                    <?php
                }
                ?>
                <div class="clearfix"></div>
                <?php
                $performanceLevels = get_the_terms( $post->ID, 'performance-level' ); 
                if(sizeof($performanceLevels) > 0) {
                    ?>
                    <p style="float: right; text-align: right;">
                    <?php
                    $count = 0;
                    foreach($performanceLevels as $performanceLevel) {
                        if (++$count > 1 && $count <= sizeof($performanceLevels)) {
                            echo ', ';
                        }
                        echo $performanceLevel->name;
                    }
                    ?>
                    </p>
                    <?php
                }
                ?>
            </div>
		</div>
	</div>
</section>
</header>
<!-- Main Section -->
<section class="MainContent skill-content">
<div class="row">
	<?php 
	if(have_posts()): while (have_posts()): the_post();
	?>
	<div class="large-8 medium-6 columns" style="margin: 15px auto;">
        <main role="main">
            <article>
                <?php
                //Unauthorized
                if(!current_user_can('memberpress_authorized')) {
                    ?>
                    <div class="bootstrap-styles main">
                        <?php
                        // Check rows exists.
                        $whenToUseIt = get_field('when_to_use_it', $post->ID);
                        if( !empty($whenToUseIt) ):
                            ?>
                            <div class="card">
                                <div class="card-body">
                                    <!-- When to use it -->
                                    <div class="ListWithHeading">
                                        <h1>When To Use It</h1>
                                        <?=$whenToUseIt?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        else :
                            // Do something...
                        endif;

                        // Check rows exists.
                        if( have_rows('common_mistakes') ):
                            $count = count(get_field('common_mistakes'));
                            ?>
                            <div class="card">
                                <div class="card-body">
                                    <!-- Common mistakes -->
                                    <div class="ListWithHeading">
                                        <h2><?=($count > 1) ? $count : ''?> Common Mistakes</h2>
                                        <?php
                                        // Loop through rows.
                                        skillBreakdownList('common_mistakes');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        // No value.
                        else :
                            // Do something...
                        endif;
                        ?>
                        <div class="card">
                            <a href="/">
                                <img src="https://cdn.thepond.howtohockey.com/2020/08/block-ad-final-scaled-1.jpg" alt="" class="card-img-top" />
                            </a>
                        </div>
                    </div>
                    <?php
                } else {
                    //Authorized
                    ?>
                    <div class="bootstrap-styles main">
                        <?php
                        if (!empty(get_field('video_code')) || !empty(get_the_content())) {
                            ?>
                            <div class="card">
                                <div class="card-img-top">
                                    <div class="videoWrapper">
                                        <?=get_field('video_code');?>	
                                    </div>
                                </div>
                                <?php
                                if (!empty(get_the_content())) {
                                    ?>
                                    <div class="card-body">
                                        <?php the_content();?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }

                        // Check rows exists.
                        if( have_rows('skill_quick_tips') ):
                            $count = count(get_field('skill_quick_tips'));
                            ?>
                            <div class="card">
                                <div class="card-body">
                                    <!-- Quick Tips -->
                                    <div class="ListWithHeading">
                                        <h2><?=($count > 1) ? $count : ''?> Quick Tips</h2>
                                        <?php
                                        // Loop through rows.
                                        skillBreakdownList('skill_quick_tips');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        // No value.
                        else :
                            // Do something...
                        endif;

                        // Check rows exists.
                        if( have_rows('common_mistakes') ):
                            $count = count(get_field('common_mistakes'));
                            ?>
                            <div class="card">
                                <div class="card-body">
                                    <!-- Common mistakes -->
                                    <div class="ListWithHeading">
                                        <h2><?=($count > 1) ? $count : ''?> Common Mistakes</h2>
                                        <?php
                                        // Loop through rows.
                                        skillBreakdownList('common_mistakes');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        // No value.
                        else :
                            // Do something...
                        endif;
                        ?>
                    </div>
                    <?php
                }
                ?>
            </article>
        </main>
    </div>
    <div class="large-4 medium-6 columns" style="margin: 15px auto;">
        <div class="bootstrap-styles side">
            <?php
            //Authorized
            if(current_user_can('memberpress_authorized')) {
                // Check rows exists.
                if( have_rows('skill_pros') ):
                    ?>
                    <div class="card">
                        <div class="card-body">
                            <!-- Pros -->
                            <div class="ListWithHeading">
                                <h1>Pros</h1>
                                <?php
                                // Loop through rows.
                                skillBreakdownList('skill_pros');
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php
                // No value.
                else :
                    // Do something...
                endif;

                // Check rows exists.
                if( have_rows('skill_cons') ):
                    ?>
                    <div class="card">
                        <div class="card-body">
                            <!-- Cons -->
                            <div class="ListWithHeading">
                                <h1>Cons</h1>
                                <?php
                                // Loop through rows.
                                skillBreakdownList('skill_cons');
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php
                // No value.
                else :
                    // Do something...
                endif;
                
                // Check rows exists.
                $whenToUseIt = get_field('when_to_use_it', $post->ID);
                if( !empty($whenToUseIt) ):
                    ?>
                    <div class="card">
                        <div class="card-body">
                            <!-- When to use it -->
                            <div class="ListWithHeading">
                                <h1>When To Use It</h1>
                                <?=$whenToUseIt?>
                            </div>
                        </div>
                    </div>
                    <?php
                else :
                    // Do something...
                endif;
            }
            ?>

            <?php
            // Related Lessons
            $relatedLessons = get_field('skills', $post->ID);
            $relatedLessons = get_posts(array(
                'post_type' => 'sfwd-lessons',
                'meta_query' => array(
                    array(
                        'key' => 'skills', // name of custom field
                        'value' => '"' . $post->ID . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
                        'compare' => 'LIKE'
                    )
                )
            ));
            if (!empty($relatedLessons)) {
                ?>
                <h2 style="margin-bottom: 5px;">Related Lessons</h2>
                <?php
            }
            ?>
            <div class="skills-list">
                <?php
                if (!empty($relatedLessons)) {
                    foreach($relatedLessons as $relatedLesson) {
                        $performanceLevels = get_the_terms( $relatedLesson->ID, 'performance-level' ); 
                        $performanceLevelString = '';
                        if(sizeof($performanceLevels) > 0) {
                            $count = 0;
                            foreach($performanceLevels as $performanceLevel) {
                                if (++$count > 1 && $count <= sizeof($performanceLevels)) {
                                    $performanceLevelString .= ', ';
                                }
                                $performanceLevelString .= $performanceLevel->name;
                            }
                        }
                        ?>
                        <div class="card skill">
                            <div class="card-body content">
                                <a href="<?=get_post_permalink($relatedLesson->ID)?>" class="ghost"></a>
                                <a href="<?=get_post_permalink($relatedLesson->ID)?>" class="title"><?=get_the_title($relatedLesson->ID)?></a>
                                <div class="right">
                                    <span class="level"><?=$performanceLevelString?></span>
                                    <?php
                                    $course_id = learndash_get_course_id($relatedLesson->ID);
                                    $courseTitle = get_the_title($course_id);
                                    $courseUrl = get_permalink($course_id);
                                    ?>
                                    <span class="course"><a href="<?=$courseUrl?>"><?=$courseTitle?></a></span>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <?php endwhile; endif;?>
</div>
</section>
<!-- End Main Section -->

<!-- Full Width Section -->
<?php  if ( get_field('show_full_width_section') == 'yes'): ?>		
<section class="fullwidth">
<div class="row">
<div class="large-12 columns">
<?php the_field('full_width_section'); ?>   
</div>
</div>
</section>
<?php endif; ?>
<!-- End Full Width Section -->


<!-- Testimonial Section -->
<?php 
get_footer("members");

function skillBreakdownList($listName) {
	?>
	<ol class="lesson-list">
		<?php
		while( have_rows($listName) ) : the_row();
			?>
			<li class="lesson-list-item">
				<?php
				// Load sub field values.
				$title = get_sub_field('title');
				$content = get_sub_field('content');
				if (!empty($title)) {
					?>
					<div class="title"><?=$title?></div>
					<?php
				}
				if (!empty($content)) {
					?>
					<div class="content"><?=$content?></div>
					<?php
				}
				?>
			</li>
			<?php
		// End loop.
		endwhile;
		?>
    </ol>
	<?php
}

?>