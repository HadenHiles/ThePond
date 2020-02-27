<?php /** @var \ILAB\MediaCloud\Wizard\Config\Field $field */?>
<div class="form-field">
    <label for="<?php echo e($field->name()); ?>"><?php echo e($field->title()); ?></label>
    <div style="display:none">
        <input type="password" tabindex="-1">
    </div>
    <input type="password" placeholder="<?php echo e($field->title()); ?>" name="<?php echo e($field->name()); ?>" id="<?php echo e($field->name()); ?>" value="<?php echo e($field->defaultValue()); ?>"  <?php echo e($field->required() ? 'required' : ''); ?>>
</div>
