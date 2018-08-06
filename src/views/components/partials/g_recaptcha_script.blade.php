<script type="text/javascript" src="{{ $api_render.$site_key }}"></script>
<script type="text/javascript">
    function grecaptcha_reload() {
        grecaptcha.ready(function() {
            grecaptcha.execute('{{ $site_key }}', {
                action: 'feedback_form'
            })
            .then((token) => {
                let elements = document.getElementsByName('g-recaptcha-response');
                for(let i = 0; i < elements.length; i++) {
                    elements[i].value = token;
                }
            });
        });
    }
    grecaptcha_reload();
</script>