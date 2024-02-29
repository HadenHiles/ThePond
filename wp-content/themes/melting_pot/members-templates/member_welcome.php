<?

/***  Template Name: Member Welcome */
get_header("members"); ?>
</header>

<section>
    <div class="row">
        <? if (have_posts()) : while (have_posts()) : the_post(); ?>

                <div class="large-8 large-push-2 columns">

                    <div class="welcomeWrapper">
                        <div class="welcomeHeader">
                            <h1>
                                <?php if (get_field('welcome_message')) : ?>
                                    <?php echo get_field('welcome_message'); ?>
                                <?php else : ?>
                                    Hi
                                <?php endif; ?>
                                <?php echo  do_shortcode('[mepr-account-info field="first_name"]'); ?></h1>
                            <p>
                                <?php if (get_field('welcome_submessage')) : ?>
                                    <?php echo get_field('welcome_submessage'); ?>
                                <?php else : ?>
                                    We're so excited to have you onboard - <strong>watch this short video below</strong>
                                <?php endif; ?>
                            </p>
                        </div>


                        <?php get_template_part('template-parts/courses/lesson-topic-fields'); ?>

                        <?php if (have_rows('resources')) : ?>
                            <div class="getStartedBox">
                                <h4><?php echo get_field('get_started_text'); ?></h4>
                                <ul>
                                    <?php while (have_rows('resources')) : the_row();
                                        // vars
                                        $title = get_sub_field('resource_name');
                                        $link = get_sub_field('resource_link');
                                        $icon = get_sub_field('resource_type');
                                    ?>
                                        <li>
                                            <a href="<?php echo $link ?>" target="_blank" class="BTN"><?php echo $title ?></a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <!--END Resources Embed -->
                    </div>
                </div>
    </div>
<? endwhile;
        endif; ?>
</div><!-- End Main Content -->
</section>
<? get_footer("members"); ?>