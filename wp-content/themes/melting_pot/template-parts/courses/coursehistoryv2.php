<?php ?>

<div class="myContent v2">
<div class="lessonTools">
<ul class="post_tools">
<?php
$lesson_class_complete=((check_lesson_track(get_the_id(),get_current_user_id(),'1','3')) ? ' post_tool_active' : ' post_tool_inactive');
$lesson_class_fav=((check_lesson_track(get_the_id(),get_current_user_id(),'1','2')) ? ' post_tool_active' : ' post_tool_inactive');
$lesson_class_bookmark=((check_lesson_track(get_the_id(),get_current_user_id(),'1','1')) ? ' post_tool_active' : ' post_tool_inactive');
?>
<?php if( trim($lesson_class_complete)  == 'post_tool_inactive' ) { ?>
<li><a class="lesson_tool tool_complete<?php echo $lesson_class_complete; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="3"><i class="fa fa-check-circle"></i><span> </span></a></li>
<?php }else{ ?>
<li><a class="lesson_tool tool_complete<?php echo $lesson_class_complete; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="3"><i class="fa fa-check-circle"></i><span> </span></a></li>
<?php } ?>


<?php if( trim($lesson_class_fav)  == 'post_tool_inactive' ) { ?>
 <li><a class="lesson_tool tool_fav<?php echo $lesson_class_fav; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="2"><i class="fas fa-heart"></i><span> </span></a></li>
 <?php }else{ ?>
  <li><a class="lesson_tool tool_fav<?php echo $lesson_class_fav; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="2"><i class="fas fa-heart"></i><span> </span></a></li>
<?php } ?> 

<?php if( trim($lesson_class_bookmark)  == 'post_tool_inactive' ) { ?>
<li><a class="lesson_tool tool_bookmark<?php echo $lesson_class_bookmark; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="1"><i class="fas fa-bookmark"></i> <span></span></a></li>
<?php }else{ ?>
	<li><a class="lesson_tool tool_bookmark<?php echo $lesson_class_bookmark; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="1"><i class="fas fa-bookmark"></i> <span> </span></a></li>
<?php } ?> 
</ul>
									
</div>
</div>         