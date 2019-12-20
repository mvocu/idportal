{{ __('User :user asks for account at :source', 
	['user'=> $user->first_name . " ". $user->last_name . " (". $user->identifier . ")", 'source' => $source->name]) }}