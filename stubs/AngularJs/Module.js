(function ()
{
	'use strict';

	angular
		.module('app.{{table_name}}', [
			'app.{{table_name}}.list',
			'app.{{table_name}}.register'
		])
		.config(config);

	/** @ngInject */
	function config(msNavigationServiceProvider, $translatePartialLoaderProvider)
	{
		// Navigation
		msNavigationServiceProvider.saveItem('{{table_name}}', {
			title : '{{class_name}}',
			weight: 1 
		});

		$translatePartialLoaderProvider.addPart('app/main/pages');
		$translatePartialLoaderProvider.addPart('resources/angularjs/main/pages/{{table_name}}');
	}
})();