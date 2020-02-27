<?php $classes = ($target == 'footer') ? 'button button-primary' : ''; ?>
<?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $helpLinks): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div id="doc-links-<?php echo e($id); ?>" class="doc-links-setting" <?php if(!$loop->first): ?> style="display:none" <?php endif; ?>>
        <?php $__currentLoopData = $helpLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $helpLink): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(isset($helpLink['video_url'])): ?>
            <a href="<?php echo e($helpLink['video_url']); ?>" target="_blank" class="<?php echo e($classes); ?> <?php echo e(\ILAB\MediaCloud\Utilities\arrayPath($helpLink, 'class', '')); ?> mediabox"><?php echo e($helpLink['title']); ?></a>
            <?php elseif(isset($helpLink['wizard'])): ?>
            <a href="<?php echo e(admin_url('admin.php?page=media-cloud-wizard&wizard='.$helpLink['wizard'])); ?>" class="<?php echo e($classes); ?> <?php echo e(\ILAB\MediaCloud\Utilities\arrayPath($helpLink, 'class', '')); ?>"><?php echo e($helpLink['title']); ?></a>
            <?php else: ?>
            <a href="<?php echo e($helpLink['url']); ?>" target="_blank" class="<?php echo e($classes); ?> <?php echo e(\ILAB\MediaCloud\Utilities\arrayPath($helpLink, 'class', '')); ?>" <?php if(!empty($helpLink['url'])): ?> data-article-sidebar="<?php echo e($helpLink['url']); ?>" <?php endif; ?>><?php echo e($helpLink['title']); ?></a>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php if(!empty($watch)): ?>
    <script>
        (function($){
            $('#<?php echo e($watch); ?>').on('change', function() {
                $('.doc-links-setting').css({display: 'none'});
                $('#doc-links-'+$(this).val()).css({display: ''});
            });

            $('#<?php echo e($watch); ?>').trigger('change');
        })(jQuery);
    </script>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
