(function ()
{
	'use strict';

	/**
	 * Main module of the Fuse
	 */
	angular
		.module('scaffolder', [

			// Core
			'app.core',

			// Navigation
			'app.navigation',

			// Toolbar
			'app.toolbar',

			// Quick panel
			'app.quick-panel',

			// Model
			'app.model',

			// Sample
			'app.sample',

			// BlankState
			'app.blankstate',

			// TinyMCE
			'ui.tinymce',

			// Flowjs
			'flow',

			// Sample
			'app.sample',
{{tables}}

		]);
})();