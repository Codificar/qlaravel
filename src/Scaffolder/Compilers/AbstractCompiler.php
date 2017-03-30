<?php

namespace Scaffolder\Compilers;

use Scaffolder\Compilers\Support\FileToCompile;
use Illuminate\Support\Facades\File;
use Scaffolder\Support\Json;
use stdClass ;

abstract class AbstractCompiler
{
	protected $cachePrefix ;
	protected $stubFilename;

	protected $stub;
	protected $scaffolderConfig ;
	protected $modelName ;
	protected $modelData ;
	protected $stubsDirectory ;

	protected $eagerTable ;

	const CACHE_EXT = '.scf';

	public function __construct($scaffolderConfig, $modelData = null)
	{
		$this->modelName = isset($modelData->modelName) ? $modelData->modelName : null  ;
		$this->modelData = $modelData ;
		$this->scaffolderConfig = $scaffolderConfig ;

		$this->stub = File::get($this->stubsDirectory . $this->stubFilename );
	}

	/**
	 * Compiles .
	 *
	 * @param null $extra
	 *
	 * @return string
	 */
	public function compile($extra = null)
	{
		if (File::exists(base_path('scaffolder-config/cache/' . $this->cachePrefix  . $this->modelData->modelHash . self::CACHE_EXT)))
		{
			return $this->store(new FileToCompile(true, $this->modelData->modelHash));
		}
		else
		{

			return $this->replacePrimaryKey()
						->replaceClassName()
						->replaceTableName()
						->replaceRoutePrefix()
						->replaceAndStore();
		}
	}

	/**
	 * Replace and store the Stub.
	 *
	 * @return string
	 */
	abstract protected function replaceAndStore();

	/**
	 * Store the compiled stub.
	 *
	 * @param string $eagerTable
	 *
	 * @return string
	 */
	protected function setEagerTable($eagerTable){
		$this->eagerTable = $eagerTable.'.';
	}

	/**
	 * Store the compiled stub.
	 *
	 * @param FileToCompile $fileToCompile
	 *
	 * @return string
	 */
	protected function store(FileToCompile $fileToCompile)
	{
		$path = $this->getOutputFilename();

		// Store in cache
		if ($fileToCompile->cached)
		{
			File::copy(base_path('scaffolder-config/cache/' . $this->cachePrefix . $fileToCompile->hash . self::CACHE_EXT), $path);
		}
		else
		{
			File::put(base_path('scaffolder-config/cache/' . $this->cachePrefix . $fileToCompile->hash . self::CACHE_EXT), $this->stub);
			File::copy(base_path('scaffolder-config/cache/' . $this->cachePrefix . $fileToCompile->hash . self::CACHE_EXT), $path);
		}

		return $path;
	}

	/**
	 * Get output filename
	 *
	 *
	 * @return $this
	 */
	abstract protected function getOutputFilename();

	/**
	 * Replace the primary key.
	 *
	 * @param $this->modelData
	 */
	protected function replacePrimaryKey()
	{
		
		$primaryKey = $this->getPrimaryKeyField()->name;

		$this->stub = str_replace('{{primary_key}}', $primaryKey, $this->stub);

		return $this;
	}

	protected function getPrimaryKeyField(){
		$primaryKey = new stdClass;
		$primaryKey->name = "id" ;
		$primaryKey->index = "primary" ;
		$primaryKey->declared =  false ;
		$primaryKey->type = new stdClass ;
		$primaryKey->type->ui = 'label' ;
		$primaryKey->type->db = 'integer' ;
		$primaryKey->foreignKey = [];
		$primaryKey->validations = "required" ;

		foreach ($this->modelData->fields as $field)
		{
			if ($field->index == 'primary')
			{
				$primaryKey = $field ;
				break;
			}
		}

		return $primaryKey ;
	}

	/**
	 * Replace the class name.
	 *
	 *
	 * @return $this
	 */
	protected function replaceClassName()
	{
		$this->stub = str_replace('{{class_name}}', $this->modelName, $this->stub);
		$this->stub = str_replace('{{class_name_lw}}', strtolower($this->modelName), $this->stub);

		return $this;
	}

	/**
	 * Replace the table name.
	 *
	 * @return $this
	 */
	protected function replaceTableName()
	{
		$this->stub = str_replace('{{table_name}}', $this->modelData->tableName, $this->stub);

		return $this;
	}

	/**
	 * Replace the namespace.
	 *
	 * @return $this
	 */
	protected function replaceNamespace()
	{
		$this->stub = str_replace('{{namespace}}', $this->scaffolderConfig->generator->namespaces->models, $this->stub);

		return $this;
	}

	/**
	 * Replace the foreign strings by field.
	 *
	 * @param stdClass $field
	 * @param string $originalStubPart
	 *
	 * @return $this
	 */
	protected function replaceForeingStrings($field,  $originalStubPart){
		$replaceStub = str_replace('{{foreign_table}}', $field->foreignKey->table, $originalStubPart);
		$replaceStub = str_replace('{{table_name}}', $this->modelData->tableName, $replaceStub);
		$replaceStub = str_replace('{{foreign_field}}', $field->foreignKey->field, $replaceStub);
		$replaceStub = str_replace('{{foreign_model}}', ucwords($field->foreignKey->table), $replaceStub);
		$replaceStub = str_replace('{{field}}', $field->name, $replaceStub);

		if(isset($this->scaffolderConfig->generator->namespaces))
			$replaceStub = str_replace('{{model_namespace}}', $this->scaffolderConfig->generator->namespaces->models, $replaceStub);

		return $replaceStub;
		
	}

	/**
	 * Replace the fields strings by field.
	 *
	 * @param stdClass $field
	 * @param string $originalStubPart
	 *
	 * @return $this
	 */
	protected function replaceFieldStrings($field,  $originalStubPart){
		$replaceStub = str_replace('{{field}}', $field->name, $originalStubPart);
		
		if($this->eagerTable) $this->eagerTable.'.' ;

		$replaceStub = str_replace('{{eager_table}}', $this->eagerTable, $replaceStub);

		return $replaceStub;
		
	}

	/**
	 * Replace the prefix.
	 *
	 * @return $this
	 */
	protected function replaceRoutePrefix()
	{
		$this->stub = str_replace('{{route_prefix}}', $this->scaffolderConfig->generator->routing->prefix, $this->stub);

		return $this;
	}

	/**
	 * get anoter model data
	 *
	 * @param string $tableName
	 *
	 * @return $this
	 */
	protected $modelDataArray = [];
	protected function getModelData($tableName){
		
		if(array_key_exists($tableName, $this->modelDataArray)){
			return $this->modelDataArray[$tableName];
		}
		else {

			$modelFilename = base_path('scaffolder-config/models/') . $tableName . '.json' ;

			$modelData = Json::decodeFile($modelFilename);

			// Get model name
			$modelName = ucwords($tableName);

			// Get model hash
			$modelHash = md5_file($modelFilename);

			// Set model name
			$modelData->modelName = $modelName ;

			// Set model name
			$modelData->modelHash = $modelHash ;

			$this->modelDataArray[$tableName] = $modelData;

			return $this->modelDataArray[$tableName];
		}
	}
}