@if (empty($user->user_id))
<a href="{{ route('admin.userext.notify', [ 'user' => $user ]) }}" class="btn btn-primary btn-sm btn-small"><span class="fa fa-bell">&nbsp;</span></a>
@endif