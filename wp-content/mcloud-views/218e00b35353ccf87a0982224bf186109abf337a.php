<div id="setting-<?php echo e($name); ?>" <?php echo e(((!empty($conditions)) ? 'data-conditions="true"' : '')); ?>>
<?php echo $__env->make('base/ui/checkbox', ['name' => $name, 'value' => $value, 'description' => '', 'enabled' => true], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php if($description): ?>
<p class='description'><?php echo $description; ?></p>
<?php endif; ?>
<?php if($conditions): ?>
<script id="<?php echo e($name); ?>-conditions" type="text/plain">
    <?php echo json_encode($conditions, JSON_PRETTY_PRINT); ?>

</script>
<?php endif; ?>
</div>
