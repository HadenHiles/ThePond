
// jQuery(window).load(function() {
jQuery(document).ready(function() {
	function learndash_course_grid_course_edit_page_javascript() {
		jQuery("select[name=sfwd-courses_course_price_type]").change(function(){
			var price_type = 	jQuery("select[name=sfwd-courses_course_price_type]").val();
			if(price_type == "closed") 
				jQuery("#sfwd-courses_course_price").show();
		});
		jQuery("select[name=sfwd-courses_course_price_type]").change();
	}
	if(jQuery(".sfwd-courses_settings").length)
	setTimeout( function() {learndash_course_grid_course_edit_page_javascript();}, 1000);
});
