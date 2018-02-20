<?php

namespace {{namespace}};

use {{namespace_model_extend}} as Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Schema;
// relationship tables use classes
{{relationship_tables_classes}}
// eager use classes
{{eager_use_classes}}

class {{class_name}} extends Model
{
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = '{{table_name}}';
	{{timestamps}}

	{{primaryAttribute}}
	public $fillable = [
		{{fillable}}
	];

	// validation rules 
	public static $rules = 	[
			{{validations}}
	];

	// eager loading objects
	public $with = [
		{{eager}}
	];

	{{enum}}

	{{belongsTo}}

	{{reverseRelationship}}
	

	//api resources



	// save {{class_name_lw}}
	public function store(Request $request)
	{
		$vars = $request->all();

		//create eager objects
		{{store_eager_objects}}

		${{class_name_lw}} = {{class_name}}::create($vars);

		//create relationship_tables objects
		{{relationship_tables_call}}

		return ${{class_name_lw}} ;
	}

	
	// get {{class_name_lw}} by id
	public function show($id)
	{
		${{class_name_lw}} = {{class_name}}::find($id);
		return ${{class_name_lw}};
	}


	
	//get first row by field and value
	public function findByField(Request $request)
	{
		${{class_name_lw}} = {{class_name}}::where($request->input('field') , '=', $request->input('value'))->first();
		return ${{class_name_lw}} ;
	}

	
	// update {{class_name_lw}} by id
	public function updateModel($id, Request $request)
	{
		// get vars
		$vars = $request->all();
		
		//update eager objects
		{{update_eager_objects}}

		${{class_name_lw}} = {{class_name}}::find($id);
		${{class_name_lw}}->fill($vars);
		${{class_name_lw}}->save();

		//update relationship_tables objects
		{{remove_relationship_objects_call}}
		
		{{relationship_tables_call}}

		return ${{class_name_lw}};
	}


	// delete model by id or ids
	public static function destroy($ids)
	{	
		$success = {{class_name}}::whereIn('id', $ids)->delete();
		
		return $success;
	}

	// query with search and pagination options
	public function querySearch(Request $request)
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
	}


	// query with fields filters and pagination
	//json = {"pagination": {"actual": 1, "itensPerPage": 20}, "fields": ["name","email","cnpj"], "orderBy": "name"}
	public function queryFilters(Request $request)
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
		
		return ${{class_name_lw}}->paginate($pagination["itensPerPage"]);
	}

	
	{{relationship_tables_store}}

	{{remove_relationship_objects}}

	{{checkbox}}
}