<script type="text/javascript" src="{{ $api_render.$site_key }}"></script>
<script type="text/javascript">
    function grecaptcha_reload() {
        grecaptcha.ready(function() {
            grecaptcha.execute('{{ $site_key }}', {
                action: 'feedback_form'
            })
            .then(function(token) {
                document.getElementsByName('g-recaptcha-response').forEach(function(node) {
                    node.value = token;
                });
            });
        });
    }
    grecaptcha_reload();
</script>