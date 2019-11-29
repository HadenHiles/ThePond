<?php
/**
 * Rows of Users for a selected Course
 */
?>
<?php 
if ( current_user_can( 'edit_user', $activity->user_id ) ) { 
	$user_link = get_edit_user_link( $activity->user_id ) ."#ld_course_info";
} else {
	$user_link = "#";
}

if ( current_user_can( 'edit_courses', $activity->post_id ) ) { 
	$post_link = get_edit_post_link( $activity->post_id ) ."#ld_course_info";
} else {
	$post_link = "#";
}

switch ( $header_key ) {
	case 'checkbox':
		?><input class="ld-propanel-report-checkbox" type="checkbox" data-user-id="<?php echo $activity->user_id; ?>"><?php
		break;
	
	case 'course_id':
		echo $activity->post_id;
		break;
		
	case 'course':	
		if ( $this->post_data['container_type'] == 'full' ) {
			echo esc_html( $activity->post_title );
		} else {
			?>
			<strong title="<?php echo sprintf( _x( '%s ID:', 'Course ID:', 'ld_propanel' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?> <?php echo $activity->post_id; ?>" class="display-name"><?php echo esc_html( $activity->post_title ); ?></strong>
			<p class="user-login"><a href="<?php echo $user_link; ?>" title="<?php echo esc_attr( $activity->user_display_name ); ?>"><?php echo esc_html( $activity->user_display_name ); ?></a></p>
			<p class="user-email"><a href="mailto:<?php echo esc_attr( $activity->user_email ); ?>" title="<?php printf( esc_attr__( 'Compose a new mail to %s', 'ld_propanel' ), $activity->user_email ); ?>"><?php echo esc_html( $activity->user_email ); ?></a></p><?php
		}
		break;
	
	case 'user_id':
		echo $activity->user_id;
		break;
	
	case 'user':
		?>
		<p class="user-login"><a href="<?php echo $user_link; ?>" title="<?php echo esc_attr( $activity->user_display_name ); ?>"><?php echo esc_html( $activity->user_display_name ); ?></a></p>
		<p class="user-email"><a href="mailto:<?php echo esc_attr( $activity->user_email ); ?>" title="<?php printf( esc_attr__( 'Compose a new mail to %s', 'ld_propanel' ), $activity->user_email ); ?>"><?php echo esc_html( $activity->user_email ); ?></a></p>
		<?php
	
		break;
	
	case 'progress':
		?><div class="progress-bar" title="<?php echo sprintf( __("%d of %d steps completed", 'ld_propanel'), LearnDash_ProPanel_Activity::get_activity_steps_completed( $activity ), LearnDash_ProPanel_Activity::get_activity_steps_total( $activity ) ) ?>"><?php 
			$progress_label_style = '';
			if ( is_null( $activity->activity_status ) ) {
				$progress_percent = 0;
				$progress_label = __('Not Started', 'ld_propanel' );
				$progress_label_style = 'font-size: 16px;';
			} else if ( $activity->activity_status == false ) {
				$steps_completed = LearnDash_ProPanel_Activity::get_activity_steps_completed( $activity );
				$steps_total = LearnDash_ProPanel_Activity::get_activity_steps_total( $activity );
				$progress_percent = round( 100 * ( intval( $steps_completed ) / intval( $steps_total ) ) );
				$progress_label = $progress_percent .'%';
				$progress_label_style = '';
			} else if ( $activity->activity_status == true ) {
				$progress_percent = 100;
				$progress_label = $progress_percent .'%';
			}
			?>
			<span class="actual-progress" style="width: <?php echo $progress_percent; ?>%;"></span>
		</div>
		<strong class="progress-amount" style="<?php echo $progress_label_style ?>"><?php echo $progress_label; ?></strong>
		<?php
		break;

	case 'last_update':
		echo learndash_adjust_date_time_display( intval($activity->activity_completed) ); 
		break;
		
	default:
		break;
}