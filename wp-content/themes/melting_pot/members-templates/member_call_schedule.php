<?php
/***  Template Name: Member Call Schedule */ 
get_header("members");
?>
</header>


<section class="Banner" style="background-image: <?php if ( get_field('banner_image')): ?>url('<?php the_field('banner_image'); ?>');<?php else:?><?php endif; ?>" >
<div class="row"> 
	<div class="<?php the_field('center_banner_content'); ?>">
	<h1><?php the_field('banner_headline'); ?></h1>
    <h2><?php the_field('bannner_sub_header'); ?></h2>	
    <a href="<?php the_field('meeting_room_link'); ?>" target="_blank" class="BTN"><?php the_field('button_text'); ?></a> 
	</div>
</div>
</section>


<?php //get_template_part('template-parts/members/members-next-call'); ?>


<section class="">
	<div class="row">
<?php if( have_rows('call_schedule') ): ?>
<table class="callSchedule" border="1" cellpadding="0" cellspacing="0">
	
<thead>
	<tr>
		<td class="tdDate">Date <? echo the_field('date_icon'); ?></td>
		<td class="tdDesc">Call Description & Duration <? echo the_field('call_icon'); ?></td>
		<td class="tdTime">Time <? echo the_field('time_icon'); ?></td>
		<td class="tdCallLink">&nbsp;</td>
	</tr>
</thead>
	
	
	
	<?php while( have_rows('call_schedule') ): the_row();
	// vars
		$calldate = get_sub_field('call_date');
		$callname = get_sub_field('call_name');
		$callsubject = get_sub_field('call_subject');
		$calltime = get_sub_field('time');
		$calltimezone = get_sub_field('time_zone');
		$endevent = get_sub_field('call_date_end');
		$date_now = current_time('timestamp'); //date("Y-m-d H:i:s");
	
	
	
	
	
	// Vars for Shares 
		$atctitle = isset($instance['atctitle']) ? $instance['atctitle'] : 'Add to Calendar' ;
		$title = $callname;
		$content = $callsubject;
		$timezone = $calltimezone;
		$startdate = $calldate;
		$enddate = $endevent;
	
		$eventlink = get_field('meeting_room_link');
		if(get_sub_field('zoom_link')) {
			$eventlink = get_sub_field('zoom_link');
		}
		 
		if( strtotime($calldate) > $date_now ) {
	?>
	
	<tr valign="top">
		<td class="tdDate">
			<?php //echo $calldate;
			

			
			$date = $calldate; // Reformat date for display purposes & hide time.
			$createDate = new DateTime($date);

			$strip = $createDate->format('M Y');
			$nameday = $createDate->format('D');
			//var_dump($strip); // string(10) "2012-09-09"	
			$day = $createDate->format('d');
			
			$ends = array('th','st','nd','rd','th','th','th','th','th','th');
			if (($day %100) >= 11 && ($day%100) <= 13) {
			   $abbreviation = $day. 'th';
			} else {
			   $abbreviation = $day. $ends[$day % 10];
			}
			
			echo $nameday . ' <span>' . $abbreviation . '</span> ' . $strip . '' ; 
		
			?>		
		
		</td>
		<td class="tdDesc"><h4><?php echo $callname; ?></h4><p> <?php echo $callsubject; ?></p></td>
		<td class="tdTime">
			<?
			// Create START date convert to time
			$date = $calldate; 
			$createDate = new DateTime($date);

			$timeonly = $createDate->format('h:i A');
			
			// Create END date convert to time
			$edate = $enddate; 
			$createDate = new DateTime($edate);

			$timeonlyend = $createDate->format('h:i A'); ?>
			
			
			<!-- Both dates together -->
			<p><? echo $timeonly; ?> to <? echo $timeonlyend; ?></p>
		
						
			<p><?php echo $calltimezone; ?></p>
			
		</td>
		<td class="tdCallLink centered">
			<link href="//addtocalendar.com/atc/1.5/atc-style-menu-wb.css" rel="stylesheet" type="text/css">

		    <script type="text/javascript">(function () {
				if (window.addtocalendar)if(typeof window.addtocalendar.start == "function")return;
				if (window.ifaddtocalendar == undefined) { window.ifaddtocalendar = 1;
					var d = document, s = d.createElement('script'), g = 'getElementsByTagName';
					s.type = 'text/javascript';s.charset = 'UTF-8';s.async = true;
					s.src = ('https:' == window.location.protocol ? 'https' : 'http')+'://addtocalendar.com/atc/1.5/atc.min.js';
					var h = d[g]('body')[0];h.appendChild(s); }})();
		    </script>

			<aside class="sidebar_orange next_training">

				<div>
			    <!-- 3. Place event data -->
			    <span class="addtocalendar atc-style-menu-wb">
			    	<a class="atcb-link BTN"><?php if (isset($atctitle)) : echo $atctitle; else: echo 'Add to Calendar'; endif; ?></a>
			        <var class="atc_event">
			            <var class="atc_date_start"><?php echo $startdate; ?></var>
			            <var class="atc_date_end"><?php echo $enddate; ?></var>
			            <var class="atc_timezone"><?php echo $timezone; ?></var>
			            <var class="atc_title"><?php echo $title; ?></var>
			            <var class="atc_description"><?php echo $content; ?></var>
			            <var class="atc_location"><?php echo $eventlink; ?></var>
			        </var>
			    </span>			
			    </div>	

			</aside>
		</td>
	</tr>
	<?php } ?>
	<?php endwhile; ?>
</table>
		
<?php endif; ?>

	</div>
</section>



<?  get_footer("members"); ?>