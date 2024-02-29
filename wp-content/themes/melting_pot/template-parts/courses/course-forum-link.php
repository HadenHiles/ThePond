<?php $course_id = learndash_get_course_id();

?>

<? if (get_field('link_to_forum', $course_id) === "yes") : ?>
	<?php /*<a href="<? echo echo get_field('forum_link' , $course_id); ?>" class="forumQuestion">*/ ?>
	<a href="/oauth/authorize/?response_type=code&client_id=2H7ipcD9JE5VRlOsZqTC8qX3DQFiOznkSZ9JpS8h&redirect_uri=https://thepond.howtohockey.com/squad/oauth_login/wordpress" class="forumQuestion">
		<i class="far fa-question-circle"></i> Have a question about this course? Post it in the squad</a>
<? else : echo ""; ?>
<? endif; ?>