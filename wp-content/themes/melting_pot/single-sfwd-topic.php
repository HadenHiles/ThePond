<?php
global $smof_data;

$layout = $smof_data['course_page_layout'];

if( empty( $layout ) )
 $layout = "one";
 
 	 
get_template_part('learndash/layout/single-topic-layout' , $layout );