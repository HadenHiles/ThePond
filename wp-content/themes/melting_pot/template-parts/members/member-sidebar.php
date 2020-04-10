 
<?php
$sidebar = get_field('sidebar');
if($sidebar) {
dynamic_sidebar($sidebar);
}	
?>

 
<?php
$sidebar = get_field('sidebar_two');
if($sidebar) {
dynamic_sidebar($sidebar);
}	
?>