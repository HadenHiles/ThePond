<?php  
global $smof_data;

if( is_user_logged_in() )
	get_header('members');
else
	get_header();



$bgIMge = $smof_data['404_image'];
 ?>
</header>
<section class="MainContent" id="ErrorBG" style="background-image: url(<?php echo $bgIMge; ?>)">

<div class="row">
<div class="large-8 columns ">
    <main role="main">

        <article>

			<h1 class="BlueError">404</h1>
            <h4>OOOPS Something Went Wrong</h4>

            <p>We can't find the page you're looking for</p>

            <a class="BTN" href="/">Click Here To Return To The Homepage</a>

            

        </article>

    </main>

</div>





</div>

</section><!-- End Main Content --> 	

<?php  

if( is_user_logged_in() )
	get_footer('members');
else
	get_footer('blank');

 ?>