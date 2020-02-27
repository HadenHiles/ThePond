<?php /** @var \ILAB\MediaCloud\Wizard\Config\Step $step  */?>
<?php /** @var int $stepIndex  */?>
<div data-id="<?php echo e($step->id()); ?>" data-type="<?php echo e($step->type()); ?>" class="wizard-step wizard-step-<?php echo e($step->type()); ?> <?php echo e(($stepIndex == 0) ? 'current': ''); ?> <?php echo e($step->stepClass()); ?>" <?php if(!empty($step->next())): ?> data-next="<?php echo e($step->next()); ?>" <?php endif; ?>  <?php if(!empty($step->returnLink())): ?> data-return="<?php echo e($step->returnLink() ? 'true' : 'false'); ?>" <?php endif; ?>>
    <?php $__currentLoopData = $step->groups(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="step-contents step-group" data-id="<?php echo e($step->id()); ?>-group-<?php echo e($group->index()); ?>">
        <script type="application/json" id="<?php echo e($step->id()); ?>-group-<?php echo e($group->index()); ?>">
            <?php echo json_encode($group->conditions(), JSON_PRETTY_PRINT); ?>

        </script>
        <?php if(!empty($group->introView())): ?>
            <div class="intro">
                <?php echo $__env->make($group->introView(), array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
            </div>
        <?php endif; ?>

        <div class="contents">
            <ul class="options <?php echo e($group->groupClass()); ?>">
                <?php $__currentLoopData = $group->options(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <?php if(!empty($option->descriptionView())): ?>
                            <div class="description">
                                <?php echo $__env->make($option->descriptionView(), array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                                <div class="arrow-down"></div>
                            </div>
                        <?php endif; ?>
                        <?php if(!empty($option->link())): ?>
                        <a class="<?php echo e($option->optionClass()); ?>" href="<?php echo e($option->link()); ?>" <?php if(!empty($option->target())): ?>target="<?php echo e($option->target()); ?>" <?php endif; ?> tooltip="<?php echo e($option->title()); ?>">
                            <?php if(!empty($option->icon())): ?>
                                <img src="<?php echo e(ILAB_PUB_IMG_URL.'/'.$option->icon()); ?>">
                            <?php else: ?>
                                <?php echo e($option->title()); ?>

                            <?php endif; ?>
                        </a>
                        <?php else: ?>
                        <a class="<?php echo e($option->optionClass()); ?>" href="#" tooltip="<?php echo e($option->title()); ?>" data-next="<?php echo e($option->next()); ?>">
                            <?php if(!empty($option->icon())): ?>
                                <img src="<?php echo e(ILAB_PUB_IMG_URL.'/'.$option->icon()); ?>">
                            <?php else: ?>
                                <?php echo e($option->title()); ?>

                            <?php endif; ?>
                        </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
