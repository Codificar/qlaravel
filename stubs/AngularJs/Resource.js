(function ()
{
	'use strict';

	angular
		.module('app.{{table_name}}')
		.factory('{{class_name}}Resource', {{class_name}}Resource); 

	function {{class_name}}Resource($resource, api)
	{

		var {{class_name}} = $resource(api.baseUrl + "api/{{class_name_lw}}/:id", { id: '@id' }, 
			{ 
				'update': { method:'PUT' }, 
				'query': { method:'POST' , url: api.baseUrl + 'api/{{class_name_lw}}/query' }
			});

		var resource = {

			create: function(data){

				return new {{class_name}}(data).$save();
			},

			get: function(id){	

				return {{class_name}}.get({ id: id });
			},

			update: function(id, data){

				return {{class_name}}.update({ id: id }, data); 
			},

			delete: function(id){

				return {{class_name}}.delete({ id: id });
			},

			query: function(options){

				var options = options || {};

				return {{class_name}}.query(options);
			}

		};

		api.{{class_name_lw}} = {{class_name}} ;


		return resource;

	}

})();