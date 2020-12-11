<?php
add_shortcode('skill_competency_slider', 'display_skill_competency_slider');
function display_skill_competency_slider($atts = [], $content = null, $tag = '') {
  $tmp_atts = array_change_key_case((array)$atts, CASE_LOWER);

  // override default attributes with user attributes
  extract( shortcode_atts( array(
    'skill_id' => 'skill_id',
    'user_id' => 'user_id'
    ), $atts ) );
  ?>
    <div class="card" id="skill-competency">
        <div class="card-body" style="padding: 20px;">
            <h3 style="float: left;">Your Rating</h3>
            <div id="current-competency-color"></div>
            <br />
            <br />
            <div class="scale-indicators">
                <span class="lowest">Can't do it</span>
                <span class="highest">Amazing at it</span>
            </div>
            <div id="competency-slider" data-skill-id="<?=esc_attr($skill_id)?>" data-user-id="<?=esc_attr($user_id)?>"></div>
        </div>
    </div>
    <?php
}
?>