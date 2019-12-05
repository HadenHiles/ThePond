<?php
global $smof_data;
$current_user = wp_get_current_user();	 
/*  Template Name: Member Directory*/
get_header('members'); ?>
</header>

<?php get_template_part ('template-parts/content-blocks/bannerheader'); ?>

<section class="member-ask">
  <div class="row rowNarrow">
    <?php the_field('ask_text'); ?>
    <div class="formbox questionbox">
      <?php the_field('form'); ?>
    </div>
    <div class="QandASection" id="qanda">
    <div class="large-5 medium-12 columns nopad">
    <h3>Questions & Responses</h3>
        </div>
    <div class="large-7 medium-12 columns nopad">
	<form action="<?php the_permalink(); ?>" method="get">
		<input type="text" name="questionSearch" value="" />
		<button type="submit"> Search</button>
        <a href="/ask/?questionSearch=" class="BTN refreshsearch"><i class="far fa-sync-alt"></i></a>
	</form>
        </div>
	<?php
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		$args = array(
			'post_type' => 'question',
			'post_status' => 'publish',
			'paged' => $paged,
			//'posts_per_page' => 2
		
		);
	 if( isset($_GET['questionSearch'])) {
	 	$args['s'] = $_GET['questionSearch'];
	 	
	 }	
		
	  query_posts( $args ); 
	 
	?>
	
	
      <?php if(  have_posts() ): ?>
      <div class="FAQ_Wrap">
        <?php while (  have_posts() ) :  the_post(); ?>
		 <div id="faq_container"> 
			<div class="faq">
				<div class="faq_question"> <span class="question">  <?php the_title(); ?></span></div>
					<div class="faq_answer_container">
						<div class="faq_answer">
                            <span class="topic"><strong>Topic: </strong> <?php the_field('topic'); ?></span>
							<span><strong>Answer: </strong> <?php the_field('answer'); ?></span>
                            
						</div>
					</div>
				</div>
			</div> 
		<?php endwhile;?>	
      </div>
        
       <nav class="navigation paging-navigation" role="navigation">

	 

		<div class="nav-links">
 
			<?php if ( get_next_posts_link() ) : ?>

			<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'battle' ) ); ?></div>

			<?php endif; ?>



			<?php if ( get_previous_posts_link() ) : ?>

			<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'battle' ) ); ?></div>

			<?php endif; ?>



		</div><!-- .nav-links -->

	</nav>
       <?php endif; wp_reset_query(); ?>
       
        
        
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
  </div>
  </div>
</section>
<!-- End Main Section -->
<?php
/* Template Name: Logged in Footer*/
if (is_user_logged_in()):
get_footer('members');
else: 
get_footer();
endif;
?>
