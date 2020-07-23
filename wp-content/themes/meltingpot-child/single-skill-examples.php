<?php 
ini_set('display_errors', 1);
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
        <div class="small-6 columns title-content-left">
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
            </h3>
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
        <div class="small-6 columns puck-rating title-content-right">
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
</section>
</header>
<!-- Main Section -->
<section class="MainContent skill-example">
<div class="row">
	<?php 
	if(have_posts()): while (have_posts()): the_post();
	?>
	<div class="large-8 medium-6 columns" style="margin: 15px auto;">
        <main role="main">
            <article>
                <h2>Breakdown</h2>
                <div class="bootstrap-styles">
                    <div class="card">
                        <div class="card-img-top">
                            <div class="videoWrapper">
                                <?=get_field('video_code');?>	
                            </div>
                        </div>
                        <div class="card-body">
                            <?php the_content();?>
                        </div>
                    </div>
                </div>
            </article>
        </main>
    </div>
    <div class="large-4 medium-6 columns" style="margin: 15px auto;">
        <main role="main">
            <article>
                <?php
                // Check rows exists.
                if( have_rows('examples') ):
                    ?>
                    <h2>Examples</h2>
                    <div class="bootstrap-styles gifs">
                        <?php
                        // Loop through gifs.
                        while( have_rows('examples') ) : the_row();
                            // Load sub field values.
                            $gif = get_sub_field('gif');
                            $video = get_sub_field('video_code');
                            $description = get_sub_field('description');
                            ?>
                            <div class="card gif">
                                <?php
                                if (!empty($gif)) {
                                    ?>
                                    <img class="card-img-top" src="<?=$gif?>" alt="" data-enlargable />
                                    <?php
                                } else if (!empty($video)) {
                                    ?>
                                    <div class="card-img-top">
                                        <div class="videoWrapper">
                                            <?=$video?>
                                        </div>
                                    </div>
                                    <?php
                                }

                                if ((!empty($gif) || !empty($video)) && !empty($description)) {
                                    ?>
                                    <div class="card-body">
                                        <p class="card-text"><?=$description?></p>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        // End loop.
                        endwhile;
                        ?>
                    </div>
                    <?php
                else :
                    // Do something...
                endif;
                ?>
            </article>
        </main>
    </div>
    <?php
    endwhile; endif;
    ?>
</div>
</section>
<!-- End Main Section -->
<?php 
get_footer("members");
?>