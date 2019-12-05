<?php

if( is_user_logged_in() ) {
get_header('members');
}else {get_header();}

?>
</header>
<section class="blogHeader">
<div class="row">
<div class="large-12 medium-12 columns">
<h1><?php echo single_cat_title();?></h1>
</div>
</div>
</section>

<section class="MainContent blogpage">
<div class="row">
<div class="categoryWrapper">
<?php 

if(have_posts()):while(have_posts()):the_post();

$terms = get_the_terms( get_the_ID(), 'category' ); 
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, ''); 
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);

if( empty($img[0]))
$img[0] = DEFAULT_IMG;

?>

<div class="blogpreview">

<a href="<?php the_permalink();?>" class="blogprevimage" style="background: url('<?php echo $img[0]; ?>')no-repeat;background-size:cover;background-position:center;"></a>

<div class="blogprevtext">
<h5><a href="<?php the_permalink();?>" alt="Article - <?php the_title();?>"><?php the_title();?></a></h5>
<div class="filterDesc">
<?php the_excerpt()?>
</div>
<a href="<?php the_permalink();?>" alt="Article - <?php the_title();?>" class="BTN">Read More</a>
</div> 
</div>

<?php  endwhile; ?>
<nav class="navigation paging-navigation" role="navigation">
<div class="nav-links">
<?php if ( get_next_posts_link() ) : ?>
<div class="nav-previous">
<?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'battle' ) ); ?>
</div>
<?php endif; ?>
<?php if ( get_previous_posts_link() ) : ?>
<div class="nav-next">
<?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'battle' ) ); ?>
</div>
<?php endif; ?>
</div>
<!-- .nav-links -->
</nav>
<?php 

endif;



?>
</div>

</div>
</div>
</section>
<?php

get_footer();

?>
