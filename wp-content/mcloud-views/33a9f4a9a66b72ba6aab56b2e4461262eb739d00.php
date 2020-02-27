<div class="settings-container">
    <header class="all-settings">
        <div class="contents">
            <img class="logo" src="<?php echo e(ILAB_PUB_IMG_URL); ?>/icon-cloud-w-type.svg">
            <div class="settings-select-container">
                <nav class="dropdown">
                    <div>Settings:</div>
                    <div class="dropdown">
                        <div class="current">
                            <?php if($tool->enabled()): ?>
                                <span class="tool-indicator tool-active"></span>
                            <?php elseif($tool->envEnabled()): ?>
                                <span class="tool-indicator tool-env-active"></span>
                            <?php else: ?>
                                <span class="tool-indicator tool-inactive"></span>
                            <?php endif; ?>
                            <?php echo e($tool->toolInfo['name']); ?>

                        </div>
                        <div class="items">
                            <ul>
                                <?php $__currentLoopData = $tools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $atool): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(!empty($atool->toolInfo['settings'])): ?>
                                        <li class="<?php echo e(($tab == $key) ? 'active' : ''); ?>">
                                            <a class="tool" href="<?php echo e(ilab_admin_url('admin.php?page=media-cloud-settings&tab='.$key)); ?>">
                                                <?php if($atool->enabled()): ?>
                                                    <span class="tool-indicator tool-active"></span>
                                                <?php elseif($atool->envEnabled()): ?>
                                                    <span class="tool-indicator tool-env-active"></span>
                                                <?php else: ?>
                                                    <span class="tool-indicator tool-inactive"></span>
                                                <?php endif; ?>
                                                <?php echo e($atool->toolInfo['name']); ?>

                                            </a>
                                            <a title="Pin these settings to the admin menu." data-tool-name="<?php echo e($atool->toolName); ?>" data-tool-title="<?php echo e($atool->toolInfo['name']); ?>" class="tool-pin <?php echo e(($atool->pinned()) ? 'pinned' : ''); ?>" href="#"></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <div class="mcloud-settings-tabs">
            <div class="navwrap">
                <ul>
                    <?php $__currentLoopData = $tools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $atool): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(!empty($atool->toolInfo['settings'])): ?>
                            <li class="<?php echo e(($tab == $key) ? 'active' : ''); ?>">
                                <a class="tool" href="<?php echo e(ilab_admin_url('admin.php?page=media-cloud-settings&tab='.$key)); ?>">
                                    <?php if(!$atool->alwaysEnabled()): ?>
                                        <?php if($atool->enabled()): ?>
                                            <span class="tool-indicator tool-active"></span>
                                        <?php elseif($atool->envEnabled()): ?>
                                            <span class="tool-indicator tool-env-active"></span>
                                        <?php else: ?>
                                            <span class="tool-indicator tool-inactive"></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php echo e($atool->toolInfo['name']); ?>

                                </a>
                                <a title="Pin these settings to the admin menu." data-tool-name="<?php echo e($atool->toolName); ?>" data-tool-title="<?php echo e($atool->toolInfo['name']); ?>" class="tool-pin <?php echo e(($atool->pinned()) ? 'pinned' : ''); ?>" href="#"></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <a class="tabs-nav tabs-prev hidden" href="#"><span>LEFT</span></a>
            <a class="tabs-nav tabs-next hidden" href="#"><span>RIGHT</span></a>
        </div>
    </header>
    <div class="settings-body <?php if (\ILAB\MediaCloud\Utilities\LicensingManager::ActivePlan('free')): ?> show-upgrade <?php endif; ?>">
        <div class="settings-interior">
            <div class="ilab-notification-container"></div>
            <?php if (is_multisite() && \ILAB\MediaCloud\Utilities\Environment::NetworkMode()): ?>
            <form action='edit.php?action=update_media_cloud_network_options' method='post' autocomplete="off">
            <?php else: ?>
            <form action='options.php' method='post' autocomplete="off">
            <?php endif; ?>
                <?php
                settings_fields( $group );
                ?>
                <?php if(empty($tool->toolInfo['exclude'])): ?>
                <div class="ilab-settings-section ilab-settings-toggle">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable <?php echo e($tool->toolInfo['name']); ?></th>
                            <td>
                                <?php echo $__env->make('base/fields/enable-toggle', ['name' => $tab, 'manager' => $manager, 'tool' => $tool], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                            </td>
                        </tr>
                        <?php if(!empty($tool->toolInfo['related'])): ?>
                        <?php $__currentLoopData = $tool->toolInfo['related']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(empty($manager->tools[$relatedKey])): ?>
                                <?php continue; ?>
                            <?php endif; ?>
                            <?php if($loop->first): ?>
                            <tr>
                                <td colspan="2" style="width:100%; padding: 0;"><hr></td>
                            </tr>
                            <?php endif; ?>
                            <?php $relatedTool = $manager->tools[$relatedKey]; ?>
                            <tr>
                                <th scope="row">Enable <?php echo e($relatedTool->toolInfo['name']); ?></th>
                                <td>
                                    <?php echo $__env->make('base/fields/enable-toggle', ['name' => $relatedTool->toolInfo['id'], 'manager' => $manager, 'tool' => $relatedTool], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </table>
                </div>
                <?php endif; ?>
                <?php if((count($sections) > 1) && !empty($jump_links)): ?>
                <div class="section-jumps">
                    <span class="label">Quick Jump</span>
                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($loop->first) continue; ?>
                        <a href="#<?php echo e(sanitize_title($section['title'])); ?>"><?php echo e($section['title']); ?></a>
                        <?php if(!$loop->last): ?>
                        <span class="sep">|</span>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div id="<?php echo e(sanitize_title($section['title'])); ?>" class="ilab-settings-section">
                    <?php if(!empty($section['title'])): ?>
                    <h2>
                        <?php echo e($section['title']); ?>

                        <?php if(!empty($section['doc_link'])): ?>
                            <a href="<?php echo e($section['doc_link']); ?>" class="help-beacon" data-article-inline="<?php echo e($section['doc_link']); ?>">
                                Help
                            </a>
                        <?php endif; ?>
                        <?php if(!empty($section['help']) && !empty($section['help']['data']) && (\ILAB\MediaCloud\Utilities\arrayPath($section['help'], 'target', 'footer') == 'header')): ?>
                            <div class="ilab-section-title-doc-links">
                                <?php echo $__env->make('base.fields.help', $section['help'], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                            </div>
                        <?php endif; ?>
                    </h2>
                    <?php endif; ?>
                    <?php if(!empty($section['description'])): ?>
                    <div class="section-description"><?php echo $section['description']; ?></div>
                    <?php endif; ?>
                    <table class="form-table">
                        <?php do_settings_fields( $page, $section['id'] ) ?>
                    </table>
                    <?php if(!empty($section['help']) && !empty($section['help']['data']) && (\ILAB\MediaCloud\Utilities\arrayPath($section['help'], 'target', 'footer') == 'footer')): ?>
                        <div class="ilab-section-doc-links">
                            <?php echo $__env->make('base.fields.help', $section['help'], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                        </div>
                    <?php endif; ?>
                    <?php if((empty($section['hide-save']) && (count($sections) > 1))): ?>
                        <div class="section-submit">
	                        <?php submit_button('', 'primary small'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if((count($sections) <= 1) || (count($tool->actions()) > 0)): ?>
                <div class="ilab-settings-button">
                    <?php if(!empty($tool->actions())): ?>
                        <div class="ilab-settings-batch-tools <?php echo e((count($sections) <= 1) ? 'has-submit' : ''); ?>">
                            <?php $__currentLoopData = $tool->actions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a class="button ilab-ajax-button" data-ajax-action="<?php echo e(str_replace('-','_',$key)); ?>" data-ajax-nonce="<?php echo e(wp_create_nonce(str_replace('-','_',$key))); ?>" href="#"><?php echo e($action['name']); ?></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                    <?php if(count($sections) <= 1): ?>
                    <?php submit_button(); ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </form>
        </div>
        <?php if (\ILAB\MediaCloud\Utilities\LicensingManager::ActivePlan('free')): ?>
        <?php echo $__env->make('base/upgrade', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
        <?php endif; ?>
    </div>


    <?php echo $__env->make('support.beacon', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
</div>
<script>
    (function($){
        $('[data-conditions]').each(function(){
            var parent = this.parentElement;
            while (parent.tagName.toLowerCase() != 'tr') {
                parent = parent.parentElement;
                if (!parent) {
                    return;
                }
            }
            var name = this.getAttribute('id').replace('setting-','');
            var conditions = JSON.parse($('#'+name+'-conditions').html());

            var conditionTest = function() {
                var match = false;
                Object.getOwnPropertyNames(conditions).forEach(function(prop){
                    var val = $('#'+prop).val();

                    var trueCount = 0;
                    conditions[prop].forEach(function(conditionVal){
                        if (conditionVal[0] == '!') {
                            conditionVal = conditionVal.substring(1);
                            if (val != conditionVal) {
                                trueCount++;
                            }
                        } else {
                            if (val == conditionVal) {
                                trueCount++;
                            }
                        }
                    });

                    if (trueCount>0) {
                        match = true;
                    } else {
                        match = false;
                    }
                });

                return match;
            };

            if (!conditionTest()) {
                parent.style.display = 'none';
            }

            Object.getOwnPropertyNames(conditions).forEach(function(prop){
                $('#'+prop).on('change', function(e){
                    if (!conditionTest()) {
                        parent.style.display = 'none';
                    } else {
                        parent.style.display = '';
                    }
                });
            });
        });

        $('#ilab-media-settings-nav').on('change', function(e){
           document.location = $(this).val();
        });

        $('a.ilab-ajax-button').on('click', function(e){
            e.preventDefault();

            const data={
                action: $(this).data('ajax-action'),
                nonce: $(this).data('ajax-nonce')
            };

            $.post(ajaxurl, data, function(response){
                if (response.hasOwnProperty('message')) {
                    alert(response.message);
                } else {
                    document.location.reload();
                }
            });

            return false;
        });

        $('nav.dropdown').each(function(){
            var dropdown = $(this);
            var current = dropdown.find('div.current');
            var items = dropdown.find('div.items');
            current.on('click', function(e) {
               e.preventDefault();
               dropdown.addClass('active');
               items.addClass('visible');
               items.on('mouseleave', function(){
                   items.removeClass('visible');
                   dropdown.removeClass('active');
               });
               return false;
           });
        });

        var currentLabels = [];
        var lastPinnedItems = [];
        var menu = $('#toplevel_page_media-cloud');
        var menuUL = menu.find('ul');
        var firstItem = menuUL.find('li.wp-first-item').next();
        var pinnedSeparator = null;

        firstItem.next().next().find('span.ilab-admin-separator-settings').each(function(){
            if (pinnedSeparator == null) {
                pinnedSeparator = firstItem.next().next();
            }
        });

        $('a.tool-pin').each(function(){
            var pin = $(this);
            var pinToolName = pin.data('tool-name');
            var pinToolTitle = pin.data('tool-title');
            var pinItem = null;

            menuUL.find('li').each(function(){
               var item = $(this);
               item.find('a').each(function(){
                   var label = $(this).text();
                   if (currentLabels.indexOf(label) == -1) {
                    currentLabels.push(label);
                   }

                   const regex = /\page\=media\-cloud\-settings\-pinned\-(.*)$/gm;
                   var m = regex.exec($(this).attr('href'));
                   if ((m != null) && (m.length > 1)) {
                       var tool = m[m.length - 1];
                       if (tool == pinToolName) {
                           pinItem = item;
                           lastPinnedItems.push(pinItem);
                       }
                   }
               });
            });

            pin.on('click', function(e) {
                e.preventDefault();

                console.log('pin');

                const data={
                    action: 'ilab_pin_tool',
                    tool: pinToolName
                };

                $.post(ajaxurl, data, function(response){
                    console.log(response);

                    if (response.status == 'error') {
                        console.log(response);
                        return;
                    }

                    var pinned = (response.status == 'pinned');
                    if (!pinned) {
                        if (lastPinnedItems.indexOf(pinItem) >= 0) {
                            lastPinnedItems.splice(lastPinnedItems.indexOf(pinItem), 1);
                        }

                        if (pinItem) {
                            pinItem.remove();
                            pinItem = null;
                        }

                        if (currentLabels.indexOf(pinToolTitle) != -1) {
                            console.log('removing');
                            currentLabels.splice(currentLabels.indexOf(pinToolTitle), 1);
                        }


                        pin.removeClass('pinned');
                    } else {
                        pin.addClass('pinned');

                        if (currentLabels.indexOf(pinToolTitle) != -1) {
                            console.log('exiting');
                            return;
                        }

                        pinItem = $('<li id="pinned-tool-'+pinToolName+'"><a href="admin.php?page=media-cloud-settings-pinned-'+pinToolName+'" aria-current="page">'+pinToolTitle+'</a></li>');

                        if (lastPinnedItems.length > 0) {
                            pinItem.insertAfter(lastPinnedItems[lastPinnedItems.length - 1]);
                        } else {
                            pinItem.insertAfter(pinnedSeparator);
                        }

                        lastPinnedItems.push(pinItem);
                    }
                });


                return false;
            });
        });
    })(jQuery);
</script>