<template>
<div class="sms-authorization">

                         <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
								<div class="g-recaptcha" v-bind:data-sitekey="recaptcha.client_secret"></div>
                            </div>
                        </div>

                         <div class="form-group row">
                            <label for="phone" class="col-md-4 col-form-label text-md-right">{{ phone.label }}</label>

                            <div class="col-md-6">
                                <input id="phone" type="text" v-model="mobile" class="form-control" v-bind:class="{ 'is-invalid': !phone.valid }" name="phone" required>

								<slot name="phone-error"></slot>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <button type="button" class="btn btn-primary" v-on:click="sendToken(mobile)">
                                    {{ send.label }}
                                </button>
                            </div>
                        </div>

						<div class="fixed-top text-center w-100 h-100" style="background-color: rgba(240,240,240,0.6)" v-if="busy">
						  <img class="position-absolute" style="top: 30%" src="/images/ajax-loader.gif">
						</div>
						
                        <div class="form-group row" v-if="tokenSent || !token.valid ">
                            <label for="token" class="col-md-4 col-form-label text-md-right">{{ token.label }}</label>

                            <div class="col-md-6">
                                <input ref="token" id="token" type="text" class="form-control" v-bind:class="{ 'is-invalid': !token.valid }" v-model="authcode" name="token" required autofocus>

								<slot name="token-error"></slot>
                            </div>
                        </div>
</div>	
</template>

<script>
    export default {
    	data() {
    		return {
    			tokenSent: 0,
    			mobile: this.phone.old,
    			authcode: "",
    			busy: 0
    		}
    	},
    	props: {
    		phone: Object,
    		token: Object,
    		send: Object,
    		recaptcha: Object
    	},
    	methods: {
			sendToken: function(phone) {
				var context = this;
				var reCaptcha = grecaptcha.getResponse();
				this.busy = 1;
				jQuery.post(this.send.url, { 'phone': this.mobile, 'g-recaptcha-response': reCaptcha })
					  .done(function(data) { context.tokenSent = 1; context.busy = 0; context.authcode = ""; context.setFocus() });
			},
			setFocus: function() {
				this.$nextTick(() => this.$refs.token.focus());
			}
    	}
    }
    
</script>
