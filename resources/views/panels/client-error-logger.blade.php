<script>
    (function () {
        var endpoint = @json(route('client-log.store'));
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var sent = {};
        var remaining = 20;

        function report(payload) {
            var key = payload.type + '|' + payload.message + '|' + (payload.source || payload.ajax_url || '');
            if (remaining <= 0 || sent[key] || !window.fetch) {
                return;
            }
            sent[key] = true;
            remaining--;
            payload.page_url = window.location.href;

            fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: true,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': tokenMeta ? tokenMeta.content : ''
                },
                body: JSON.stringify(payload)
            }).catch(function () {});
        }

        window.addEventListener('error', function (event) {
            if (!event.message) {
                return;
            }
            report({
                type: 'error',
                message: String(event.message).slice(0, 2000),
                source: event.filename || null,
                line: event.lineno || null,
                column: event.colno || null,
                stack: event.error && event.error.stack ? String(event.error.stack).slice(0, 8000) : null
            });
        });

        window.addEventListener('unhandledrejection', function (event) {
            var reason = event.reason || {};
            report({
                type: 'unhandledrejection',
                message: String(reason.message || reason).slice(0, 2000),
                stack: reason.stack ? String(reason.stack).slice(0, 8000) : null
            });
        });

        if (window.jQuery) {
            window.jQuery(document).ajaxError(function (event, xhr, settings, thrown) {
                if (!settings || !settings.url || settings.url.indexOf(endpoint) === 0 || xhr.status === 0 || xhr.status === 422) {
                    return;
                }
                report({
                    type: 'ajax',
                    message: String(thrown || xhr.statusText || 'AJAX error').slice(0, 2000),
                    ajax_url: String(settings.url).slice(0, 2000),
                    status: xhr.status
                });
            });
        }
    })();
</script>
