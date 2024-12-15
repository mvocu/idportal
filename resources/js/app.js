require('./mdb.js');

// import here so webpack includes it to the generated app
import { createApp, ref } from 'vue';
//import { initMDB, Input, Button } from 'mdb-ui-kit';
import VueSelect from "vue3-select-component";

// store the imported methods into global object so that they are available later in the application pages
window.idPortal = {
//	'initMDB': initMDB,
//	'Input': Input,
//	'Button': Button,
	'createApp': createApp,
	'ref': ref,
	'VueSelect': VueSelect,
};

// const app = createApp({}).mount('#app')


