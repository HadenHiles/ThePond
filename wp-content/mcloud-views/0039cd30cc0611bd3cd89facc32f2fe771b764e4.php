<?php /** @var \ILAB\MediaCloud\Wizard\Config\Field $field */?>
<div class="form-field">
    <label for="<?php echo e($field->name()); ?>"><?php echo e($field->title()); ?></label>
    <input type="file" name="<?php echo e($field->name()); ?>" id="<?php echo e($field->name()); ?>" <?php echo e($field->required() ? 'required' : ''); ?>>
</div>
