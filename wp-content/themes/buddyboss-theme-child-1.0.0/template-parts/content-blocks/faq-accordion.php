<?php
/**
 * Renders a landing-style FAQ accordion.
 *
 * Expects $args['items'] as a list of arrays with 'id', 'question', and 'answer' (HTML).
 * IDs must be unique on the page; answers are filtered with wp_kses_post().
 */
$items = isset($args['items']) && is_array($args['items']) ? $args['items'] : array();
if (!$items) {
    return;
}
?>
<div class="c-accordion-list">
    <?php foreach ($items as $item) : ?>
        <?php
        $panel_id = sanitize_html_class($item['id']);
        ?>
        <div class="c-accordion-item">
            <div class="_w-accordion-item">
                <button type="button" id="<?php echo esc_attr($panel_id); ?>-question" class="c-accordion-item-q" aria-expanded="false" aria-controls="<?php echo esc_attr($panel_id); ?>">
                    <div class="_w-accordion-item-q">
                        <div class="c-accordion-item-q-text">
                            <div class="_w-accordion-item-q-text">
                                <div class="accordion-item-q-text"><?php echo esc_html($item['question']); ?></div>
                            </div>
                        </div>
                        <div class="c-accordion-item-q-icon">
                            <div class="_w-accordion-item-q-icon">
                                <div class="accordion-item-q-icon-stripe-1"></div>
                                <div class="accordion-item-q-icon-stripe-2"></div>
                            </div>
                        </div>
                    </div>
                </button>
                <div id="<?php echo esc_attr($panel_id); ?>" class="c-accordion-item-a" role="region" aria-labelledby="<?php echo esc_attr($panel_id); ?>-question" style="height: 0;">
                    <div class="_w-accordion-content-divider">
                        <div class="accordion-content-divider"></div>
                    </div>
                    <div class="_w-accordion-item-a">
                        <p class="accordion-item-a-text"><?php echo wp_kses_post($item['answer']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
