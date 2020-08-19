<?php ?>

<?php if( get_field('above_media') ): ?>
<?php the_field('above_media'); ?>
<?php endif; ?>
    <?php
    //Unauthorized
    if (!current_user_can('mepr-active','rules:487')) {
        ?>
        <div class="bootstrap-styles">
            <div class="card">
                <a href="/">
                    <img src="https://cdn.thepond.howtohockey.com/2020/08/block-ad-final-scaled-1.jpg" alt="" class="card-img-top" />
                </a>
            </div>
        </div>
        <?php
    } else {
        if (get_field('course_image')): ?>    
            <div class="sfwdvideo">
            <?php if( get_field('course_image') ): ?>
                <div class="videoWrapper"><img src="<?php the_field('course_image'); ?>" alt="<?php the_title();?>"/></div>
            <?php endif; ?>
            </div>	
            <?php endif; ?>
            <!-- End Video Embed -->
    
    
            <?php if (get_field('video_code')): ?>    
            <div class="sfwdvideo videowrapper">
            <?php if( get_field('video_code') ): ?>
                <div class="videoWrapper"><?php the_field('video_code'); ?></div>
            <?php endif; ?>
            </div>	
            <?php endif; ?>
            <!-- End Video Embed -->
    
    
            <!-- Jump To Links-->
            <?php if ( get_field('enable_video_jump_links') =='yes'): ?>
    
            <?php get_template_part('template-parts/courses/course-jumpto'); ?>
    
    
            <?php endif; ?>
    
    
            <!--End jump Links-->
    
            <?php if (get_field('add_audio') == 'yes'): ?>    
    
                    <?php if( get_field('download_embed') == "download"): ?>
                        <?php if (get_field('download_audio_file')): ?>
                    <div class="sfwdaudio downloadaudio">
                    <h4>Download Audio</h4>
                    <a href="<?php the_field('download_audio_file'); ?>" download class="BTN">Download Audio</a>
                    </div>
                        <?php endif; ?>
                    <?php endif; ?>   
    
                    <?php if( get_field('download_embed') == "embed"): ?>
                        <?php if (get_field('audio_id')): ?>
                    <div class="sfwdaudio">
                    <h4>Listen to Audio</h4>
                    <iframe style="border: none" src="//html5-player.libsyn.com/embed/episode/id/<?php the_field('audio_id'); ?>/height/90/theme/custom/thumbnail/no/direction/backward/render-playlist/no/<?php  if ( get_field('custom_libsyn_colour_code')): ?>custom-color/<?php the_field('custom_libsyn_colour_code'); ?>/<?php endif; ?>" height="90" width="100%" scrolling="no"  allowfullscreen webkitallowfullscreen mozallowfullscreen oallowfullscreen msallowfullscreen></iframe>
                    </div>
                        <?php endif; ?>
                    <?php endif; ?>
                
            <?php endif; ?>
            <!-- End Audio Embed -->
    <?php } ?>









<?php if( get_field('below_media') ): ?>
<?php the_field('below_media'); ?>
<?php endif; ?>