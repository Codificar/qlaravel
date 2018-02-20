<?php

namespace Scaffolder\Compilers\Core;

use Illuminate\Support\Facades\File;
use Scaffolder\Compilers\AbstractCompiler;
use Scaffolder\Compilers\Support\FileToCompile;
use Scaffolder\Compilers\Support\PathParser;
use Scaffolder\Support\CamelCase;

class ControllerCompiler extends AbstractCompiler
{	
	protected $cachePrefix 	= 'controller_';
	protected $stubFilename = 'Controller/Controller.php' ;
	
	public function __construct($scaffolderConfig, $modelData = null, $stubName = null)
	{
		if ($stubName)
			$this->stubFilename = $stubName;
		$this->stubsDirectory = __DIR__ . '/../../../../stubs/Api/';
		parent::__construct($scaffolderConfig, $modelData);
	}

	/**
	 * Replace and store the Stub.
	 *
	 * @return string
	 */
	public function replaceAndStore()
	{
		
		return $this->replacePrimaryKey()
					->replaceEagerCode()
					->replaceUniqueRules()
					->replaceSearchConditions()
					->replaceSimpleFilter()
					->replaceSortConditions()
					->replaceRoutePrefix()
					->replaceReverseRelationships()
					->replaceCheckbox()
					->replaceEnum()
					->replaceRelationshipTables()
					->store(new FileToCompile(false, $this->modelData->modelHash));
		
	}


	/**
	 * Get output filename
	 *
	 *
	 * @return $this
	 */
	protected function getOutputFilename()
	{

		return PathParser::parse($this->scaffolderConfig->generator->paths->controllers) . $this->modelName . 'Controller.php';
	}

	/**
	 * Replace eager code for each foreing key with eager = true 
	 *
	 * @return $this
	 */
	private function replaceEagerCode()
	{
		$storeCommands = $updateCommands = $ruleCommands = $useCommands = '';
		$eagerConditions = $eagerJoins = '';

		$storeEagerStubOriginal = File::get($this->stubsDirectory . 'StoreEager.php');
		$updateEagerStubOriginal = File::get($this->stubsDirectory . 'UpdateEager.php');
		$rulesEagerStubOriginal = File::get($this->stubsDirectory . 'RulesEager.php');
		$useEagerStubOriginal = File::get($this->stubsDirectory . 'UseEager.php');
		$joinEagerStubOriginal = File::get($this->stubsDirectory . 'SearchConditions/JoinEager.php');

		foreach ($this->modelData->fields as $field)
		{
			
			// Check foreign key
			if ($field->foreignKey && isset($field->foreignKey->eager) && $field->foreignKey->eager)
			{

				$storeCommands 	.= str_replace('{{foreign_model_name}}', CamelCase::convertToCamelCase($field->foreignKey->table), $this->replaceForeingStrings($field, $storeEagerStubOriginal));
				$updateCommands	.= str_replace('{{foreign_model_name}}', CamelCase::convertToCamelCase($field->foreignKey->table), $this->replaceForeingStrings($field, $updateEagerStubOriginal)) ;
				$ruleCommands	.= str_replace('{{foreign_model_name}}', CamelCase::convertToCamelCase($field->foreignKey->table), $this->replaceForeingStrings($field, $rulesEagerStubOriginal)) ;
				$useCommands	.= str_replace('{{foreign_model_name}}', CamelCase::convertToCamelCase($field->foreignKey->table), $this->replaceForeingStrings($field, $useEagerStubOriginal)) ;
				$eagerJoins		.= $this->replaceForeingStrings($field, $joinEagerStubOriginal) ;

				// search eager fields
				$foreignModelData = $this->getModelData($field->foreignKey->table);
				$foreignControllerCompiler = new ControllerCompiler($this->scaffolderConfig, $foreignModelData);
				$foreignControllerCompiler->setEagerTable($this->modelData->tableName);
				$eagerConditions 	.= $foreignControllerCompiler->getSearchConditions();
				$eagerUniqueRules = $foreignControllerCompiler->getEagerUniqueRules();
				$eagerUniqueRules = str_replace("{{class_name_lw}}", strtolower($this->modelName), $eagerUniqueRules);
				$eagerUniqueRules = str_replace("{{field}}", $field->name, $eagerUniqueRules);
				$ruleCommands = str_replace('{{unique_eager_rules}}', $eagerUniqueRules, $ruleCommands) ;
			}

		}

		$this->stub = str_replace('{{store_eager_objects}}', $storeCommands, $this->stub);
		$this->stub = str_replace('{{update_eager_objects}}', $updateCommands, $this->stub);
		$this->stub = str_replace('{{rules_eager}}', $ruleCommands, $this->stub);
		$this->stub = str_replace('{{eager_use_classes}}', $useCommands, $this->stub);
		$this->stub = str_replace('{{eager_joins}}', $eagerJoins, $this->stub);
		$this->stub = str_replace('{{eager_conditions}}', $eagerConditions, $this->stub);
		$this->stub = str_replace('{{eager_table}}', $this->eagerTable, $this->stub);
		


		return $this;
	}


