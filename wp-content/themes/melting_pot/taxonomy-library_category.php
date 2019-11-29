<?php get_header("members"); 
?> 
</header>
<section class="clbHeader">
<div class="row">
<div class="large-8 columns">
<h1><?php echo single_cat_title();?></h1>
</div>
<div class="large-4 columns">
<a class="backBTN" href="/content-library/">
<i class="fas fa-angle-left"></i> All Library Items</a>
</div>
</div>
</section>
<!-- section-->
<section class="memberContent">
<div class="row">
<div class="large-12 columns">
<main>
<div class="filtr-container archive">
<?php while(have_posts()) : the_post();
$post_thumbnail_id = get_post_thumbnail_id();
$img =  wp_get_attachment_image_url( $post_thumbnail_id , 'full');
?>
<div class="filtr-item">
<div class="coursePrevImage" style="background: url('<? echo $img; ?>')no-repeat;background-size:cover;background-position:center;">
<?php if( get_field('available') ){ ?>
<a href="<? the_permalink();?>" class="courseImgOver"></a>
</div>
<? }else {  ?>
<div class="CourseSoon">Coming Soon</div>
</div>

<?php } ?>

<h4>
<?php if( get_field('available') ){ ?>
<a href="<?php the_permalink();?>"><?php the_title();?></a>
<?php }else{ ?>
<?php the_title();?>
<?php } ?>
</h4>
<div class="filterDesc">
<?php the_field('description_short'); ?>
</div>
<!-- <a class="BTN" href="<?php the_permalink();?>">View Resource</a>-->
</div>
<?php endwhile; ?>	 
</div>
</main>
</div>
</div>
</section>
<?php get_footer("members"); ?>