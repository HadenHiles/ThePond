<div id="setting-<?php echo e($name); ?>" <?php echo e((($conditions) ? 'data-conditions="true"' : '')); ?>>
	<input size='40' type='text' id="<?php echo e($name); ?>" name='<?php echo e($name); ?>' value='<?php echo e($value); ?>' placeholder='<?php echo e($placeholder); ?>'>
    <div class="upload-path-preview">
        <span>Preview</span>
        <span id="<?php echo e($name); ?>-preview"></span>
    </div>
	<?php if($description): ?>
	<p class='description'><?php echo $description; ?></p>
	<?php endif; ?>
    <?php if($conditions): ?>
    <script id="<?php echo e($name); ?>-conditions" type="text/plain">
        <?php echo json_encode($conditions, JSON_PRETTY_PRINT); ?>

    </script>
    <?php endif; ?>

    <script>
        (function($) {
            var uploadPathId = "#<?php echo e($name); ?>";
            var uploadPathNonce = "<?php echo e(wp_create_nonce('mcloud-preview-upload-path')); ?>";

            var updating = false;
            var needsUpdate = false;

            var updatePreview = function() {
                if (updating) {
                    needsUpdate = true;
                    return;
                }

                updating = true;

                $.post(ajaxurl, {
                    'action': 'mcloud_preview_upload_path',
                    'nonce': uploadPathNonce,
                    'prefix': $(uploadPathId).val() },
                    (response) => {
                        if (response.hasOwnProperty('path')) {
                            $(uploadPathId+'-preview').text(response.path);
                        }

                        updating = false;

                        if (needsUpdate) {
                            needsUpdate = false;
                            updatePreview();
                        }
                    })
                    .fail((response) => {
                        updating = false;

                        if (needsUpdate) {
                            needsUpdate = false;
                            updatePreview();
                        }
                    });
            };

            var updateTimeout = null;
            $(uploadPathId).on('keyup', function() {
                clearTimeout(updateTimeout);
                updateTimeout = setTimeout(() => {
                    updatePreview();
                }, 500);
            });

            updatePreview();
        })(jQuery);
    </script>
</div>