	public function replaceSortConditions()	{
		$joinSorts = '';

		foreach ($this->modelData->fields as $field) {
			if($field->foreignKey){
				$joinSortStub = File::get($this->stubsDirectory . 'SearchConditions/JoinSort.php');
				$joinSortStub = str_replace('{{field}}', $field->name, $joinSortStub);
				$joinSortStub = str_replace('{{foreign_table}}', $field->foreignKey->table, $joinSortStub);
				$joinSortStub = str_replace('{{foreign_key}}', $field->foreignKey->table, $joinSortStub);
				$joinSorts .= $joinSortStub;

			}
			
	
			
		}
		

		$this->stub = str_replace('{{relationship_tables_joins_sort}}', $joinSorts, $this->stub);

		return $this;
	}

	public function replaceUniqueRules() {
		$uniqueRules = '';

		foreach ($this->modelData->fields as $field) {
			if (strpos($field->validations, 'unique')) {
				$rule = sprintf('$rules["%s"] = $rules["%s"] . \',%s,\' . $id;', $field->name, $field->name, $field->name);
				$uniqueRules .= $rule . "\n";
			}
		}

		$this->stub = str_replace('{{unique_rules}}', $uniqueRules, $this->stub);

		return $this;
	}

	public function getEagerUniqueRules() {
		$uniqueRules = '';

		foreach ($this->modelData->fields as $field) {
			if (strpos($field->validations, 'unique')) {
				$rule = sprintf('$rules["%s.%s"] .= \',%s,\' . ${{class_name_lw}}->{{field}};', $this->modelData->tableName, $field->name, $field->name);
				$uniqueRules .= $rule . "\n";
			}
		}

		return $uniqueRules;
	}

	/**
	 * Replace search conditions
	 *
	 * @return $this
	 */
	private function replaceSearchConditions(){

		$searchConditions = $this->getSearchConditions();

		$this->stub = str_replace('{{conditions}}', $searchConditions, $this->stub);

		return $this;
	}

	/**
	 * get search conditions
	 *
	 * @return $this
	 */
	public function getSearchConditions(){

		$fieldConditions = '';

		$searchConditions = File::get($this->stubsDirectory . '/SearchConditions/Conditions.php');

		foreach ($this->modelData->fields as $field)
		{

			$fieldConditions .= $this->replaceFieldStrings($field, $this->getConditionStubByField($field)) ;

		}

		// replace all field conditions
		$searchConditions = str_replace('{{field_conditions}}', $fieldConditions, $searchConditions);

		// replace table name
		$searchConditions = str_replace('{{table_name}}', $this->modelData->tableName, $searchConditions);

		return $searchConditions ;

	}


	/**
	 * Replace simple filter
	 *
	 * @return $this
	 */
	private function replaceSimpleFilter(){
		$i = 0;

		$stubSimpleFilter = '';

		foreach ($this->modelData->fields as $field)
		{
			//var_dump($field->name);
			
			if($field->index == 'primary'){
				$dbType = 'primaryKey' ;
			}
			elseif($field->foreignKey){
				$dbType = 'primary' ;
			}
			elseif($field->type->db == 'enum'){
				$dbType = 'primary' ;
			}
			elseif($field->type->db == 'boolean'){
				$dbType = 'primary' ;
			}
			elseif($field->type->db == 'text'){
				$dbType = 'string' ;
			}
			else {
				$dbType = $field->type->db ;
			}
			
			if($dbType == 'primaryKey')
			{
				$stubSimpleFilter .= '$query->where("'.$this->modelData->tableName.'.'.$field->name.'", "=", $'.$this->modelData->tableName.'Conditions["'.$field->name.'"])'.PHP_EOL;
			}

			if($dbType == 'primary')
			{
				$stubSimpleFilter .= '->orWhere("'.$this->modelData->tableName.'.'.$field->name.'", "=", $'.$this->modelData->tableName.'Conditions["'.$field->name.'"])'.PHP_EOL;
			}

			if($dbType == 'string')
			{
				$stubSimpleFilter .= '->orWhere("'.$this->modelData->tableName.'.'.$field->name.'", "LIKE", "%".$'.$this->modelData->tableName.'Conditions["'.$field->name.'"]."%")'.PHP_EOL;
			}

			if($dbType == 'date' || $dbType == 'datetime' || $dbType == 'float' || $dbType == 'integer' || $dbType == 'number')
			{
				$stubSimpleFilter .= '->orWhere("'.$this->modelData->tableName.'.'.$field->name.'", "=", $'.$this->modelData->tableName.'Conditions["'.$field->name.'"])'.PHP_EOL;
			}

		}
		$stubSimpleFilter .= ';';
		$this->stub = str_replace('{{simple_filter}}', $stubSimpleFilter, $this->stub);

		return $this;
	}


