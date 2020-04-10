<?php 
/**
 *  Template Name: Optin Page
 */
 get_header( 'blank' ); ?>
</header>
	

<section>
<div class="row">
	<?php 
	if(have_posts()): while (have_posts()): the_post();
	?>
	<div class="large-12 columns centered">
		
		<h1>7 things to change on your website to get more conversions in just 7 days</h1>
       
    </div>
	
	<div class="large-7 columns">

		 <main role="main">
        <article>
        	<?php the_content();?>
			
			<div class="large-5 columns">
			
			<img src="http://test.meltdesigndev.co.uk/wp-content/uploads/2019/03/7ways-ebook-245x300.png" />
				
				</div>
			<div class="large-7 columns">
			
			<h3>What you'll learn</h3>
			<ul>
			<li>How to turn your website into a conversion first focused website – designed to generate you leads and sales.</li>
				<li>The type of content that works best on websites</li>
				<li>How to connect with your site visitors</li>
				<li>The one thing 95% of businesses do wrong on their websites</li>
			</ul>
			</div>
        </article>
        </main>
	</div>
	
	<div class="large-5 columns">
		
	<div class="optinForm">
		<h3>Optin Form Headline</h3>
		<p>Text instrction to get the download</p>
		  <?php echo do_shortcode('[gravityform id="2" title="false" description="false" tabindex="10"]'); ?>
      </div>
		
	</div>
		
    <?php endwhile; endif;?>
</div>
</section>
<!-- End Main Section -->
 	
<?php  get_footer( 'blank' ); ?>