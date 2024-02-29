<?php global $smof_data; ?>
<!doctype html>
<html class="no-js" lang="en">

<head>

  <?php if ($smof_data['googletagmanager']) {
    echo  $smof_data['googletagmanager'];
  } ?>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <?php wp_head(); ?>

  <?php if ($smof_data['head_script']) {
    echo  $smof_data['head_script'];
  } ?>


  <script>
    window.FontAwesomeConfig = {
      searchPseudoElements: true
    }
  </script>
</head>


<body class="vidFull fitvidcontiner">

  <?php if ($smof_data['googletagmanagernoscript']) {
    echo  $smof_data['googletagmanagernoscript'];
  } ?>

  <header class="header" style="background: <?php if (get_field('banner_background')) : ?>url('<?php echo get_field('banner_background'); ?>')no-repeat;background-size:cover;background-position:center;<?php else : ?>#ffffff<?php endif; ?>">