	/**
	 * replace reverse relationships
	 *
	 * @return $this
	 */
	public function replaceReverseRelationships(){

		$functions = '';

		$method = File::get($this->stubsDirectory . '/Controller/ControllerReverseRelationship.php');
		if(isset($this->modelData->reverseRelationships)){
			foreach ($this->modelData->reverseRelationships as $relationship)
			{
				$functionName = '';
				if ($relationship->type == "hasOne")
					$functionName = strtolower($relationship->modelName);
				elseif ($relationship->type == "belongsToMany") 
					$functionName = CamelCase::pluralize(strtolower($relationship->relatedTable));
				else 
					$functionName = CamelCase::pluralize(strtolower($relationship->modelName));

				$replacedMethod = '';
				$replacedMethod = str_replace('{{function_name}}', $functionName, $method);
				$replacedMethod = str_replace('{{class_name_lw}}', $this->modelData->tableName, $replacedMethod);
				$replacedMethod = str_replace('{{class_name}}', ucwords($this->modelData->tableName), $replacedMethod);

				$functions .= $replacedMethod;
			}
		}

		$this->stub = str_replace('{{reverseRelationships}}', $functions, $this->stub);

		return $this;

	}

	/**
	 * replace enum fields
	 *
	 * @return $this
	 */
	public function replaceEnum(){

		$functions = '';

		$method = File::get($this->stubsDirectory . '/Controller/ControllerEnum.php');

		foreach ($this->modelData->fields as $field)
		{
			if ($field->type->db == "enum") {
				$replacedMethod = '';
				$replacedMethod = str_replace('{{field_name_uc}}', CamelCase::convertToCamelCase($field->name), $method);	
				$replacedMethod = str_replace('{{field_name}}', $field->name, $replacedMethod);
				$replacedMethod = str_replace('{{model_name}}', $this->modelData->modelName, $replacedMethod);
				
				$functions .= $replacedMethod;
			}
		}

		$this->stub = str_replace('{{enum}}', $functions, $this->stub);

		return $this;

	}

