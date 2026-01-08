<!-- Custom Alert Modal -->
@include('components.custom-alert-modal')

<!-- Global Page Loader -->
<x-system-preloader x-show="pageLoading" message="Loading page..." />

<script>
    // Global page loading handler
    (function() {
        function initPageLoader() {
            const body = document.body;
            let alpineData = null;
            
            if (typeof Alpine !== 'undefined' && Alpine.$data) {
                alpineData = Alpine.$data(body);
            }
            
            if (alpineData && typeof alpineData.pageLoading !== 'undefined') {
                // Show loader on link clicks
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('a[href]');
                    if (link && link.href && !link.href.startsWith('javascript:') && !link.href.startsWith('#')) {
                        const href = link.getAttribute('href');
                        if (href && !href.startsWith('#') && !link.hasAttribute('data-no-loader')) {
                            alpineData.pageLoading = true;
                        }
                    }
                });
                
                // Show loader on form submissions (but don't prevent submission)
                document.addEventListener('submit', function(e) {
                    const form = e.target;
                    if (form && form.tagName === 'FORM' && !form.hasAttribute('data-no-loader')) {
                        // Only show loader if form is actually submitting
                        // Don't prevent default - let form submit normally
                        // Use setTimeout to ensure the form submission isn't blocked
                        setTimeout(function() {
                            alpineData.pageLoading = true;
                        }, 0);
                    }
                });
                
                // Hide loader when page is fully loaded
                if (document.readyState === 'complete') {
                    alpineData.pageLoading = false;
                } else {
                    window.addEventListener('load', function() {
                        alpineData.pageLoading = false;
                    });
                }
                
                // Hide loader on popstate (back/forward navigation)
                window.addEventListener('popstate', function() {
                    alpineData.pageLoading = false;
                });
            } else {
                setTimeout(initPageLoader, 100);
            }
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPageLoader);
        } else {
            setTimeout(initPageLoader, 100);
        }
    })();
</script>

