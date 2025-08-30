/**
 * CSRF Token Helper
 * Handles CSRF token refresh and AJAX requests
 */
class CsrfHelper {
    constructor() {
        this.initialized = false;
        this.init();
    }

    /**
     * Initialize the CSRF helper
     */
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    /**
     * Setup CSRF handling
     */
    setup() {
        if (this.initialized) return;
        
        this.setupAjaxDefaults();
        this.setupErrorHandling();
        this.initialized = true;
        
        console.log('CSRF Helper initialized');
    }

    /**
     * Setup default AJAX headers with CSRF token
     */
    setupAjaxDefaults() {
        // Set default CSRF token for all AJAX requests (jQuery)
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': this.getToken()
                }
            });
        }

        // Also setup for fetch requests
        if (typeof fetch !== 'undefined') {
            const originalFetch = window.fetch;
            window.fetch = (url, options = {}) => {
                const token = this.getToken();
                options.headers = {
                    ...options.headers,
                    'X-CSRF-TOKEN': token
                };
                return originalFetch(url, options);
            };
        }
    }

    /**
     * Setup error handling for CSRF token mismatch
     */
    setupErrorHandling() {
        // jQuery error handling
        if (typeof $ !== 'undefined') {
            $(document).ajaxError((event, xhr, settings) => {
                if (xhr.status === 419) { // CSRF token mismatch
                    console.log('CSRF token mismatch detected, refreshing token...');
                    this.refreshToken().then(() => {
                        // Retry the original request
                        $.ajax(settings);
                    }).catch(error => {
                        console.error('Failed to refresh CSRF token:', error);
                        // Redirect to login if token refresh fails
                        window.location.href = '/login';
                    });
                }
            });
        }

        // Fetch error handling
        if (typeof fetch !== 'undefined') {
            const originalFetch = window.fetch;
            window.fetch = async (url, options = {}) => {
                try {
                    const response = await originalFetch(url, options);
                    if (response.status === 419) {
                        console.log('CSRF token mismatch detected in fetch, refreshing token...');
                        await this.refreshToken();
                        // Retry the original request
                        return await originalFetch(url, options);
                    }
                    return response;
                } catch (error) {
                    console.error('Fetch error:', error);
                    throw error;
                }
            };
        }
    }

    /**
     * Refresh CSRF token
     */
    async refreshToken() {
        try {
            console.log('Refreshing CSRF token...');
            
            const response = await fetch('/refresh-csrf', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`Failed to refresh token: ${response.status}`);
            }

            const data = await response.json();
            
            // Update the meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                metaTag.setAttribute('content', data.token);
            }
            
            // Update any hidden input fields
            document.querySelectorAll('input[name="_token"]').forEach(input => {
                input.value = data.token;
            });

            // Update AJAX defaults
            if (typeof $ !== 'undefined') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': data.token
                    }
                });
            }

            console.log('CSRF token refreshed successfully:', data.token);
            return data.token;
        } catch (error) {
            console.error('Error refreshing CSRF token:', error);
            throw error;
        }
    }

    /**
     * Get current CSRF token
     */
    getToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }

    /**
     * Update CSRF token in all forms
     */
    updateForms() {
        const token = this.getToken();
        document.querySelectorAll('input[name="_token"]').forEach(input => {
            input.value = token;
        });
    }

    /**
     * Manual refresh token (for testing)
     */
    static async manualRefresh() {
        const helper = new CsrfHelper();
        return await helper.refreshToken();
    }
}

// Initialize CSRF helper
window.csrfHelper = new CsrfHelper();

// Export for use in other scripts
window.CsrfHelper = CsrfHelper;

// Also initialize when jQuery is ready (if jQuery loads after this script)
if (typeof $ !== 'undefined') {
    $(document).ready(() => {
        if (window.csrfHelper) {
            window.csrfHelper.setup();
        }
    });
} 