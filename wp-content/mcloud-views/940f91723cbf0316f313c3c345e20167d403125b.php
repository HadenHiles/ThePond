<?php /** @var \ILAB\MediaCloud\Wizard\Config\Field $field */?>
<div class="form-field">
    <label for="<?php echo e($field->name()); ?>"><?php echo e($field->title()); ?></label>
    <select name="<?php echo e($field->name()); ?>" id="<?php echo e($field->name()); ?>" value="<?php echo e($field->defaultValue()); ?>" required>
        <?php $__currentLoopData = $field->options(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($value); ?>" <?php echo e(($value == $field->defaultValue()) ? 'selected' : ''); ?>><?php echo e($name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>
