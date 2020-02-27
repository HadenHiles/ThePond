<?php $__env->startSection('main'); ?>
    <?php if (is_multisite() && \ILAB\MediaCloud\Utilities\Environment::NetworkMode()): ?>
    <form action='edit.php?action=update_media_cloud_network_options' method='post' autocomplete="off">
    <?php else: ?>
    <form action='options.php' method='post' autocomplete="off">
    <?php endif; ?>
        <?php
        settings_fields( $group );
        ?>
        <?php if(is_multisite() && is_network_admin()): ?>
        <div class="ilab-settings-section ilab-settings-features">
            <table class="form-table">
                <tr>
                    <td class="toggle">
                        <div class="ic-Super-toggle--on-off">
                            <?php echo $__env->make('base/fields/checkbox', ['name' => 'mcloud-network-mode', 'value' => $networkMode, 'description' => '', 'conditions' => null], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                        </div>
                        <div class="title">
                            Network Mode
                        </div>
                    </td>
                    <td class="description">
                        <p>Turning this value on means that all sites in your network will share the same Media Cloud configuration.  Additionally, the individual sites will not be able to see or change this configuration.  The use of the plugin will be, for the most part, transparent to them.  They will still have access to certain batch tools and direct uploads.</p>
                    </td>
                </tr>
            </table>
        </div>
        <?php endif; ?>
        <div class="ilab-settings-section ilab-settings-features">
            <table class="form-table">
                <?php /** @var $tool \ILAB\MediaCloud\Tools\Tool */ ?>
                <?php $__currentLoopData = $tools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $tool): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(!empty($tool->toolInfo['exclude'])): ?>
                        <?php continue; ?>
                    <?php endif; ?>
                <tr>
                    <td class="toggle">
                        <div class="ic-Super-toggle--on-off <?php echo e(($tool->envEnabled() && !$tool->enabled()) ? 'toggle-warning' : ''); ?>">
                            <?php echo $__env->make('base/fields/enable-toggle-checkbox', ['name' => $key, 'tool' => $tool], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                        </div>
                        <div class="title">
                            <?php echo e($tool->toolInfo['name']); ?>

                            <div class="tool-links">
                                <?php if($tool->hasWizard()): ?>
                                <a href="<?php echo e($tool->wizardLink()); ?>">Setup Wizard</a>
                                <?php endif; ?>
                                <?php if($tool->hasSettings()): ?>
                                <a href="<?php echo e(ilab_admin_url("admin.php?page=media-cloud-settings&tab=$key")); ?>">Settings</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="description">
                        <?php echo $__env->make('base/fields/enable-toggle-description', ['name' => $key, 'tool' => $tool, 'manager' => $manager], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </table>
        </div>
        <div class="ilab-settings-button">
            <?php submit_button(); ?>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('../templates/sub-page', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>