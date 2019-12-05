<?php ?>

<div class="accordion">

<?php if( get_field('faq_title') ): ?>
<h3><?php the_field('faq_title'); ?></h3>
<?php endif; ?>
	
<?php if( get_field('faq_sub_heading') ): ?>
<p><?php the_field('faq_sub_heading'); ?></p>
<?php endif; ?>	
	
<?php if( have_rows('ik_faq') ): ?>
<div class="FAQ_Wrap">
<?php while ( have_rows('ik_faq') ) : the_row();
echo'<div id="faq_container"> 
<div class="faq">
<div class="faq_question"> <span class="question">' . get_sub_field('ik_question') . '</span></div>
<div class="faq_answer_container">
<div class="faq_answer"><span>' . get_sub_field('ik_answer') . '</span></div>
</div>
</div>
</div>';
endwhile;?>
</div>
<?php endif; ?>


</div>


<script>

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
