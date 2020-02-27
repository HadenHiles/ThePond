<?php /** @var \ILAB\MediaCloud\Wizard\Config\Config $config  */?>
<div class="wizard-container wizard-invisible">
    <div class="wizard-modal <?php if(!$config->initialSectionHasSteps()): ?>no-steps <?php endif; ?>" data-initial-section="<?php echo e($config->initialSectionName()); ?>" data-admin-exit="<?php echo e(admin_url('admin.php?page=media-cloud')); ?>" data-admin-template="<?php echo e(admin_url('admin.php?page=media-cloud-wizard&wizard=')); ?>">
        <div class="steps-background"></div>
        <div class="wizard-content">
            <div class="sections" tabindex="-1">
                <?php $__currentLoopData = $config->sections(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('wizard.section', ['section' => $section, 'initial' => ($section->id() == $config->initialSectionName())], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="steps">
                <ul></ul>
            </div>
            <footer>
                <img class="logo" src="<?php echo e(ILAB_PUB_IMG_URL); ?>/icon-cloud-w-type.svg">
                <nav>
                    <a class="previous invisible" href="#">Go Back</a>
                    <a class="next" href="#">Next</a>
                    <a class="return hidden" href="#">Return</a>
                </nav>
            </footer>
        </div>
        <a href="#" class="close-modal">Close</a>
    </div>

    

</div>

<script type="text/html" id="tmpl-step-template">
    <li>
        <div class="step-number">
            <span>{{ data.index }}</span>
            <span class="back"><img src="<?php echo e(ILAB_PUB_IMG_URL); ?>/wizard-check.svg"></span>
        </div>
        <input id="{{ data.id }}-checkbox" type="checkbox">
        <div class="description">
            <# if (data.title != null) { #>
            <h3>{{  data.title }}</h3>
            <# } #>
            <# if (data.description != null) { #>
            <div class="description-container">
                <p>{{ data.description }}</p>
            </div>
            <# } #>
        </div>
    </li>
</script>