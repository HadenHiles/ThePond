<?php /** @var \ILAB\MediaCloud\Wizard\Config\Field $field */?>
<div class="form-field">
    <label for="<?php echo e($field->name()); ?>"><?php echo e($field->title()); ?></label>
    <input type="text" name="<?php echo e($field->name()); ?>" placeholder="<?php echo e($field->title()); ?>" id="<?php echo e($field->name()); ?>" value="<?php echo e($field->defaultValue()); ?>" <?php echo e($field->required() ? 'required' : ''); ?>>
</div>
