<?php
add_shortcode('skill_competency_slider', 'display_skill_competency_slider');

function display_skill_competency_slider($atts = [], $content = null, $tag = '') {
    $tmp_atts = array_change_key_case((array)$atts, CASE_LOWER);

    // override default attributes with user attributes
    extract(shortcode_atts(array(
        'skill_id' => 'skill_id',
        'user_id' => 'user_id'
    ), $tmp_atts));
?>
    <div class="card" id="skill-competency">
        <div class="card-body" style="padding: 20px;">
            <h3 style="float: left;">Your Rating</h3>
            <div id="current-competency-color"></div>
            <br />
            <br />
            <div class="scale-indicators">
                <span class="lowest">Working on it</span>
                <span>Can do it in a game</span>
                <span class="highest">Game Changer</span>
            </div>
            <div id="competency-slider" data-skill-id="<?= esc_attr($skill_id) ?>" data-user-id="<?= esc_attr($user_id) ?>"></div>
        </div>
    </div>
    <?php
}

add_shortcode('skill_rating_box', 'skill_rating_box');

function skill_rating_box($atts = [], $content = null, $tag = '') {
    $tmp_atts = array_change_key_case((array)$atts, CASE_LOWER);

    // override default attributes with user attributes
    extract(shortcode_atts(array(
        'skill_id' => 'skill_id'
    ), $tmp_atts));

    $result = apply_filters('get_skill_rating', $skill_id);
    $result = $result->data;
    if (!empty($result) && (!empty($result->rgb) && !empty($result->percentage))) {
        if ($result->percentage >= 99) {
            ?>
            <td data-order="<?=$result->percentage?>">
                <div style="width: 20px; height: 20px; float: right;">
                    <img src="/wp-content/plugins/skill-competency-slider/images/hth_logo.png" style="width: 100%;" />
                </div>
            </td>            
            <?php
        } else {
            ?>
            <td data-order="<?=$result->percentage?>">
                <div style="width: 20px; height: 20px; float: right; background-color: <?=$result->rgb ?>"></div>
            </td>            
            <?php
        }
    } else {
        ?>
        <td data-order="0">
            <div style="width: 20px; height: 20px; float: right; background-color: #eaeaea;"></div>
        </td>
        <?php
    }
}
?>