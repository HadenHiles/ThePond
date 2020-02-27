function mcloudHeartbeat() {
    jQuery.post(ajaxurl, { 'action': 'mcloud_task_heartbeat'});
    setTimeout(mcloudHeartbeat, <?php echo e($heartbeatFrequency); ?>);
}

document.addEventListener('DOMContentLoaded', function(){
    mcloudHeartbeat();
});