<div class="row mt-1">
	<div class="col-xs-4">
		Id
	</div>
	<div class="col-xs-8">
		{{ $user->id }}
	</div>
</div>
<div class="row mt-1">
	<div class="col-xs-4">
		Parent
	</div>
	<div class="col-xs-8">
		{{ $user->parent }}
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
<div class="row mt-1">
	<div class="col-xs-4">
		Created
	</div>
	<div class="col-xs-8">
		{{ $user->created_at }}
	</div>
</div>
<div class="row mt-1">
	<div class="col-xs-4">
		Modified
	</div>
	<div class="col-xs-8">
		{{ $user->updated_at }}
	</div>
</div>
