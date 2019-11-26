<?php 
$callSedule = 2476;   
if( get_field( 'show-sitewide' , $callSedule) ) { 
?>

<div class="wrap-timer-footer">
<div class="upcommingEvent">
	<div class="row">
	
<?php if( have_rows('call_schedule' , $callSedule) ):
   	$i = 0;                                              
    while( have_rows('call_schedule' , $callSedule) ): the_row();
	// vars
		$calldate = get_sub_field('call_date');
		$date_now = current_time('timestamp');// date("Y-m-d H:i:s");
		$callname = get_sub_field('call_name');
		$callsubject = get_sub_field('call_subject');
		$calltime = get_sub_field('time');
		$calltimezone = get_sub_field('time_zone');
		$endevent = get_sub_field('call_date_end');
	
		
	
	
	// Vars for Shares 
		$atctitle = isset($instance['atctitle']) ? $instance['atctitle'] : 'Add to Calendar' ;
		$title = $callname;
		$content = $callsubject;
		$timezone = $calltimezone;
		$startdate = $calldate;
		$enddate = $endevent;
		$eventlink = $instance['eventlink'] ? $instance['eventlink'] : site_url();
	  
		if( strtotime($calldate) >  $date_now ) {
		 	$countDownDate =  date('D M d Y H:i:s O' , strtotime($calldate));
	?>
	<?php $i++; ?>
		<?php if( $i > 1 ): ?>
			<?php break; ?>
		<?php endif; ?>
	<div class="large-6 columns">
	<div class="callDetails">
		
		<div class="icon-main">
			<i class="fal fa-microphone-alt"></i>
		</div>
		
		<h3>Next Live Call</h3>
	
			<!-- <h3>Next Live Call</h3>-->
			<?php //echo $calldate;
			$date = $calldate; // Reformat date for display purposes & hide time.
			$createDate = new DateTime($date);
			$strip = $createDate->format('D d M Y');
			//var_dump($strip); // string(10) "2012-09-09"			
			//echo $strip; 
		
			?>		
			<p><?php echo $callname; ?> - <? echo $strip; ?></p>

			<?
			// Create START date convert to time
			$date = $calldate; 
			$createDate = new DateTime($date);

			$timeonly = $createDate->format('H:i');
			
			// Create END date convert to time
			$edate = $enddate; 
			$createDate = new DateTime($edate);

			$timeonlyend = $createDate->format('H:i'); ?>

			<!-- Both dates together -->
			<!-- <p><? echo $timeonly; ?> to <? echo $timeonlyend; ?> <span><?php echo $calltimezone; ?></span></p> -->
			
			
	</div>
	</div>
	
	<div class="large-3 columns">			
		<ul class="callTime">
			<li><span id="days" class="number"></span><span class="number_unit">Days</span></li>
			<li><span id="hours" class="number"></span><span class="number_unit">Hours</span></li>
			<li><span id="mins" class="number"></span><span class="number_unit">Mins</span></li>
			<li><span id="secs" class="number"></span><span class="number_unit">Secs</span></li>
		</ul>

<script>
// Set the date we're counting down to
var countDownDate = new Date("<?php echo  $countDownDate; ?>").getTime();

// Update the count down every 1 second
var x = setInterval(function() {

  // Get today's date and time
  var now = new Date().getTime();

  // Find the distance between now and the count down date
  var distance = countDownDate - now;

  // Time calculations for days, hours, minutes and seconds
  var days = Math.floor(distance / (1000 * 60 * 60 * 24));
  var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  var seconds = Math.floor((distance % (1000 * 60)) / 1000);

  // Display the result in the element with id="demo"
  document.getElementById("days").innerHTML = days ;
  document.getElementById("hours").innerHTML = hours; 
   document.getElementById("mins").innerHTML = minutes;
    document.getElementById("secs").innerHTML = seconds;

  // If the count down is finished, write some text
  if (distance < 0) {
    clearInterval(x);
    document.getElementById("demo").innerHTML = "EXPIRED";
  }
}, 1000);
</script>
		
	</div>
	
	<div class="large-3 columns">
			
			
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
	</div>
	<?php   } ?>
	<?php endwhile;
	else:
    endif; ?>	
        
        </div>
	</div>
</div>
</div>

<style>
	
/*This CSS is in the members-next-call template */
.atcb-list {
	top: -135px;
	left: 20px;
}
.footer {
	padding-bottom: 90px;
}
</style>

<?php } ?>