<script>
export default {

	// input parameters
  	props: {
		id: String,
		name: String,
  	},

  	// internal properties
  	data() {
  		return {
			tokenSent: 0,
			sendError: 0,
  		}
  	},
  	
	// internal methods
	methods: {
		sendToken: function(mobile) {},
		resendToken: function(mobile) {},
	},
}
</script>

<template>
<div class="form-outline">
<div class="d-flex flex-row">
    <button type="button" class="btn btn-secondary" v-on:click="sendToken(mobile)" v-if="!tokenSent && !sendError">
		Send
    </button>
<!--
    <button type="button" class="btn btn-primary" v-on:click="resendToken(mobile)" v-if="tokenSent || sendError">
        Resend
    </button>
	<input type="text" class="form-control" name="{{ name }}" value=""></input>
	<label for="" class="form-label"></label>
    <div class="error" v-if="sendError"></div>
 -->
</div>
</div>
</template>