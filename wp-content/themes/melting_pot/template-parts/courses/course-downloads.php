<?php 

$course_id = learndash_get_course_id();
?>
  <?php if( have_rows('documents', $course_id ) ||  have_rows('resources', $course_id ) ) : ?>
<div class="courseResources">
  <div class="clResourcesBox courseResources">

    <?php if( have_rows('documents', $course_id ) ): ?>
    <div class="Documents">
    <h4><i class="fad fa-download"></i> Course Downloads</h4>
      <ul>
        <?php while( have_rows('documents', $course_id) ): the_row(); 
	// vars
	$title = get_sub_field('file_name');
	$link = get_sub_field('file');
	$icon = get_sub_field('file_type');
	?>
        <li> <a href="<?php echo $link ?>" target="_blank" alt="<?php echo $title ?> Download"><?php echo $icon ?> <?php echo $title ?></a> </li>
        <?php endwhile; ?>
      </ul>
    </div>
    <?php endif; ?>
    <!--END Docs Embed -->
    <?php if( have_rows('resources', $course_id) ): ?>
    <div class="Documents Resources">
        <h4><i class="fad fa-link"></i> Course Resources</h4>
      <ul>
        <?php while( have_rows('resources', $course_id) ): the_row(); 
		// vars
		$title = get_sub_field('resource_name');
		$link = get_sub_field('resource_link');
		$icon = get_sub_field('resource_type');
		?>
        <li> <a href="<?php echo $link ?>" target="_blank"><?php echo $icon ?> <?php echo $title ?></a> </li>
        <?php endwhile; ?>
      </ul>
    </div>
    <?php endif; ?>
    <!--END Resources Embed -->
  </div>
</div>
<?php endif; ?>