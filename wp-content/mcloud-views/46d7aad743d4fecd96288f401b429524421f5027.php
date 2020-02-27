<?php /** @var \ILAB\MediaCloud\Wizard\Config\Field $field */?>
<div class="form-field field-checkbox">
    <div class="checkbox">
        <?php echo $__env->make('base/fields/checkbox', ['name' => $field->name(), 'value' => $field->defaultValue(), 'description' => '', 'conditions' => null], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    </div>
    <div class="title">
        <?php echo e($field->title()); ?>

    </div>
    <div class="description">
        <?php echo $field->description(); ?>

    </div>
</div>
