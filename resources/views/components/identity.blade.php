@props([ 'identity', 'names' => \App\Interfaces\IdentityResource::IDENTITY_ATTR_KEYS ])

<div class="container border rounded rounded-4 p-4 bg-light">
	<div class="d-flex flex-row row mb-2">
		<div class="col-sm-4">{{ __('Identifier') }}</div>
		<div class="col-sm-8 fw-bold">{{ data_get($identity, 'external_id') }}</div>
	</div>
	@foreach ($names as $name)
		@if (null !== data_get($identity, $name))
		<div class="d-flex flex-row row">
			<div class="col-sm-4">{{ __($name) }}</div>
			<div class="col-sm-8 fw-bold">
				@if (is_array($value = data_get($identity, $name)))
					@foreach ($value as $oneval)
					 	{{ $oneval }}<br/>
					@endforeach
				@else
					{{ $value }}
				@endif
			</div>
		</div>
		@endif
	@endforeach
</div>
