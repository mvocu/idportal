@switch ($contact->type)
@case (1)
<div class="row">
	<div class="col-12">
		{{ $contact->street }}&nbsp;{{ $contact->org_number }}/{{ $contact->ev_number }},
		{{ $contact->post_number }}&nbsp;{{ $contact->city }}&nbsp;
	</div>
</div>
@default
@endswitch