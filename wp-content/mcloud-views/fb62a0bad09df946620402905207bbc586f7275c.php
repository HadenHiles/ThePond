<?php /** @var \ILAB\MediaCloud\Wizard\Config\Step $step  */?>
<?php /** @var int $stepIndex  */?>
<div data-id="<?php echo e($step->id()); ?>" data-type="<?php echo e($step->type()); ?>" class="wizard-step wizard-step-<?php echo e($step->type()); ?> <?php echo e(($stepIndex == 0) ? 'current': ''); ?> <?php echo e($step->stepClass()); ?>" <?php if(!empty($step->next())): ?> data-next="<?php echo e($step->next()); ?>" <?php endif; ?>  <?php if(!empty($step->returnLink())): ?>  data-return="<?php echo e($step->returnLink() ? 'true' : 'false'); ?>" <?php endif; ?>>
    <div class="step-contents">
        <?php if(!empty($step->introView())): ?>
            <div class="intro">
                <?php echo $__env->make($step->introView(), array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
            </div>
        <?php endif; ?>

        <?php if(empty($step->autoStart())): ?>
            <div class="start-buttons">
                <a href="#">Start Tests</a>
            </div>
        <?php endif; ?>

        <ul class="tests">
        </ul>

        <script id="<?php echo e($step->id()); ?>-tests" type="application/json">
            <?php echo $step->testsJson(); ?>

        </script>

        <script type="text/html" id="tmpl-test-item-template">
            <li class="hidden">
                <div class="icon">
                    <img class="waiting" src="<?php echo e(ILAB_PUB_IMG_URL); ?>/wizard-spinner.svg">
                    <img class="success" src="<?php echo e(ILAB_PUB_IMG_URL); ?>/wizard-icon-success.svg" width="32" height="32">
                    <img class="error" src="<?php echo e(ILAB_PUB_IMG_URL); ?>/wizard-icon-error.svg" width="32" height="32">
                    <img class="warning" src="<?php echo e(ILAB_PUB_IMG_URL); ?>/wizard-icon-warning.svg" width="32" height="32">
                </div>
                <div class="description">
                    <h3>{{ data.title }}</h3>
                    <p>{{ data.description  }}</p>
                    <# if (data.hasOwnProperty('errors')) { #>
                    <ul class="errors">
                        <# _.each(data.errors, function(error) { #>
                        <li>{{{ error  }}}</li>
                        <# }); #>
                    </ul>
                    <# } #>
                </div>
            </li>
        </script>
    </div>
</div>
