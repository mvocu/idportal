@props([ 'id', 'name', 'for', 'url', 'text', 'text_again' => "" ])

@push('app.scripts')
<script type="module">

window.{{$id}}_app = idPortal.createApp({

    setup(props, context) {
        // component logic

        // declare some reactive state here.
        const tokenSent = idPortal.ref(0);
        const sendError = idPortal.ref("");
 
        return {
            // expose to template
            tokenSent,
            sendError
        }
    },

    methods: {
			sendToken: function() {
				var context = this;
                @guest
				var reCaptcha = grecaptcha.getResponse();
				grecaptcha.reset();
                @else
                var reCaptcha = "";
                @endguest	
                var value = document.getElementById('{{ $for }}').value;
                document.getElementById('{{ $for }}').classList.remove('bg-opacity-25', 'bg-danger');
                axios.post('{{ $url }}', {
                    'address' : value,
                    'recaptcha' : reCaptcha
                }).then(function (response) {
                    var reply = response.data;
                    if(reply.error) {
                        context.tokenSent = 0;
                        context.sendError = reply.error;
                        // mark failed input if available
                        if(reply.reason.recaptcha) {
                            // find and mark recaptcha field
                            document.getElementById('recaptcha').classList.add('border', 'border-danger');
                        }
                        if(reply.reason.address) {
                            // find and mark $for field
                            document.getElementById('{{ $for }}').classList.add('bg-opacity-25', 'bg-danger');
                        }
                    } else {
                        context.tokenSent = 1;		
                        context.sendError = null;
                    }	
                }).catch(function (error) {
                    context.sendError = error;
                    context.tokenSent = 0;
                });   
			},
			resendToken: function() {
				this.sendToken();
			}
  	}

}).mount('#{{ $id }}');

</script>

@endpush

<div id="{{ $id }}">
	<div class="d-flex flex-row">
	    <button type="button" class="btn btn-secondary form-control data-mdb-button-init"
	    	 v-on:click="sendToken()" 
	    	 v-if="!tokenSent">
			{{ __($text) }}
		</button>
		<div class="form-outline" v-if="tokenSent">
			<input id="{{ $name }}" type="text" class="form-control" name="{{ $name }}" value=""></input>
			<label for="{{ $name }}" class="form-label">{{ __('Verification code') }}</label>
		</div>
		<div class="">
		    <button type="button" class="btn btn-secondary" v-on:click="resendToken()" v-if="tokenSent">
    		    @if (empty($text_again))
    		    	 <i class="fas fa-sync"></i>
    		    @else 
    		      {{ __($text_again) }}
    		    @endif
	    	</button>
		</div>
		<div v-if="sendError" class="text-danger small">
			@{{ sendError }}	
		</div>				    	
	</div>
</div>

<!--
	<input type="text" class="form-control" name="{{ $name }}" value=""></input>
	<label for="" class="form-label"></label>
    <div class="error" v-if="sendError"></div>
 -->
