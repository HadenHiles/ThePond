<?php
/***  Template Name: Member Roadmap */ 
get_header("members");
 
$steps = get_option('road_map_complete_step');

 
?>
</header>



<section class="your-dashboard roadmap-intro">
	<div class="row">
		<div class="large-9 large-centered">
			<? if(get_field('roadmap_image')) { ?>
			<div class="roadmap-image"><img src="<? echo the_field('roadmap_image');?>"></div>
			<? } ?>
			<div class="dash-container">
				<div class="contain-content">
				<h2><? echo the_field('banner_header'); ?></h2>
				<p><? echo the_field('banner_sub_header'); ?></p>
				<style>
					#myProgress {
					  width: 100%;
					  background-color: #ddd;
					}

					#myBar {
					  width: 50%;
					  height: 30px;
					  background-color: #4CAF50;
					  text-align: center;
					  line-height: 30px;
					  color: white;
					}
				</style>
					<?php
						$noOfRow = count(get_field("roadmap"));
						$wihtPercent = 100 / $noOfRow; 
						$completedSteps = count($steps);

						$proressWith = $wihtPercent * $completedSteps;
						$proressWith = number_format( $proressWith , 1);
					?>
					<div id="myProgress">
					  <div id="myBar" style="width:<?php echo $proressWith; ?>%"><?php echo $proressWith;?>% Complete </div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>



<section class="cd-timeline js-cd-timeline roadmap-section roadmap-feed" >
  <div class="row">
	  <div class="end-roadmap-ends">	  
	  <div class="large-9 large-centered columns cd-timeline__container">
			<div class="accordion">		
				<?php if( have_rows('roadmap') ): $counter = 0; // counter ?>
					<div class="roadmap_wrap">
						 <?php while ( have_rows('roadmap') ) : the_row(); $counter++ // counter add to ?>
						 <div class="cd-timeline__block js-cd-block">
							<div class="cd-timeline__img cd-timeline__img--picture js-cd-img count-me">
								<span><? $icon = the_sub_field('icon'); ?>			
								
								</span>
							</div> <!-- cd-timeline__img -->
							<div class="cd-timeline__content js-cd-content">
							<div id="faq_container"> 
								 <div class="faq">
									 <div class="faq_question"> 
										 <span class="question">
											<span>Item <? echo $counter ?></span>
											 
											<?php if( !in_array( $counter , $steps ) ) { ?>
											<a class="markcompleteRoad icon-outer-r" href="javascript:void(0);" data-id="<? echo $counter ?>"> <i class="fad fa-times-circle"></i></a>
											<?php } ?>
											 
											<?php if( in_array(  $counter , $steps ) ) { ?>
											<span class="if-comp"> <i class="fad fa-check-circle"></i></span>
											<?php } ?>
										 </span>
										 <span class="accordion-button-icon fa fa-plus"></span>
									 </div>
									 <div class="faq_answer_container">
										 <div class="faq_answer">
											<h2><? the_sub_field('roadmap_step_title'); ?></h2>
											<? the_sub_field('roadmap_step_textinformation'); ?>
											 
											 <? if(get_sub_field('video')) : ?>	
											 <div class="road-wrap-vid">
											 	<iframe width="560" height="315" src="<? echo the_sub_field('video'); ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
											   </iframe>
											 </div>
											 <? endif ?>
											 
											 <? if(get_sub_field('roadmap_step_after_video')) : ?>
												 <div class="after-video"><? echo the_sub_field('roadmap_step_after_video'); ?></div>
											 <? endif; ?>
											 
											 <? if(have_rows('additional_resources')): ?>
											 <div class="wrap-reso"></div>
											  <h4>Additional Resources:</h4>
											 	<ul class="road-res">
											 	<? while ( have_rows('additional_resources')): the_row(); ?>
											 		<li class="road-res-each"><a href="<? echo the_sub_field('resource_link'); ?>"><i class="far fa-link"></i> <? echo the_sub_field('resource_item'); ?></a></li>	
												 <? endwhile;?>
											 	</ul>
											 
											 <? endif; ?>	 

											<?php if( !in_array( $counter , $steps ) ) { ?>
											<a class="BTN markcompleteRoad" href="javascript:void(0);" data-id="<? echo $counter ?>"> Mark Complete</a>
											<?php } ?>
										 
										 </div>
										 </div>
									 </div>
								 </div>
								</div> <!-- cd-timeline__content -->
							</div> <!-- cd-timeline__block -->	 
								 
						 <? endwhile;?>
						 	<div class="roadmap-foot">
								<?php if( $counter == count($steps) ) { ?>
								<a class="resetroadMap BTN" href="javascript:void(0);"> Reset Roadmap</a>
								<?php } ?>
							</div>
						 </div>
					 </div>
				<?php endif; ?>			
			</div>
		  </div>
		</div>
</section>


<script>
//Roadmap js

jQuery(document).ready( function(){
	jQuery('.markcompleteRoad').click( function(e){
		e.preventDefault();
		var step = jQuery(this).data('id');
		
		jQuery.ajax({
			type: 'POST',
			url: meltObj.ajax_url,
			data: {
				'action': 'aj_road_mark_complete',
				'step': step //calls wp_ajax_nopriv_ajaxlogout
			 },
			success: function(r){
				 window.location = "<?php echo get_the_permalink();?>";
			}
		});
		
	
	});
	
	jQuery('.resetroadMap').click( function(e){
		e.preventDefault();
	 	
		jQuery.ajax({
			type: 'POST',
			url: meltObj.ajax_url,
			data: {
				'action': 'aj_reset_road_map',
		 	 },
			success: function(r){
				 window.location = "<?php echo get_the_permalink();?>";
			}
		});
		
	
	});


});
jQuery(document).ready(function($)  {
	 $('.faq_question').click(function() {
	 if ($(this).parent().is('.open')){
		 $(this).closest('.faq').find('.faq_answer_container').animate({'height':'0'},500);
		 $(this).closest('.faq').removeClass('open');
		 $(this).parent().find('.accordion-button-icon').removeClass('fa-minus').addClass('fa-plus');
	 }
	 else{
		 var newHeight =$(this).closest('.faq').find('.faq_answer').height() +'px';
		 $(this).closest('.faq').find('.faq_answer_container').animate({'height':newHeight},500);
		 $(this).closest('.faq').addClass('open');
		 $(this).parent().find('.accordion-button-icon').removeClass('fa-plus').addClass('fa-minus');
	 }
 });
});		
	

</script>

<?  get_footer("members"); ?>