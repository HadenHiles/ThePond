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
                <a href="/member-dashboard" class="BTN"><i class="fa fa-caret-left"></i> Dashboard</a>
            </div>
            <div class="large-4 medium-6 small-12 columns puck-rating title-content-right">
                <h3>Frequency</h3>
                <?php
                $puckLevel = intval(get_field('puck_level'));
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
<section class="MainContent">
<div class="row">
	<?php 
	if(have_posts()): while (have_posts()): the_post();
	?>
	<div class="<?php  if ( get_field('show_sidebar') == 'yes'): ?>large-8<?php else: ?>large-12<?php endif; ?> columns">
        <main role="main">
        <article>
        <?php
        echo get_field('video_code');
        ?>
        <br />
        <?php the_content();?>	
        </article>
        <article>
            <!-- Tips for success list -->
            <div class="KeyLessonList">
                <?php
                // Check rows exists.
                if( have_rows('tips_for_success') ):
                    $count = count(get_field('tips_for_success'));
                    ?>
                    <h3><?=($count > 1) ? $count : ''?> Tips For Success</h3>
                    <?php
                    // Loop through rows.
                    keyLessonList('tips_for_success');
                // No value.
                else :
                    // Do something...
                endif;
                ?>
            </div>

            <!-- Common mistakes list -->
            <div class="KeyLessonList">
                <?php
                // Check rows exists.
                if( have_rows('common_mistakes') ):
                    $count = count(get_field('common_mistakes'));
                    ?>
                    <h3><?=($count > 1) ? $count : ''?> Common Mistakes</h3>
                    <?php
                    // Loop through rows.
                    keyLessonList('common_mistakes');
                // No value.
                else :
                    // Do something...
                endif;
                ?>
            </div>

            <!-- Quick Tips list -->
            <div class="KeyLessonList">
                <?php
                // Check rows exists.
                if( have_rows('quick_tips') ):
                    $count = count(get_field('quick_tips'));
                    ?>
                    <h3><?=($count > 1) ? $count : ''?> Quick Tips</h3>
                    <?php
                    // Loop through rows.
                    keyLessonList('quick_tips');
                // No value.
                else :
                    // Do something...
                endif;
                ?>
            </div>

            <!-- What To Practice list -->
            <div class="KeyLessonList">
                <?php
                // Check rows exists.
                if( have_rows('what_to_practice') ):
                    ?>
                    <h3>What To Practice</h3>
                    <?php
                    // Loop through rows.
                    keyLessonList('what_to_practice');
                // No value.
                else :
                    // Do something...
                endif;
                ?>
            </div>

            <!-- You Should Be Able To list -->
            <div class="KeyLessonList">
                <?php
                // Check rows exists.
                if( have_rows('you_should_be_able_to') ):
                    ?>
                    <h3>You Should Be Able To</h3>
                    <?php
                    // Loop through rows.
                    keyLessonList('you_should_be_able_to');
                // No value.
                else :
                    // Do something...
                endif;
                ?>
            </div>
        </article>
        </main>
    </div>
    <?php  if ( get_field('show_sidebar') == 'yes'): ?>
    <div class="large-4 columns">
    <?php the_field('sidebar'); ?>
    <?php  if ( get_field('show_testimonial_in_sidebar') == 'yes'): ?>
    <?php get_template_part ('template-parts/testimonials'); ?>
    <?php endif; ?>
    </div>
    <?php endif; ?>
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

function keyLessonList($listName) {
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