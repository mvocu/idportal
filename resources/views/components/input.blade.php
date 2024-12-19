@props([ 'type', 'name', 'value' => null, 'errors', 'text', 
	'required' => false, 'maxlen' => null, 'pattern' => null, 'helper' => null ])

<div class="form-outline data-mdb-input-init">
	<input id="{{ $name }}" 
		type="{{ $type }}" 
		class="form-control{{ $errors->has($name) ? ' is-invalid' : '' }}" 
		name="{{ $name }}" 
		@if (!empty($maxlen))
		data-mdb-showcounter="true" maxlength="{{ $maxlen }}"
		@endif
		@if (!empty($pattern))
		pattern="{{ $pattern }}"
		@endif
		value="{{ isset($value) ? $value : old($name) }}" {{ $required ? "required" : "" }} />
	<label for="{{ $name }}" class="form-label">{{ __($text) }}</label>
	@if (!empty($maxlen))
	<div class="form-helper">{{ empty($helper) ? "" : __($helper) }}</div> 
	@endif
	@if ($errors->has($name))
	<div class="invalid-feedback" role="alert">
    	{{ $errors->first($name) }}
	</div>
	@endif
</div>

