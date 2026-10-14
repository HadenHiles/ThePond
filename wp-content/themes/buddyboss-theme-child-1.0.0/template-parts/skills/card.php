<?php
// One Skills card, shared by /skills/ and the dashboard Skills Vault; expects $skillId and $isMember.
$skillCard = pond_theme_skill_card($skillId);
extract($skillCard);
$img = wp_get_attachment_image_url(get_post_thumbnail_id($skillId), 'full');
if (empty($img)) $img = pond_theme_vimeo_thumbnail((string) $videoCode);
if (empty($img)) $img = '/wp-content/themes/meltingpot-child/images/placeholder.png';
$description = (string) get_the_excerpt($skillId);
$termsString = '';
foreach ($skillTypes as $skillType) $termsString .= $skillType->term_id . ',';
?>
<article class="card shadow challenge filter pond-skill-card-item" data-category="<?php echo esc_attr($termsString); ?>" data-title="<?php echo esc_attr(strtolower($name)); ?>" data-frequency="<?php echo esc_attr($puckLevel); ?>" data-rating="<?php echo esc_attr($ratingPercent); ?>" data-search="<?php echo esc_attr($name . ' ' . $skillTypeString . ' ' . $performanceLevelString . ' ' . wp_strip_all_tags($description)); ?>">
	<div class="card-img-top" style="background-image: url('<?php echo esc_url($img); ?>');">
		<?php if (is_user_logged_in()) echo pond_theme_skill_rating_badge($skillCard); ?>
		<span class="pond-skill-card__types">
			<?php foreach ($skillTypes as $skillType) : ?><span class="postLabel"><?php echo esc_html($skillType->name); ?></span><?php endforeach; ?>
		</span>
	</div>
	<div class="card-body">
		<h4><a class="pond-skill-card__link" href="<?php echo esc_url($url); ?>"><?php echo esc_html($name); ?></a></h4>
		<dl class="pond-skill-card__facts">
			<div>
				<dt><?php esc_html_e('Frequency', 'buddyboss-theme-child'); ?></dt>
				<dd class="pond-skill__frequency" title="<?php echo esc_attr(sprintf('Frequency: %d of 5', $puckLevel)); ?>"><?php echo pond_theme_skill_pucks($puckLevel); ?></dd>
			</div>
			<?php if ($performanceLevelString !== '') : ?>
			<div>
				<dt><?php esc_html_e('Level', 'buddyboss-theme-child'); ?></dt>
				<dd><span class="pond-skill__type"><?php echo esc_html($performanceLevelString); ?></span></dd>
			</div>
			<?php endif; ?>
			<?php if (is_user_logged_in()) : ?>
			<div>
				<dt><?php esc_html_e('Your rating', 'buddyboss-theme-child'); ?></dt>
				<dd><?php echo esc_html($ratingLabel); ?></dd>
			</div>
			<?php endif; ?>
		</dl>
		<?php if ($description !== '') : ?><p class="filterDesc card-text"><?php echo wp_kses_post($description); ?></p><?php endif; ?>
		<?php if ($isMember) : ?>
		<div class="pond-skill__actions"><?php include get_stylesheet_directory() . '/template-parts/skills/vault-actions.php'; ?></div>
		<?php endif; ?>
	</div>
</article>
