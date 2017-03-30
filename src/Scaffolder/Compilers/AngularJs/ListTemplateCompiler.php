<?php

namespace Scaffolder\Compilers\AngularJs;

use Illuminate\Support\Facades\File;
use Scaffolder\Compilers\AbstractCompiler;
use Scaffolder\Compilers\Support\FileToCompile;
use Scaffolder\Compilers\Support\PathParser;
use Scaffolder\Support\Directory;
use Scaffolder\Support\CamelCase;
use Scaffolder\Support\Validator;
use stdClass ;

class ListTemplateCompiler extends AbstractCompiler
{
	protected $cachePrefix 	= 'list_template_';
	protected $stubFilename = 'ListTemplate.html' ;

	public function __construct($scaffolderConfig, $modelData = null)
	{
		$this->stubsDirectory = __DIR__ . '/../../../../stubs/AngularJs/';
		parent::__construct($scaffolderConfig, $modelData);
	}

	/**
	 * Replace and store the Stub.
	 *
	 * @return string
	 */
	public function replaceAndStore()
	{
		
		return $this->replaceInputFields()
					->replaceBelongsToManyFields()
					->store(new FileToCompile(false, $this->modelData->modelHash));
		
	}

	/**
	 * Replace input fields
	 *
	 * @return $this
	 */
	private function replaceInputFields(){

		$inputFields = $this->getInputFields();

		$this->stub = str_replace('{{columns_inputs}}', $inputFields, $this->stub);

		return $this;
	}

	/**
	 * get search conditions
	 *
	 * @return $this
	 */
	public function getInputFields(){

		$inputFields = $eagerFields = '';

		foreach ($this->modelData->fields as $field)
		{
			$fieldStub = $this->getInputStubByField($field);

			if($field->foreignKey){
				$fieldStub 	= $this->replaceForeingStrings($field, $fieldStub) ;
				$fieldStub = str_replace('{{foreign_model_name}}', strtolower(CamelCase::convertToCamelCase($field->foreignKey->table)), $fieldStub);
			}

			$inputFields .= $this->replaceFieldInput($field, $fieldStub) ;

			// Check foreign key
			if ($field->foreignKey && isset($field->foreignKey->eager) && $field->foreignKey->eager)
			{
				// search eager fields
				$foreignModelData = $this->getModelData($field->foreignKey->table);
				$foreignControllerCompiler = new ListTemplateCompiler($this->scaffolderConfig, $foreignModelData);
				$foreignControllerCompiler->setEagerTable($this->modelData->tableName);
				$eagerFields 	.= $foreignControllerCompiler->getInputFields();
			}

		}

		// replace table name
		$inputFields = str_replace('{{table_name}}', $this->modelData->tableName, $inputFields);
		
		$this->stub = str_replace('{{eager_objects_inputs}}', $eagerFields, $this->stub); 

		return $inputFields ;

	}

	/**
	 * replace field stub with fields and validations
	 *
	 * @param string $field
	 * @param string $fieldStub
	 *
	 * @return $this
	 */
	protected function replaceFieldInput($field, $fieldStub){
		$fieldStub = $this->replaceFieldStrings($field, $fieldStub) ;
		$fieldStub = $this->replaceFieldValidations($field, $fieldStub) ;

		return $fieldStub ;
	}

	/**
	 * replace field stub with fields and validations
	 *
	 * @param string $field
	 * @param string $fieldStub
	 *
	 * @return $this
	 */
	protected function replaceFieldValidations($field, $fieldStub){
		$fieldStub = $this->replaceFieldStrings($field, $fieldStub) ;
		
		$validationsConverted = Validator::convertValidations($field->validations, true);

		$inputValidations = '' ; 

		foreach ($validationsConverted as $attribute => $value) {
			if($value)
				$inputValidations .=  ' '.$attribute.'="'. $value.'"' ;
			else
				$inputValidations .=  ' '.$attribute  ; 
		}

		$fieldStub = str_replace('{{field_validation}}', $inputValidations, $fieldStub);

		return $fieldStub ;
	}

	/**
	 * Replace belongs to many fields
	 *
	 * @return $this
	 */
	protected function replaceBelongsToManyFields() {
		
		$belongToManyFields = '';
		
		foreach ($this->modelData->reverseRelationships as $relationship)
		{
			if ($relationship->type == "belongsToMany") {
				$fieldStub = File::get($this->stubsDirectory . 'List/'. CamelCase::convertToCamelCase($relationship->ui). '.html');

				$fieldStub = str_replace('{{related_table}}',CamelCase::convertToCamelCase($relationship->relatedTable), $fieldStub);
				$fieldStub = str_replace('{{related_table_lw}}', strtolower(CamelCase::convertToCamelCase($relationship->relatedTable)), $fieldStub);
				$fieldStub = str_replace('{{table_name}}', $this->modelData->tableName, $fieldStub);
				$fieldStub = str_replace('{{related_table_lw_pl}}', CamelCase::pluralize(strtolower($relationship->relatedTable)), $fieldStub);
				
				$belongToManyFields .= $fieldStub;	
			}
		}

		$this->stub = str_replace('{{belongs_to_many_inputs}}', $belongToManyFields, $this->stub); 

		return $this;
	}

	
	/**
	 * get input field stub by ui type
	 *
	 * @param string $field
	 *
	 * @return $this
	 */
	private $inputStub = [];
	private function getInputStubByField($field){
		
		if($field->index == 'primary'){
			$uiType = 'primary' ;
		}
		elseif(isset($field->foreignKey) && $field->foreignKey){
			if(isset($field->foreignKey->eager) && $field->foreignKey->eager)
				$uiType = 'foreign_eager' ;
			else 
				$uiType = $field->type->ui ;
		}
		else {
			$uiType = $field->type->ui ;
		}

		if(array_key_exists($uiType, $this->inputStub)){
			return $this->inputStub[$uiType];
		}
		else {
			$this->inputStub[$uiType] = File::get($this->stubsDirectory . 'List/'. CamelCase::convertToCamelCase($uiType). '.html');

			return $this->inputStub[$uiType];
		}
	}
	
	/**
	 * Get output filename
	 *
	 *
	 * @return $this
	 */
	protected function getOutputFilename()
	{
		$folder = PathParser::parse($this->scaffolderConfig->generator->paths->pages).$this->modelData->tableName.'/list/' ;

		Directory::createIfNotExists($folder, 0755, true);

		return $folder .$this->modelData->tableName . '_list.html';
	}

}