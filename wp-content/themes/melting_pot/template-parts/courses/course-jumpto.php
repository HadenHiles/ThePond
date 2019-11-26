<?php 
$course_id = learndash_get_course_id();
?>

    
    <?php if( have_rows('video_jump_to') ): ?>
    <div class="JumpTo">
        <h4><?php  if ( get_field('video_jump_to_title')): ?><?php the_field('video_jump_to_title'); ?><?php else: ?>Looking for something specific?<?php endif; ?></h4>
      <ul>
        <?php while( have_rows('video_jump_to') ): the_row(); 
	// vars
	$title = get_sub_field('jump_to_name');
	$time = get_sub_field('jump_to_time');
	?>
        <li>
        <a class="jumplink" data-jumptime="<?php echo $time ?>">
        <span class="jumptitle"><i class="fad fa-hand-pointer"></i> <?php echo $title ?></span>
        <span class="jumptime"><i class="fad fa-stopwatch"></i> <?php echo $time ?> Secs</span>
        </a>
        </li>
        <?php endwhile; ?>
      </ul>
    </div>
    <?php endif; ?>
    <!--END Docs Embed -->

<script src="//player.vimeo.com/api/player.js"></script>
<script>
var iframe = document.querySelector('.videowrapper iframe');
var player = new Vimeo.Player(iframe);
jQuery('.jumplink').bind('click', function() {
player.setCurrentTime(jQuery(this).data('jumptime')).then(function(seconds) {
player.play();
});
});
</script>
   