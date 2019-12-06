<input name="g-recaptcha-response" type="hidden" value="" />

@if ($errors->has('g-recaptcha-response'))
    <div class="alert alert-danger">{{ $errors->first('g-recaptcha-response') }}</div>
@endif
