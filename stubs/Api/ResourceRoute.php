	Route::get('{{resource_lw}}/query', '{{resource}}Controller@query');
	Route::post('{{resource_lw}}/query', '{{resource}}Controller@query');
	Route::get('{{resource}}/findby', '{{resource}}Controller@findByField');
	Route::post('{{resource_lw}}/queryfilters', '{{resource}}Controller@queryFilters');
	Route::get('{{resource_lw}}/gettree', '{{resource}}Controller@getTree');
	{{enum}}
	Route::resource('{{resource_lw}}', '{{resource}}Controller');
{{reverseRelationships}}