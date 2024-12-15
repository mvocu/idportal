@props([ 'id', 'name', 'value', 'text', 'options' ])

@push('app.scripts')
<script type="module">

window.{{$id}}_app = idPortal.createApp({
  components: {
    'VueSelect': idPortal.VueSelect
  },
  setup() {
    // component logic
    // declare some reactive state here.
    const model = idPortal.ref("{{ $value }}"); 
 
    return {
      // exposed to template
        model
    }
  }
}).mount('#{{ $id }}');
</script>

@endpush

<div id="{{ $id }}">
	<vue-select v-model="model" 
		:options="{{ $options }}"
		placeholder="{{ __($text) }}"
		input-id="{{ $name }}">
	</vue-select>
	<input type="hidden" name="{{ $name }}" v-bind:value="model" />
</div>
