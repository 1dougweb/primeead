@php
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
@endphp

{{-- Google Tag Manager (noscript) - Para usuários sem JavaScript habilitado --}}
@if($trackingSettings['enable_google_analytics'] && !empty($trackingSettings['google_tag_manager_id']))
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id={{ $trackingSettings['google_tag_manager_id'] }}{{ $gtmQueryString }}"
        height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
@endif

{{-- Outras tags de rastreamento para usuários sem JavaScript podem ser adicionadas aqui --}} 