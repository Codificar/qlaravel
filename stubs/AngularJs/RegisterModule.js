(function ()
{
	'use strict';

	angular
		.module('app.{{table_name}}.register', ['app.components.validation', 'app.upload-file']) 
		.config(config);

	/** @ngInject */
	function config($stateProvider, msNavigationServiceProvider)
	{
		// State
		$stateProvider
			.state('app.{{table_name}}_register', {
				url    : '/{{table_name}}/:id', 
				views  : {
					'content@app': {
						templateUrl: 'resources/angularjs/main/pages/{{table_name}}/register/{{table_name}}_register.html', 
						controller : '{{class_name}}Controller as vm' 
					}
				},
				resolve: {
					{{table_name}}: function ($stateParams, {{class_name}}Resource, apiResolver)
					{
						if( $stateParams.id ){
							return apiResolver.resolve('{{class_name_lw}}@get', { 'id' : $stateParams.id });
						}
					}
				}
			});

		// Navigation

		msNavigationServiceProvider.saveItem('{{table_name}}.register', {
			title      : 'nav.register',
			icon       : 'icon-plus-circle-outline',
			state      : 'app.{{table_name}}_register',
			weight     : 1
		});
	}
})(); 