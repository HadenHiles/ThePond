<?php ?>

<?php if( have_rows('documents')  || have_rows('resources')  ): ?>  
<div class="clResourcesBox">
<?php if( have_rows('documents')  ): ?>  
<div class="Documents">
<h4><i class="fa fa-download"></i> Related Downloads</h4>
<ul>
<?php while( have_rows('documents') ): the_row(); 
// vars
$title = get_sub_field('file_name');
$link = get_sub_field('file');
$icon = get_sub_field('file_type');
$exlink = get_sub_field('external_link');

?>  
<li>
<a href="<?php echo $link ?><?php echo $exlink ?>" target="_blank" alt="<?php echo $title ?> Download"><?php echo $icon ?> <?php echo $title ?></a>
</li>
<?php endwhile; ?>  
</ul>  
</div>  
<?php endif; ?>

<!--END Docs Embed -->

<?php if( have_rows('resources') ): ?>
 
<div class="Documents Resources">
<h4><i class="fa fa-link"></i> Related Resources</h4>
<ul>
<?php while( have_rows('resources') ): the_row(); 
// vars
$title = get_sub_field('resource_name');
$link = get_sub_field('resource_link');
$icon = get_sub_field('resource_type');
?>
<li>
<a href="<?php echo $link ?>" target="_blank"><?php echo $icon ?> <?php echo $title ?></a>
</li>
<?php endwhile; ?>   
</ul>
</div> 
<?php endif; ?>
</div> 
<!--END Resources Embed -->	
<?php endif; ?>