@props([ 'identity', 'names' => \App\Interfaces\IdentityResource::IDENTITY_ATTR_KEYS ])

<div class="container border rounded rounded-4 p-4 bg-light">
	<div class="d-flex flex-row row mb-2">
		<div class="col-sm-4">{{ __('Identifier') }}</div>
		<div class="col-sm-8 fw-bold">{{ data_get($identity, 'external_id') }}</div>
	</div>
	<div class="d-flex flex-row row mb-2
	      @switch(data_get($identity, 'loa')) 
		  @case ('http://cas.cuni.cz/LoA/none')
		   bg-secondary text-white
		  @break
	      @case ('http://cas.cuni.cz/LoA/low')
	       bg-warning text-white
	      @break
		  @case ('http://cas.cuni.cz/LoA/substantial')
		   bg-info text-white
		  @break
		  @case ('http://cas.cuni.cz/LoA/high')
		   bg-success text-white
		  @break
		  @default 
		   bg-danger text-white
		  @endswitch     	
	">
		<div class="col-sm-4">{{ __('loa') }}</div>
		<div class="col-sm-8 fw-bold">{{ empty(data_get($identity, 'loa')) ? __('None') : __(data_get($identity, 'loa')) }}</div>
	</div>
	@foreach ($names as $name)
		@if (!empty(data_get($identity, $name)) && $name != \App\Interfaces\IdentityResource::LOA)
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
