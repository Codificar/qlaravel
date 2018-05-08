<?php

namespace Scaffolder\Compilers\Core;

use Illuminate\Support\Facades\File;
use Scaffolder\Compilers\AbstractCompiler;
use Scaffolder\Compilers\Support\FileToCompile;
use Scaffolder\Compilers\Support\PathParser;
use Scaffolder\Support\Directory;
use Scaffolder\Support\CamelCase;

class ModelCompiler extends AbstractCompiler
{
	protected $cachePrefix 	= 'model_';
	protected $stubFilename = 'Model/Model.php' ;

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
		
		return $this->replaceNamespace()
				->replaceNamespaceModelExtend()
				->setPrimaryKey()
				->setTimeStamps()
				->addFillable()
				->addEnumFields()
				->addRules()
				->addBelongsTo()
				->addReverseRelationship()
				->replaceEagerCode()
				->replaceRelationshipTables()
				->replaceSearchConditions()
				->replaceSimpleFilter()
				->replaceSortConditions()
				->replaceReverseRelationshipsFunctions()
				->replaceCheckbox()
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
		$folder = PathParser::parse($this->scaffolderConfig->generator->paths->models) ;

		return $folder .  $this->modelName . '.php';
	}

	

	/**
	 * Replace the namespace which the model extends
	 *
	 * @param $this->scaffolderConfig
	 *
	 * @return $this
	 */
	private function replaceNamespaceModelExtend()
	{
		$this->stub = str_replace('{{namespace_model_extend}}', $this->scaffolderConfig->generator->inheritance->model, $this->stub);

		return $this;
	}

	/**
	 * Add fillable.
	 *
	 * @return $this
	 */
	private function addFillable()
	{
		$fields = '';
		$firstIteration = true;

		foreach ($this->modelData->fields as $field)
		{
			if($field->index == "primary")
				continue ;
			if(isset($this->modelData->timeStamps) && $this->modelData->timeStamps && $field->name == "created_at")
				continue ;
			if(isset($this->modelData->timeStamps) && $this->modelData->timeStamps && $field->name == "updated_at")
				continue ;

			if ($firstIteration)
			{
				$fields .= sprintf("'%s'," . PHP_EOL, $field->name);
				$firstIteration = false;
			}
			else
			{
				$fields .= sprintf("\t\t'%s'," . PHP_EOL, $field->name);
			}
		}

		$this->stub = str_replace('{{fillable}}', $fields, $this->stub);

		return $this;
	}

	private function addEnumFields() {

		$items = '';
		$arrays = '';
		
		foreach ($this->modelData->fields as $field) {
			$enumStub = File::get($this->stubsDirectory . '/Model/ModelEnum.php');

			if ($field->type->db == "enum") {
				$items = '';
				
				foreach ($field->options as $key => $option) {
					$items .= "'" . $option . "'";
					if ($key < (count($field->options) - 1))
						$items .= ", ";

				}


				$enumStub = str_replace('{{field_options}}', $items, $enumStub);
				$enumStub = str_replace('{{field_name}}', $field->name, $enumStub);	
					
				$arrays .= $enumStub;

				
			}
		}

		$this->stub = str_replace('{{enum}}', $arrays, $this->stub);

		return $this;
	}

	/**
	 * Set validations.
	 *
	 *
	 * @return $this
	 */
	private function addRules()
	{
		$fields = '';
		$firstIteration = true;

		foreach ($this->modelData->fields as $field)
		{
			if($field->index == "primary")
				continue ;
			if(isset($this->modelData->timeStamps) && $this->modelData->timeStamps && $field->name == "created_at")
				continue ;
			if(isset($this->modelData->timeStamps) && $this->modelData->timeStamps && $field->name == "updated_at")
				continue ;

			if ($firstIteration)
			{
				$fields .= sprintf("'%s' => '%s'," . PHP_EOL, $field->name, $field->validations);
				$firstIteration = false;
			}
			else
			{
				$fields .= sprintf("\t\t\t'%s' => '%s'," . PHP_EOL, $field->name, $field->validations);
			}
		}

		$fields = str_replace('unique','unique:'.$this->modelData->tableName,$fields);
		//var_dump($fields);

		$this->stub = str_replace('{{validations}}', $fields, $this->stub);

		return $this;
	}

	/**
	 * Set the timestamps value.
	 *
	 */
	private function setTimeStamps()
	{
		if(isset($this->modelData->timeStamps) && $this->modelData->timeStamps)
		{
			$this->stub = str_replace('{{timestamps}}', ' ', $this->stub);
		} else {
			$this->stub = str_replace('{{timestamps}}', 'public $timestamps = false;', $this->stub);
		}

		return $this;
	}

	/**
	 *  Set the primary key.
	 *
	 */
	private function setPrimaryKey()
	{
		$primaryKey = '// Using default primary key' . PHP_EOL;

		$field = $this->getPrimaryKeyField() ;

		$primaryKey = 'protected $primaryKey = \'' . $field->name . '\';' . PHP_EOL;

		$this->stub = str_replace('{{primaryAttribute}}', $primaryKey, $this->stub);

		return $this;
	}

	/**
	 * Add belongsTo Relationships.
	 *
	 *
	 * @return $this
	 */
	private function addBelongsTo()
	{
		$functions = '';

		$eagerArray = [];
		
		foreach ($this->modelData->fields as $field)
		{
			
			// Check foreign key
			if (isset($field->foreignKey->relationship))
			{
				$belongsToStub = "";
				$functionName = $field->foreignKey->table;

				if ($field->foreignKey->relationship == "belongsTo") {
					$belongsToOriginalStub = File::get($this->stubsDirectory . '/Model/ModelBelongsTo.php');
					$belongsToStub = str_replace('{{foreign_model}}', CamelCase::convertToCamelCase($field->foreignKey->table), $belongsToOriginalStub);
					$belongsToStub = str_replace('{{field}}', $field->name, $belongsToStub);
					$belongsToStub = str_replace('{{foreign_field}}', $field->foreignKey->field, $belongsToStub);
				}
				elseif ($field->foreignKey->relationship == "belongsToMany") {
					$belongsToOriginalStub = File::get($this->stubsDirectory . '/Model/ModelBelongsToMany.php');
					$functionName = CamelCase::pluralize($field->foreignKey->table);
					$belongsToStub = str_replace('{{foreign_model}}', CamelCase::convertToCamelCase($field->foreignKey->table), $belongsToOriginalStub);
					$belongsToStub = str_replace('{{foreign_key}}', $field->name, $belongsToStub);
					$belongsToStub = str_replace('{{related_field}}', $field->foreignKey->field, $belongsToStub);
					$belongsToStub = str_replace('{{table_name}}', $field->foreignKey->table, $belongsToStub);
				}
				
				$belongsToStub = str_replace('{{foreign_table}}', $functionName, $belongsToStub);
				$belongsToStub = str_replace('{{model_namespace}}', $this->scaffolderConfig->generator->namespaces->models, $belongsToStub);
				
				$functions .= $belongsToStub ;

				if(isset($field->foreignKey->eager) && $field->foreignKey->eager){
					array_push($eagerArray, "'".$field->foreignKey->table."'");
				}
			}

			
		}

		$this->stub = str_replace('{{belongsTo}}', $functions, $this->stub);

		$this->stub = str_replace('{{eager}}', join("," , $eagerArray) , $this->stub);

		return $this;
	}

	private function addReverseRelationship()
	{
		$functions = '';

		$eagerArray = [];
		
		if(isset($this->modelData->reverseRelationships)){
			foreach ($this->modelData->reverseRelationships as $relationship)
			{
				
				// Check foreign key
				if ($relationship->foreignKey)
				{
					$reverseRelationshipStub = "";
					$functionName = '';
					if ($relationship->type == "hasOne")
						$functionName = strtolower($relationship->modelName);
					else 
						$functionName = CamelCase::pluralize(strtolower($relationship->modelName));
					
					if ($relationship->type == "belongsToMany") {
						$reverseRelationshipOriginalStub = File::get($this->stubsDirectory . '/Model/ModelBelongsToMany.php');
						$reverseRelationshipStub = str_replace('{{foreign_model}}', CamelCase::convertToCamelCase($relationship->relatedTable), $reverseRelationshipOriginalStub);
						$reverseRelationshipStub = str_replace('{{foreign_key}}', $relationship->foreignKey, $reverseRelationshipStub);
						$reverseRelationshipStub = str_replace('{{table_name}}', $relationship->tableName, $reverseRelationshipStub);
						$reverseRelationshipStub = str_replace('{{related_field}}', $relationship->relatedField, $reverseRelationshipStub);
						$reverseRelationshipStub = str_replace('{{foreign_table}}', CamelCase::pluralize(strtolower($relationship->relatedTable)), $reverseRelationshipStub);
					}
					else {
						$reverseRelationshipOriginalStub = File::get($this->stubsDirectory . '/Model/ModelReverseRelationship.php');
						$reverseRelationshipStub = str_replace('{{foreign_model}}', $relationship->modelName, $reverseRelationshipOriginalStub);
						$reverseRelationshipStub = str_replace('{{field}}', $relationship->foreignKey, $reverseRelationshipStub);
						$reverseRelationshipStub = str_replace('{{foreign_field}}', $relationship->localKey, $reverseRelationshipStub);
						$reverseRelationshipStub = str_replace('{{type}}', $relationship->type, $reverseRelationshipStub);
						$reverseRelationshipStub = str_replace('{{foreign_table}}', $functionName, $reverseRelationshipStub);
					}
						
					$reverseRelationshipStub = str_replace('{{model_namespace}}', $this->scaffolderConfig->generator->namespaces->models, $reverseRelationshipStub);
					
					$functions .= $reverseRelationshipStub ;

					if(isset($relationship->foreignKey->eager) && $relationship->foreignKey->eager){
						array_push($eagerArray, "'".$relationship->foreignKey->table."'");
					}
				}		
			}
		}

		$this->stub = str_replace('{{reverseRelationship}}', $functions, $this->stub);


		return $this;
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

		$method = File::get($this->stubsDirectory . '/Model/ModelRelationshipTable.php');
		
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



	/**
	 * replace reverse relationships
	 *
	 * @return $this
	 */
	public function replaceReverseRelationshipsFunctions(){

		$functions = '';

		$method = File::get($this->stubsDirectory . '/Model/ModelReverseRelationshipFunctions.php');
		
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

		$this->stub = str_replace('{{reverseRelationshipFunctions}}', $functions, $this->stub);

		return $this;

	}


	/**
	 * replace checkbox fields
	 *
	 * @return $this
	 */
	public function replaceCheckbox(){

		$method = File::get($this->stubsDirectory . '/Model/ModelCheckbox.php');
		$key = false;

		$method = str_replace('{{class_name_lw}}', $this->modelData->tableName, $method);
		$method = str_replace('{{class_name}}', ucwords($this->modelData->tableName), $method);
		$method = str_replace('{{model_name}}', $this->modelData->modelName, $method);
		$this->stub = str_replace('{{checkbox}}', $method, $this->stub);

		return $this;

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
				$stubSimpleFilter .= '				->orWhere("'.$this->modelData->tableName.'.'.$field->name.'", "=", $'.$this->modelData->tableName.'Conditions["'.$field->name.'"])'.PHP_EOL;
			}

			if($dbType == 'string')
			{
				$stubSimpleFilter .= '				->orWhere("'.$this->modelData->tableName.'.'.$field->name.'", "LIKE", "%".$'.$this->modelData->tableName.'Conditions["'.$field->name.'"]."%")'.PHP_EOL;
			}

			if($dbType == 'date' || $dbType == 'datetime' || $dbType == 'float' || $dbType == 'integer' || $dbType == 'number')
			{
				$stubSimpleFilter .= '				->orWhere("'.$this->modelData->tableName.'.'.$field->name.'", "=", $'.$this->modelData->tableName.'Conditions["'.$field->name.'"])'.PHP_EOL;
			}

		}
		$stubSimpleFilter .= '				;';
		$this->stub = str_replace('{{simple_filter}}', $stubSimpleFilter, $this->stub);

		return $this;
	}
}