<?php 
global $smof_data;
get_header('members');
?>
</header>
 
<section class="memberDashWelc" style="padding-bottom: 0;">
	<div class="row">
		<div class="large-12 columns centered">
			<h1 class="page-title">
<?php _e( 'Search results for:', 'meltdefault' ); ?>
 <span class="searchResult"><?php echo get_search_query(); ?></span>
</h1>
		
			<div class="searchResults">
		<form role="search" method="get" class="search-form" action="<?php echo site_url();?>">
				    <input type="search" class="search-field" placeholder="Search..." value="<?php echo get_search_query(); ?>" name="s" title="Search">
				    <input type="submit" class="search-submit" value="Search">
				</form>
			</div>
			</div>
	</div>
</section> 
<!-- section-->
<section class="searchContent" style="padding: 1rem 0;">
<div class="row">

	<?php if ( have_posts() ) : ?>
		<div class="large-12 challenges-wrapper">
			<div class="bootstrap-styles challenges">
                <?php 
                while(have_posts()):the_post();
                    $terms = get_the_terms( get_the_ID(), 'skill-type' );
                    $post_thumbnail_id = get_post_thumbnail_id();
                    $img =  wp_get_attachment_image_url( $post_thumbnail_id , 'full');
                    if( empty($img) )
                        $img =  DEFAULT_IMG;
                    ?>
                    <a href="<?php the_permalink(); ?>" class="card shadow challenge">
                        <div class="card-img-top" style="background-image: url('<? echo $img; ?>'); overflow: hidden; position: relative;">
                            <?php 
                            foreach ($terms as $term) {
                                ?> 
                                <span class="postLabel" style="float: right; margin: 10px 10px 0 0;"><?=$term->name;?></span>
                                <?php 
                            }
                            ?>
                            <span class="postLabel" style="float: right; margin: 10px 10px 0 0;">
                                <?php
                                $obj = get_post_type_object(get_post_type());
                                if ($obj->name != 'content-library') {
                                    echo $obj->labels->singular_name;
                                } else {
                                    $libraryCategories = get_the_terms( get_the_ID(), 'library_category' );
                                    $libraryCatSlugs = [];
                                    foreach ($libraryCategories as $libraryCategory) {
                                        $libraryCatSlugs[] = $libraryCategory->slug;
                                    }
                                    
                                    if (!in_array('challenges', $libraryCatSlugs)) {
                                        echo $obj->labels->singular_name;
                                    } else {
                                        echo 'Challenge';
                                    }
                                }
                                ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h4><? the_title(); ?></h4>
                            <p class="filterDesc card-text"><?php the_excerpt(); ?></p>
                        </div>
                    </a>
                <?php 
                endwhile;
                ?>
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
           
    </div>
	<?php else : ?>
		<p class="searchNothingFound"><?php _e( 'Sorry, but nothing matched your search terms. Please try again with a different keyword or phrase.', 'meltdefault' ); ?></p>
	<?php endif;?>
</div>
</section>

<?php get_footer('members'); ?>