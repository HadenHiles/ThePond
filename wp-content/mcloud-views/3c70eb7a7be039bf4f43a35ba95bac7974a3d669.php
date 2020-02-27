<div class="ic-Super-toggle--on-off checkbox-w-description <?php echo e(($tool->envEnabled() && !$tool->enabled()) ? 'toggle-warning' : ''); ?>">
    <?php echo $__env->make('base/fields/enable-toggle-checkbox', ['name' => $name, 'tool' => $tool], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <div>
        <?php echo $__env->make('base/fields/enable-toggle-description', ['name' => $name, 'tool' => $tool, 'manager' => $manager], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    </div>

</div>

