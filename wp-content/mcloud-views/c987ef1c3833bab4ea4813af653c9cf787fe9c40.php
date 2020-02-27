<?php
if (empty($value)) {
	$value = $default;
}
?>
<div id="setting-<?php echo e($name); ?>" <?php echo e((($conditions) ? 'data-conditions="true"' : '')); ?>>
	<select id="<?php echo e($name); ?>" name='<?php echo e($name); ?>'>
	<?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $optionName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		<option value='<?php echo e($val); ?>' <?php echo e((($val == $value) ? 'selected' : '')); ?>><?php echo e($optionName); ?></option>
	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
	</select>
	<?php if($description): ?>
	<p class='description'><?php echo $description; ?></p>
	<?php endif; ?>
    <?php if($conditions): ?>
    <script id="<?php echo e($name); ?>-conditions" type="text/plain">
        <?php echo json_encode($conditions, JSON_PRETTY_PRINT); ?>

    </script>
    <?php endif; ?>
</div>
