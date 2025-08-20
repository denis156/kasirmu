<div>
    @assets
        <script 
            src="{{ app('midtrans')->getSnapUrl() }}" 
            data-client-key="{{ app('midtrans')->getClientKey() }}"
            id="snap-script">
        </script>
    @endassets

    @script
        <script>
            // Simple error handling untuk CORS postMessage errors
            window.addEventListener('error', function(e) {
                if (e.message && (
                    e.message.indexOf('postMessage') !== -1 || 
                    e.message.indexOf('Cannot read properties of null') !== -1 ||
                    e.message.toLowerCase().indexOf('cors') !== -1
                )) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }, true);

            window.addEventListener('unhandledrejection', function(e) {
                var reason = e.reason ? e.reason.toString() : '';
                if (reason.indexOf('postMessage') !== -1 || 
                    reason.indexOf('Cannot read properties of null') !== -1) {
                    e.preventDefault();
                    return false;
                }
            });

            $wire.on('triggerSnapPay', function(snapToken) {
                var token = Array.isArray(snapToken) ? snapToken[0] : snapToken;

                if (!token || typeof token !== 'string') {
                    $wire.call('handleError', { error: 'Invalid snap token' });
                    return;
                }

                if (typeof snap === 'undefined') {
                    $wire.call('handleError', { error: 'Snap library not loaded' });
                    return;
                }

                var snapScript = document.getElementById('snap-script');
                var clientKey = snapScript ? snapScript.getAttribute('data-client-key') : null;
                if (!clientKey || clientKey.trim() === '') {
                    $wire.call('handleError', { error: 'Midtrans client key not configured' });
                    return;
                }

                try {
                    snap.pay(token, {
                        skipOrderSummary: false,
                        onSuccess: function(result) {
                            $wire.call('handleSuccess', result);
                        },
                        onPending: function(result) {
                            $wire.call('handlePending', result);
                        },
                        onError: function(result) {
                            if (result && (result.status_code || result.transaction_status)) {
                                $wire.call('handleError', result);
                            } else if (result && result.error && result.error.toString().indexOf('postMessage') === -1) {
                                $wire.call('handleError', result);
                            } else {
                                $wire.call('handleClose');
                            }
                        },
                        onClose: function() {
                            $wire.call('handleClose');
                        }
                    });

                } catch (error) {
                    if (error.message && (
                        error.message.indexOf('postMessage') !== -1 || 
                        error.message.indexOf('Cannot read properties of null') !== -1
                    )) {
                        $wire.call('handleClose');
                    } else {
                        $wire.call('handleError', { error: error.message });
                    }
                }
            });
        </script>
    @endscript
</div>
