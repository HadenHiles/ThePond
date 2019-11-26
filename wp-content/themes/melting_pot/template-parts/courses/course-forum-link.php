<?php $course_id = learndash_get_course_id(); 
  
 ?>
  
	<? if(get_field('link_to_forum' , $course_id) === "yes"): ?>
		<a href="<? echo the_field('forum_link' , $course_id); ?>" class="forumQuestion">
				<i class="far fa-question-circle"></i> Have a question about this course? Post it in the forum</a>
		<? else: echo ""; ?>
	<? endif; ?>
 