<?php
get_header("members");
if(has_post_thumbnail()) {
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, ''); 
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
}
?>
</header>
<!-- Main Section -->

<section class="clbHeader SingleDirectoryHead">
	<div class="row">
		<div class="large-8 columns">
        <h1>Members Directory</h1>
		</div>
		
		<div class="large-4 columns">
			<a class="BTN" href="/members_directory/">View All Members</a>
		</div>
	</div>
</section>



<section class="memberContent membersingle">
<div class="row">

<div class="large-8 columns">
<div class="memberBio">

            <div class="biotitleBox">
            <?php if( get_field('business_logo') ): ?>
            <img src="<?php the_field('business_logo'); ?>" alt="<?php the_title();?>"/>
            <?php endif; ?>
            <h1 class="bioTitle"><strong><?php the_title();?></strong></h1>
            <p><strong>Business:</strong> <?php the_field('business'); ?> <strong>Position:</strong> <?php the_field('position'); ?> </p>
            </div>

            <!--
            <div class="bioCats">
            <p>Categories: <a href="">Web Design</a> <a href="">Marketing</a> </p>
            </div>-->


            <div class="bioDecrip">
            <h2><strong>About <?php the_title();?></strong></h2>
            <?php the_field('biography'); ?>
            </div>


            <?php if( get_field('expertise_list') ): ?>
            <div class="expertlist">
            <p><strong>Areas of expertise</strong></p>
            <?php if( have_rows('expertise_list') ): ?>
            <ul>
            <?php while( have_rows('expertise_list') ): the_row(); 
            $content = get_sub_field('expertise_item');
            ?>
            <li><?php echo $content; ?></li>
            <?php endwhile; ?>
            </ul>
            <?php endif; ?>
            </div>
            <?php endif; ?>

</div>
</div>

<div class="large-4 columns">

                <?php if(have_posts()):while(have_posts()):the_post(); ?>
                <?php $backgroundImg = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );?>

                <?php if(get_post_thumbnail_id($post->ID) ): ?>
                <div class="bio-imagewrap" style="background: url('<?php echo $backgroundImg[0]; ?>') no-repeat center; background-size: cover; ">
                </div>
                <?php endif; ?>

                <?php endwhile; endif;wp_reset_query();?>
    
    
    
<div class="ConnectWith">
<h6 class="centered"><strong>Connect With <?php the_title();?></strong></h6>

<ul>
<?php if( get_field('website_address') ): ?>
<li><a class="BTN bioWeb" target="_blank" href="<?php the_field('website_address'); ?>">View The Website</a></li>
<?php endif; ?>

<li><a class="BTN bioEmail" href="mailto:<?php the_field('email_address'); ?>">Email <?php the_title();?></a></li>
	
	<?php if( get_field('phonenumber') ): ?>
<li><a class="BTN bioEmail" href="tel:<?php the_field('phonenumber'); ?>">Call <?php the_field('phonenumber'); ?></a></li>
	<?php endif; ?>
</ul>

        <div class="bioSocialLinks">
        <ul>
        <?php if( get_field('twitter_link') ): ?>
        <li><a target="_blank" href="<?php the_field('twitter_link'); ?>"><i class="fab fa-twitter"></i></a></li>
        <?php endif; ?>
            <?php if( get_field('facebook_link') ): ?>
        <li><a target="_blank" href="<?php the_field('facebook_link'); ?>"><i class="fab fa-facebook-f"></i></a></li>
        <?php endif; ?>
            <?php if( get_field('insta_link') ): ?>
        <li><a target="_blank" href="<?php the_field('insta_link'); ?>"><i class="fab fa-instagram"></i></a></li>
        <?php endif; ?>
            <?php if( get_field('youtube_link') ): ?>
        <li><a target="_blank" href="<?php the_field('youtube_link'); ?>"><i class="fab fa-youtube"></i></a></li>
        <?php endif; ?>
            <?php if( get_field('linkedin_link') ): ?>
        <li><a target="_blank" href="<?php the_field('linkedin_link'); ?>"><i class="fab fa-linkedin-in"></i></a></li>
        <?php endif; ?>
        </ul>
        </div>

</div>
</div>



</div>
</section>

<?php  get_footer("members"); ?>