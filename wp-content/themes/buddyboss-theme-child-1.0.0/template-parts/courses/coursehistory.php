<?php
$favorite = check_lesson_track(get_the_ID(), get_current_user_id(), 1, 2);
$bookmark = check_lesson_track(get_the_ID(), get_current_user_id(), 1, 1);
?>
<div class="myContent lessonTools">
    <ul class="post_tools">
        <li>
            <a role="button" tabindex="0" class="lesson_tool tool_fav <?php echo $favorite ? 'post_tool_active' : 'post_tool_inactive'; ?>" data-lesson-id="<?php echo absint(get_the_ID()); ?>" data-track-type="2">
                <i class="fa fa-heart"></i> <span><?php echo $favorite ? 'Remove from favourites' : 'Favourite'; ?></span>
            </a>
        </li>
        <li>
            <a role="button" tabindex="0" class="lesson_tool tool_bookmark <?php echo $bookmark ? 'post_tool_active' : 'post_tool_inactive'; ?>" data-lesson-id="<?php echo absint(get_the_ID()); ?>" data-track-type="1">
                <i class="fa fa-bookmark"></i> <span><?php echo $bookmark ? 'Remove Bookmark' : 'Bookmark'; ?></span>
            </a>
        </li>
    </ul>
</div>
