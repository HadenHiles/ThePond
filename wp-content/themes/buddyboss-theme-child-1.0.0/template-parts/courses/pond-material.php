<?php
get_template_part('template-parts/courses/lesson-topic-fields');

foreach (array(
    'goals' => 'Goals',
    'tips_for_success' => 'Tips for Success',
    'common_mistakes' => 'Common Mistakes',
    'what_to_practice' => 'What To Practice',
) as $field => $heading) {
    if (!have_rows($field)) {
        continue;
    }
    echo '<h3>' . esc_html($heading) . '</h3><ol class="lesson-list">';
    while (have_rows($field)) {
        the_row();
        echo '<li>' . ($field === 'goals' ? get_sub_field('goal') : (get_sub_field('content') ?: get_sub_field('title'))) . '</li>';
    }
    echo '</ol>';
}

foreach (array(
    'prerequisite_skills' => 'Prerequisite Skills',
    'targeted_skills' => 'Targeted Skills',
    'skills' => 'Related Skills',
) as $field => $heading) {
    $skills = get_field($field);
    if (!$skills || !is_array($skills)) {
        continue;
    }
    echo '<h3>' . esc_html($heading) . '</h3><div class="pond-related-skills">';
    foreach ($skills as $skill) {
        $id = $skill instanceof WP_Post ? $skill->ID : absint($skill);
        echo '<a href="' . esc_url(get_permalink($id)) . '">' . esc_html(get_the_title($id)) . '</a>';
    }
    echo '</div>';
}
