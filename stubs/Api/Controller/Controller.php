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
	/*public function store(Request $request)
	{
		// validate {{class_name_lw}} and eager objects
		$this->validate($request, $this->getRules());
		

		$vars = $request->all();

		//create eager objects
		{{store_eager_objects}}

		${{class_name_lw}} = {{class_name}}::create($vars);

		//create relationship_tables objects
		{{relationship_tables_call}}
		return ${{class_name_lw}} ;
	}*/

	// save {{class_name_lw}}
	public function store(Request $request)
	{
		// validate {{class_name_lw}} and eager objects
		$this->validate($request, $this->getRules());
		
		$model = new {{class_name}}();

		${{class_name_lw}} = $model->store($request);

		return ${{class_name_lw}};
	}

	/*// update {{class_name_lw}} by id
	public function update($id, Request $request)
	{
		// get vars
		$vars = $request->all();
		
		// validate {{table_name}} and eager objects
		$this->validate($request, $this->getRules($id));
		
		//update eager objects
		{{update_eager_objects}}

		${{class_name_lw}} = {{class_name}}::find($id);
		${{class_name_lw}}->fill($vars);
		${{class_name_lw}}->save();

		//update relationship_tables objects
		{{remove_relationship_objects_call}}
		{{relationship_tables_call}}
		return ${{class_name_lw}};
	}*/
	public function update($id, Request $request)
	{
		// validate {{table_name}} and eager objects
		$this->validate($request, $this->getRules($id));
		
		$model = new {{class_name}}();
		${{class_name_lw}} = $model->updateModel($id, $request);

		return ${{class_name_lw}};
	}


	/*// get {{class_name_lw}} by id
	public function show($id)
	{
		${{class_name_lw}} = {{class_name}}::find($id);
		return ${{class_name_lw}} ;
	}*/
	public function show($id)
	{
		$model = new {{class_name}}();
		${{class_name_lw}} = $model->show($id);
		return ${{class_name_lw}} ;
	}

	/*// find first by field and value
	public function findByField(Request $request)
	{
		${{class_name_lw}} = {{class_name}}::where($request->input('field') , '=', $request->input('value'))->first();
		return ${{class_name_lw}} ;
	}*/
	public function findByField(Request $request)
	{
		$model = new {{class_name}}();
		${{class_name_lw}} = $model->findByField($request);
		return ${{class_name_lw}} ;
	}

	/*// build all validation rules
	protected function getRules($id = null){
		// default object rules
		$rules = {{class_name}}::$rules;
		${{class_name_lw}} = {{class_name}}::find($id);
		{{unique_rules}}
		// nested rules for eager objects
		{{rules_eager}}
		
		return $rules;
	}*/
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

	/*// delete model by id
	public function destroy($id)
	{	
		$ids = explode(",", $id);

	    $ids = array_unique($ids);

		$success = {{class_name}}::whereIn('id', $ids)->delete();
		$status = array("error" => true, "message" => "Error deleting object");
		if ($success)
			$status = array("error" => false, "message" => "Object successfully deleted");

		return json_encode($status);
	}*/
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
	/*public function query(Request $request)
	{
		// get query parameters
		$params = $request->all();

		// get pagination conditions
		if(isset($params["pagination"])) {
			$pagination = $params["pagination"];
		}
		else { // set default 
			$pagination =  ["actual" => 1, "itensPerPage" => 25 ] ;
		}

		// resolve current page
		$currentPage = $pagination["actual"];
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});

		// set first condition 1=1 (all results)
		$query = {{class_name}}::WhereNotNull('{{table_name}}.{{primary_key}}');

		// get filters conditions
		$filters = isset($params["filters"]) ? $params["filters"] : $params;
		
		// field by field condition
		{{conditions}}

		// join eager objects
		{{eager_joins}}

		// eager object condition
		{{eager_conditions}}

		// join relationship tables objects
		{{relationship_tables_joins}}
		// get sort clauses
		if(isset($params["sort"]) && count($params["sort"])) {
			foreach($params["sort"] as $sort){
				{{relationship_tables_joins_sort}} 
				$query->orderBy($sort["field"], $sort["order"]);
			}
		}


		
		return $query->paginate($pagination["itensPerPage"]);
	}*/

	// query with search and pagination options
	public function query(Request $request)
	{
		$model = new {{class_name}}();

		$query = $model->querySearch($request);

		return $query;
	}


	// query with fields filters and pagination
	//json = {"pagination": {"actual": 1, "itensPerPage": 20}, "fields": ["name","email","cnpj"], "orderBy": "name"}
	/*public function queryFilters(Request $request)
	{
		// get query parameters
		$params = $request->all();


		$orderBy = '{{primary_key}}';
		$fields = null;
		$selectArray = [];
		array_push($selectArray,'{{primary_key}}');

		// get pagination conditions
		if(isset($params["pagination"])) {
			$pagination = $params["pagination"];
		}
		else { // set default 
			$pagination =  ["actual" => 1, "itensPerPage" => 25 ] ;
		}

		// resolve current page
		$currentPage = $pagination["actual"];
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});

		if(isset($params["fields"])) {
			$fields = $params["fields"];

			foreach ($fields as $field) {
				if(Schema::hasColumn('{{table_name}}', $field)){
					array_push($selectArray, $field);
				}
			}

		}

		if(isset($params["orderBy"])) {
			if(Schema::hasColumn('{{table_name}}',$params["orderBy"])){
				$orderBy = $params["orderBy"];
			}
		}


		${{class_name_lw}} = {{class_name}}::WhereNotNull('{{table_name}}.{{primary_key}}');

		${{class_name_lw}}->select($selectArray)->orderBy($orderBy,'asc');
		
		/*if($orderBy != null)
		{
			${{class_name_lw}} = {{class_name}}::select($selectArray)->orderBy($orderBy,'asc')->get();
		} else {
			${{class_name_lw}} = {{class_name}}::select($selectArray)->get();
		}
		

		// set first condition 1=1 (all results)
		//$query = {{class_name}}::WhereNotNull('{{table_name}}.{{primary_key}}');

		return ${{class_name_lw}}->paginate($pagination["itensPerPage"]);
	}*/
	// query with fields filters and pagination
	//json = {"pagination": {"actual": 1, "itensPerPage": 20}, "fields": ["name","email","cnpj"], "orderBy": "name"}
	public function queryFilters(Request $request)
	{
		$model = new {{class_name}}();

		${{class_name_lw}} = $model->queryFilters($request);
		
		return ${{class_name_lw}};
	}

	
}
