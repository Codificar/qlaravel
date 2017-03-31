<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Models\{{class_name}};
use Schema;
// relationship tables use classes
{{relationship_tables_classes}}
// eager use classes
{{eager_use_classes}}

class {{class_name}}Controller extends Controller
{
	// blade views
	// index view
	public function index()
	{
		return view('{{class_name_lw}}.index');
	}
	// create view
	public function create()
	{
		return view('{{class_name_lw}}.create');
	}

	// edit view
	public function edit($id)
	{
		${{class_name_lw}} = {{class_name}}::find($id);

		return view('{{class_name_lw}}.edit')->with('model', ${{class_name_lw}});
	}

	// api resources
	

	// save {{class_name_lw}}
	public function store(Request $request)
	{
		// validate {{class_name_lw}} and eager objects
		$this->validate($request, $this->getRules());
		
		$model = new {{class_name}}();

		${{class_name_lw}} = $model->store($request);

		return ${{class_name_lw}};
	}

	// update {{class_name_lw}} by id
	public function update($id, Request $request)
	{
		// validate {{table_name}} and eager objects
		$this->validate($request, $this->getRules($id));
		
		$model = new {{class_name}}();
		${{class_name_lw}} = $model->updateModel($id, $request);

		return ${{class_name_lw}};
	}


	// get {{class_name_lw}} by id
	public function show($id)
	{
		$model = new {{class_name}}();
		${{class_name_lw}} = $model->show($id);
		return ${{class_name_lw}} ;
	}

	// find first by field and value
	public function findByField(Request $request)
	{
		$model = new {{class_name}}();
		${{class_name_lw}} = $model->findByField($request);
		return ${{class_name_lw}} ;
	}

	// build all validation rules
	protected function getRules($id = null){
		// default object rules
		$model = new {{class_name}}();
		$rules = $model::$rules;
		${{class_name_lw}} = $model->show($id);
		{{unique_rules}}
		// nested rules for eager objects
		{{rules_eager}}
		
		return $rules;
	}

	// delete model by ids
	public function destroy($id)
	{
		$ids = explode(",", $id);
	    $ids = array_unique($ids);

	    $model = new {{class_name}}();

	    $success = $model->destroy($ids);

	    $status = array("error" => true, "message" => "Error deleting object");
		if ($success)
			$status = array("error" => false, "message" => "Object successfully deleted");

		return json_encode($status);

	}

	{{reverseRelationships}}
	{{relationship_tables_store}}
	{{remove_relationship_objects}}
	{{enum}}
	{{checkbox}}


	// query with search and pagination options
	public function query(Request $request)
	{
		$model = new {{class_name}}();

		$query = $model->querySearch($request);

		return $query;
	}


	// query with fields filters and pagination
	//json = {"pagination": {"actual": 1, "itensPerPage": 20}, "fields": ["name","email","cnpj"], "orderBy": "name"}
	public function queryFilters(Request $request)
	{
		$model = new {{class_name}}();

		${{class_name_lw}} = $model->queryFilters($request);
		
		return ${{class_name_lw}};
	}

	
}
