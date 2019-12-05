<?php ?>

<div class="row">
<div class="salesSectHeader centered">

<?php if( get_field('pricing_title') ): ?>
<h2><?php the_field('pricing_title'); ?></h2>
<?php endif; ?>
	
<?php if( get_field('pricing_sub_head') ): ?>
<p><?php the_field('pricing_sub_head'); ?></p>
<?php endif; ?>


</div>



<?php if( have_rows('pricing_boxes') ): ?>
<ul class="PriceBoxWrap">
<?php while( have_rows('pricing_boxes') ): the_row();
// vars
$priceheader = get_sub_field('pricing_title');
$price = get_sub_field('price');
$pricesub = get_sub_field('sub_price');
$pricecontent = get_sub_field('pricing_content');
$featureitem = get_sub_field('feature_item');
$buttonurl = get_sub_field('button_url');
$buttontext = get_sub_field('button_text');

?>
<li class="large-6 medium-6 columns">

<div class="memberPriceBox">

<h4><?php echo $priceheader; ?></h4>
<h2><?php echo $price; ?></h2>
<h6><?php echo $priceheader; ?></h6>
<p><?php echo $pricecontent; ?></p>

<!--//Sub reapeator features -->
<?php if( have_rows('pricing_features') ): ?>
<ul class="">
<?php while( have_rows('pricing_features') ): the_row();
// vars
$feature = get_sub_field('feature_item');
?>
<li class="large-12 columns">
<?php echo $feature; ?>
</li>
<?php endwhile; ?>
</ul>
<?php endif; ?>
<a class="BTN" href="<?php echo $buttonurl; ?>"><?php echo $buttontext; ?></a>
</div>
</li>
<?php endwhile; ?>
</ul>
<?php endif; ?>


	<?php if( get_field('pricing_extra_details') ): ?>
<div class="pricingExtra"><?php the_field('pricing_extra_details'); ?></div>
<?php endif; ?>


</div>