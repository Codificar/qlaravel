<?php
/*
|--------------------------------------------------------------------------
| Scaffolder routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => '{{route_prefix}}', 'middleware' => 'cors'], function ()
{

	Route::get('dashboard', function ()
	{
		return view('dashboard');
	});

	Route::match(['post'], 'upload', 'FileController@upload')->middleware('cors');
	Route::get('upload', 'FileController@upload');
	
{{routes}}
});

# todo fix in the correct manner
// font fix issues
Route::get('/styles/ui-grid.woff', function(){
	return file_get_contents(base_path('public').'/fonts/ui-grid.woff');
});

Route::get('/styles/ui-grid.ttf', function(){
	return file_get_contents(base_path('public').'/fonts/ui-grid.ttf');
});

// other routes point for the dist html file
Route::any('{any}', function(){
	return file_get_contents(base_path('public').'/index.html');
})->where('any', '.*');