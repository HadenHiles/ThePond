<?php /** @var \ILAB\MediaCloud\Wizard\Config\Step $step  */?>
<?php /** @var int $stepIndex  */?>
<div data-id="<?php echo e($step->id()); ?>" data-type="<?php echo e($step->type()); ?>" class="wizard-step wizard-step-<?php echo e($step->type()); ?> <?php echo e(($stepIndex == 0) ? 'current': ''); ?> <?php echo e($step->stepClass()); ?>" <?php if(!empty($step->next())): ?> data-next="<?php echo e($step->next()); ?>" <?php endif; ?>  <?php if(!empty($step->returnLink())): ?> data-return="<?php echo e($step->returnLink() ? 'true' : 'false'); ?>" <?php endif; ?>>
    <div class="step-contents">
        <?php if(!empty($step->introView())): ?>
            <div class="intro">
                <?php echo $__env->make($step->introView(), array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
            </div>
        <?php endif; ?>

        <div class="contents">
            <form>
                <?php $__currentLoopData = $step->fields(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('wizard.steps.form-fields.'.$field->type(), ['field' => $field], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </form>
        </div>
    </div>
    <div class="progress">
        <h3>Please wait ...</h3>
        <div class="logo-spinner">
            <img src="<?php echo e(ILAB_PUB_IMG_URL); ?>/icon-cloud.svg">
        </div>
    </div>
</div>