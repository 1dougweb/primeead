<?php
    $trackingSettings = \App\Models\SystemSetting::getTrackingSettings();
    
    // Gerar parâmetros adicionais para o iframe do GTM
    $gtmParams = [];
    if (isset($pageTitle)) {
        $gtmParams[] = 'title=' . urlencode($pageTitle);
    }
    if (isset($pageType)) {
        $gtmParams[] = 'type=' . urlencode($pageType);
    }
    if (auth()->check()) {
        $gtmParams[] = 'logged_in=true';
        $gtmParams[] = 'user_type=' . urlencode(session('admin_tipo') ?? 'user');
    }
    
    $gtmQueryString = !empty($gtmParams) ? '&' . implode('&', $gtmParams) : '';
?>


<?php if($trackingSettings['enable_google_analytics'] && !empty($trackingSettings['google_tag_manager_id'])): ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo e($trackingSettings['google_tag_manager_id']); ?><?php echo e($gtmQueryString); ?>"
        height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
<?php endif; ?>

 <?php /**PATH /home/douglas/Downloads/ec-complete-backup-20250728_105142/ec-complete-backup-20250813_144041/resources/views/components/tracking-noscript.blade.php ENDPATH**/ ?>