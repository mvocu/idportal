<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item active" role="presentation">
		<a class="nav-link" id="personal-tab" data-toggle="tab" href="#personal-{{$id}}" role="tab" aria-controls="personal" aria-selected="true">{{ __("Personal information") }}</a>
	</li>				
	<li class="nav-item" role="presentation">
		<a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact-{{$id}}" role="tab" aria-controls="contact" aria-selected="false">{{ __("Contacts") }}</a>
	</li>				
	<li class="nav-item" role="presentation">
		<a class="nav-link" id="account-tab" data-toggle="tab" href="#account-{{$id}}" role="tab" aria-controls="account" aria-selected="false">{{ __("Accounts") }}</a>
	</li>
	<li class="nav-item" role="presentation">
		<a class="nav-link" id="ldap-tab" data-toggle="tab" href="#ldap-{{$id}}" role="tab" aria-controls="ldap" aria-selected="false">{{ __("LDAP entry") }}</a>
	</li>
	<li class="nav-item" role="presentation">
		<a class="nav-link" id="status-tab" data-toggle="tab" href="#status-{{$id}}" role="tab" aria-controls="status" aria-selected="false">{{ __("Status") }}</a>
	</li>
</ul>
<div class="tab-content">
	<div role="tabpanel" class="tab-pane fade in active" id="personal-{{$id}}" role="tabpanel" aria-labelledby="personal-tab">
		<div class="row mt-2">
			<div class="col-xs-11">
				@if (!empty($user->parent))
				<div class="row">
					<div class="col-xs-4">
						{{ __("parent") }}
					</div>
					<div class="col-xs-8">
						@include('components.userlink', ['user' => $user->parent, 'name' => $user->parent_id])
					</div>
				</div>
				@endif
				@foreach([
					'identifier', 'first_name', 'last_name', 'middle_name', 'title_before', 
					'title_after', 'birth_date', 'birth_code', 'gender', 'country'] as $attr)
				<div class="row">
					<div class="col-xs-4">
						{{ __($attr) }}
					</div>
					<div class="col-xs-8">
						{{ $user->$attr }}
					</div>
				</div>
				@endforeach
			</div>
			<div class="col-xs-1">
			</div>
		</div>
	</div>
	<div role="tabpanel" class="tab-pane fade" id="contact-{{$id}}" role="tabpanel" aria-labelledby="contact-tab">
		<div class="row mt-2 mb-1">
			<div class="col-xs-2">
				{{ __('Phone') }}
			</div>
			<div class="col-xs-3">
				@foreach($user->phones as $phone)
					{{ $phone->phone }} 
					<br />
				@endforeach
			</div>
			<div class="col-xs-2">
				{{ __('Email') }}
			</div>
			<div class="col-xs-5">
				@foreach($user->emails as $email)
					{{ $email->email }}
					 <br />
				@endforeach
			</div>
		</div>
		@foreach(['residency', 'address', 'addressTmp', 'birthPlace'] as $attr)
		<div class="row mt-1">
			<div class="col-xs-2">
						{{ __($attr) }}
			</div>
			<div class="col-xs-10">
				@if(!empty($user->$attr))
				@component('components.contact', ['contact' => $user->$attr ])
				@endcomponent
				@endif
			</div>
		</div>
		@endforeach
		<div class="row mt-1">
			<div class="col-xs-2">
						{{ __('Addresses') }}
			</div>
			<div class="col-xs-10">
				@if(!empty($user->addresses))
				@foreach ($user->addresses as $addr)
				@component('components.contact', ['contact' => $addr ])
				@endcomponent
				@endforeach
				@endif
			</div>
		</div>
	</div>
	<div role="tabpanel" class="tab-pane fade" id="account-{{$id}}" role="tabpanel" aria-labelledby="account-tab">
		<div class="row mt-3">
			<div class="col-xs-12">
				{{ tableView($user->accounts)
					->column('Source', function($acct) { return __($acct->extSource->name); })
					->column('Identifier', 'login')
					->column('Last updated', 'updated_at')
		        	->childDetails(function ($user) {
        		    	return view('admin.part.userextdetail', ['embed' => true, 'id' => $user->id, 'user' => $user]);
        			})
					->setTableClass('table compact hover')
					->useDataTable()
					->render() }}
			</div>
		</div>
	</div>
	<div role="tabpanel" class="tab-pane fade" id="ldap-{{$id}}" role="tabpanel" aria-labelledby="ldap-tab">
		@if (isset($ldapuser))
		@foreach(['uniqueidentifier', 'uid', 'cn', 'givenname', 'sn', 'c', 'telephonenumber', 'mail', 
			'street', 'l', 'st', 'postalCode', 'houseIdentifier', 'postalAddress'] as $attr)
		<div class="row mt-2">
			<div class="col-xs-4">
				{{ __($attr) }}
			</div>
			<div class="col-xs-8">
				@if(!empty($vals = $ldapuser->getAttribute($attr)))
				@foreach($vals as $val)
					{{ $val }} <br/>
				@endforeach
				@endif
			</div>
		</div>
		@endforeach
		@foreach($ldapuser->getAttributesAndTags('employeenumber') as $es => $login)
		<div class="row mt-1">
			<div class="col-xs-4">
				{{ __("employeenumber;".$es) }}
			</div>
			<div class="col-xs-8">
				@foreach($login as $val)
					{{ $val }} <br/>
				@endforeach
			</div>
		</div>
		@endforeach
		<div class="row mt-1">
			<div class="col-xs-4">
				{{ __("Account status") }}
			</div>
			<div class="col-xs-8">
				{{ $lock ? "LOCKED" : "ACTIVE" }}
			</div>
		</div>
		<div class="row mt-1">
			<div class="col-xs-4">
				{{ __("Password status") }}
			</div>
			<div class="col-xs-8">
				{{ $haspw ? "SET": "UNSET" }}
			</div>
		</div>
		@else 
			No LDAP record.
		@endif
	</div>
	<div role="tabpanel" class="tab-pane fade" id="status-{{$id}}" role="tabpanel" aria-labelledby="status-tab">
		<div class="row mt-2">
			<div class="col-xs-11">
				@foreach(['trust_level', 'consent_requested', 'consent_at', 'created_at', 'updated_at'] as $attr)
				<div class="row mt-1">
					<div class="col-xs-4">
						{{ __($attr) }}
					</div>
					<div class="col-xs-8">
						{{ $user->$attr }}
					</div>
				</div>
				@endforeach
			</div>
			<div class="col-xs-1">
			</div>
		</div>
	</div>
</div>

@push(config('tableView.dataTable.js.stack_name'))
<script type="text/javascript">
window.addEventListener('load', function () {
    $('a[data-toggle="tab"]').on( 'shown.bs.tab', function (e) {
        $.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
    } );
} );
</script>
@endpush
