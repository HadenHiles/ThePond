<?php /** @var \ILAB\MediaCloud\Wizard\Config\Step $step  */?>
<?php /** @var int $stepIndex  */?>
<div data-id="<?php echo e($step->id()); ?>" data-type="<?php echo e($step->type()); ?>" class="wizard-step wizard-step-<?php echo e($step->type()); ?> <?php echo e(($stepIndex == 0) ? 'current': ''); ?> <?php echo e($step->stepClass()); ?>" <?php if(!empty($step->next())): ?> data-next="<?php echo e($step->next()); ?>" <?php endif; ?>  <?php if(!empty($step->returnLink())): ?> data-return="<?php echo e($step->returnLink() ? 'true' : 'false'); ?>" <?php endif; ?>>
    <div class="step-contents">
        <div class="video">
            <?php echo str_replace('feature=oembed', 'feature=oembed&enablejsapi=1', wp_oembed_get($step->videoUrl())); ?>

        </div>
    </div>
</div>