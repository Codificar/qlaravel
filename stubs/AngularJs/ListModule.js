(function ()
{
	'use strict';

	angular
		.module('app.{{table_name}}.list')
		.config(config);

	/** @ngInject */
	function config($stateProvider, msNavigationServiceProvider)
	{
		// State
		$stateProvider
			.state('app.{{table_name}}_list', {
				url    : '/{{table_name}}/list',
				views  : {
					'content@app': {
						templateUrl: 'resources/angularjs/main/pages/{{table_name}}/list/{{table_name}}_list.html',
						controller : '{{class_name}}ListController as vm'
					}
				},
				resolve: {
					datasource: function (apiResolver, {{class_name}}Resource)
					{
						return apiResolver.resolve('{{class_name_lw}}@query', {});
					}
				}
			});

		// Navigation
		msNavigationServiceProvider.saveItem('{{table_name}}.list', {
			title : 'nav.list',
			icon       : 'icon-view-list',
			state      : 'app.{{table_name}}_list',
			weight     : 2
		});
	}
})();