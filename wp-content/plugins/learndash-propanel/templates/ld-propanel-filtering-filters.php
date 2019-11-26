<?php
/**
 * Learndash ProPanel Reporting - Filters sections
 */
?>
<div class="reporting-actions toggle-section <?php echo $filter_tab_display ?>" id="table-filters">

	<div class="filter-selection" style="display: inline-block;"><?php _e( 'Filter By:', 'ld_propanel' ); ?></div>
	<?php echo LearnDash_ProPanel::get_instance()->filtering_widget->show_filters(); ?>
	<p>
		<?php esc_html_e( 'Per Page:', 'ld_propanel' ); ?>
		<?php
			$per_page_array = ld_propanel_get_pager_values(); 
			if ( !empty( $per_page_array ) ) {
				?><select id="ld-propanel-pagesize" class="pagesize"><?php
				$selected_per_page = 0;
				foreach( $per_page_array as $per_page ) {
					if ( empty( $selected_per_page ) ) $selected_per_page = $per_page;
					?><option <?php selected( $selected_per_page, $per_page ) ?> value="<?php echo abs( intval( $per_page ) ) ?>"><?php echo abs( intval( $per_page ) ) ?></option><?php
				}
				?></select><?php
			}
		?>
	</p>

	<p><button type="button" class="button button-primary filter"><?php esc_html_e( 'Filter', 'ld_propanel' ); ?></button>  <button type="button" class="button reset"><?php esc_html_e( 'Reset', 'ld_propanel' ); ?></button></p>	
</div>
