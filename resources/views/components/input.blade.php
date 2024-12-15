@props([ 'type', 'name', 'input', 'errors', 'text', 'required' => false ])

<div class="form-outline data-mdb-input-init">
	<input id="{{ $name }}" 
		type="{{ $type }}" 
		class="form-control{{ $errors->has($name) ? ' is-invalid' : '' }}" 
		name="{{ $name }}" 
		value="{{ isset($input[$name]) ? $input[$name] : '' }}" {{ $required ? "required" : "" }} />
	<label for="{{ $name }}" class="form-label">{{ __($text) }}</label>
	@if ($errors->has($name))
	<div class="invalid-feedback" role="alert">
    	{{ $errors->first($name) }}
	</div>
	@endif
</div>

