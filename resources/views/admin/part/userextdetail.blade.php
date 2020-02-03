<div class="row mt-1">
	<div class="col-xs-4">
		Id
	</div>
	<div class="col-xs-8">
		{{ $user->id }}
	</div>
</div>
@foreach($user->attributes as $attr)
<div class="row mt-1">
	<div class="col-xs-4">
		{{ $attr->attrDesc->name }}
	</div>
	<div class="col-xs-8">
		{{ $attr->value }}
	</div>
</div>
@endforeach
