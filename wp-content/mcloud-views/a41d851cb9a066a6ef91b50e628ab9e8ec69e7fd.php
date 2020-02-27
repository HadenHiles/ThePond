<?php /** @var \ILAB\MediaCloud\Wizard\Config\Section $section  */?>
<?php /** @var bool $initial  */?>
<div data-id="<?php echo e($section->id()); ?>" class="wizard-section <?php echo e($initial ? 'current' : ''); ?> <?php echo e($section->sectionClass()); ?>" data-initial="<?php echo e($initial ? 'true' : 'false'); ?>" data-display-steps="<?php echo e($section->displaySteps() ? 'true' : 'false'); ?>" tabindex="-1">
    <?php $__currentLoopData = $section->steps(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $__env->make('wizard.steps.'.$step->type(), ['step' => $step, 'stepIndex' => $loop->index], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if($section->displaySteps()): ?>
    <script id="<?php echo e($section->id().'-steps'); ?>" type="application/json">
        <?php echo $section->stepJson(); ?>

    </script>
    <?php endif; ?>
</div>