	/**
	 * replace relationship tables
	 *
	 * @return $this
	 */
	public function replaceRelationshipTables() {

		$functions = "";
		$functionsCall = "";
		$removeAll = "";
		$removeAllCall = "";
		$includes = "";
		$joins = "";
		$joinSorts = "";

		$method = File::get($this->stubsDirectory . '/Controller/ControllerRelationshipTable.php');
		if(isset($this->modelData->reverseRelationships)){
			foreach ($this->modelData->reverseRelationships as $relationship) {

				if ($relationship->type == "belongsToMany") {
					$relatedTablePluralized = CamelCase::pluralize($relationship->relatedTable);
					$relatedTablePluralizedUc = CamelCase::pluralize(CamelCase::convertToCamelCase($relationship->relatedTable));

					$replacedMethod = '';
					$replacedMethod = str_replace('{{related_table_pl_uc}}', $relatedTablePluralizedUc, $method);
					$replacedMethod = str_replace('{{class_name_lw}}', $this->modelData->tableName, $replacedMethod);
					$replacedMethod = str_replace('{{related_table_pl}}', $relatedTablePluralized, $replacedMethod);
					$replacedMethod = str_replace('{{foreign_key}}', $relationship->foreignKey, $replacedMethod);
					$replacedMethod = str_replace('{{related_field}}', $relationship->relatedField, $replacedMethod);
					$replacedMethod = str_replace('{{foreign_table_lw}}', strtolower($relationship->modelName), $replacedMethod);
					$replacedMethod = str_replace('{{foreign_table}}', $relationship->modelName, $replacedMethod);

					$functions .= $replacedMethod;

					$methodCall = 'if (array_key_exists(\'{{related_table_pl}}\', $vars))';
					$methodCall .= "\n\t\t\t";
					$methodCall .= '$this->save{{related_table_pl_uc}}($vars, ${{class_name_lw}});';
					$methodCall = str_replace('{{related_table_pl_uc}}', $relatedTablePluralizedUc, $methodCall);
					$methodCall = str_replace('{{related_table_pl}}', $relatedTablePluralized, $methodCall);
					$methodCall = str_replace('{{class_name_lw}}', $this->modelData->tableName, $methodCall);

					$functionsCall .= $methodCall . "\n\t\t";
					
					$removeAllMethod = File::get($this->stubsDirectory . '/Controller/ControllerRemoveAll.php');
					$removeAllMethod = str_replace('{{related_table_pl_uc}}', $relatedTablePluralizedUc, $removeAllMethod);
					$removeAllMethod = str_replace('{{foreign_key}}', $relationship->foreignKey, $removeAllMethod);
					$removeAllMethod = str_replace('{{foreign_table}}', $relationship->modelName, $removeAllMethod);
					$removeAllMethod = str_replace('{{foreign_table_lw_pl}}', CamelCase::pluralize(strtolower($relationship->modelName)), $removeAllMethod);
					
					$removeAll .= $removeAllMethod;

					$removeAllCallMethod = '$this->deleteAll{{related_table_pl_uc}}(${{class_name_lw}}[\'id\']);';
					$removeAllCallMethod = str_replace('{{related_table_pl_uc}}', $relatedTablePluralizedUc, $removeAllCallMethod);
					$removeAllCallMethod = str_replace('{{class_name_lw}}', $this->modelData->tableName, $removeAllCallMethod);
					
					$removeAllCall .= $removeAllCallMethod . "\n\t\t";

					$joinRelationshipTableStub = File::get($this->stubsDirectory . 'SearchConditions/joinRelationshipTable.php');
					$joinRelationshipTableStub = str_replace('{{class_name_lw}}', $this->modelData->tableName, $joinRelationshipTableStub);
					$joinRelationshipTableStub = str_replace('{{related_table_pl}}', $relatedTablePluralized, $joinRelationshipTableStub);
					$joinRelationshipTableStub = str_replace('{{related_table}}', CamelCase::convertToCamelCase($relationship->relatedTable), $joinRelationshipTableStub);
					$joinRelationshipTableStub = str_replace('{{foreign_key}}', $relationship->foreignKey, $joinRelationshipTableStub);
					$joinRelationshipTableStub = str_replace('{{related_field}}', $relationship->relatedField, $joinRelationshipTableStub);
					$joinRelationshipTableStub = str_replace('{{foreign_table}}', $relationship->tableName, $joinRelationshipTableStub);

					$joins .= $joinRelationshipTableStub . "\n";

					$use = 'use App\Models\{{foreign_table}};';
					$use = str_replace('{{foreign_table}}', $relationship->modelName, $use);

					$includes .= $use . "\n";
				}
			}
		}

		$this->stub = str_replace('{{relationship_tables_store}}', $functions, $this->stub);

		$this->stub = str_replace('{{relationship_tables_call}}', $functionsCall, $this->stub);

		$this->stub = str_replace('{{remove_relationship_objects}}', $removeAll, $this->stub);

		$this->stub = str_replace('{{remove_relationship_objects_call}}', $removeAllCall, $this->stub);

		$this->stub = str_replace('{{relationship_tables_classes}}', $includes, $this->stub);

		$this->stub = str_replace('{{relationship_tables_joins}}', $joins, $this->stub);

		
		
		return $this;

	}


	/**
	 * replace checkbox fields
	 *
	 * @return $this
	 */
	public function replaceCheckbox(){

		$method = File::get($this->stubsDirectory . '/Controller/ControllerCheckbox.php');
		$key = false;

		$method = str_replace('{{class_name_lw}}', $this->modelData->tableName, $method);
		$method = str_replace('{{class_name}}', ucwords($this->modelData->tableName), $method);
		$method = str_replace('{{model_name}}', $this->modelData->modelName, $method);
		$this->stub = str_replace('{{checkbox}}', $method, $this->stub);

		/*foreach ($this->modelData->fields as $field)
		{
			if ($field->type->ui == "checkbox") {
				
				$method = str_replace('{{class_name_lw}}', $this->modelData->tableName, $method);
				$method = str_replace('{{class_name}}', ucwords($this->modelData->tableName), $method);

				$this->stub = str_replace('{{checkbox}}', $method, $this->stub);



				$key = true;
			}
		}

		if(!$key)
		{
			$this->stub = str_replace('{{checkbox}}', ' ', $this->stub);
		}*/
		

		return $this;

	}


	/**
	 * get search conditions stub by db type
	 *
	 * @param string $field
	 *
	 * @return $this
	 */
	private $conditionsStub = [];
	private function getConditionStubByField($field){
		
		if($field->index == 'primary'){
			$dbType = 'primary' ;
		}
		elseif($field->foreignKey){
			$dbType = 'primary' ;
		}
		elseif($field->type->db == 'enum'){
			$dbType = 'primary' ;
		}
		elseif($field->type->db == 'boolean'){
			$dbType = 'primary' ;
		}
		elseif($field->type->db == 'text'){
			$dbType = 'string' ;
		}
		else {
			$dbType = $field->type->db ;
		}

		if(array_key_exists($dbType, $this->conditionsStub)){
			return $this->conditionsStub[$dbType];
		}
		else {
			$this->conditionsStub[$dbType] = File::get($this->stubsDirectory . 'SearchConditions/'. ucwords($dbType). '.php');;

			return $this->conditionsStub[$dbType];
		}
	}
	


}