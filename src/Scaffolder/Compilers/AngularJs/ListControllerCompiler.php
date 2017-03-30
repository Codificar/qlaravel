<?php

namespace Scaffolder\Compilers\AngularJs;

use Illuminate\Support\Facades\File;
use Scaffolder\Compilers\AbstractCompiler;
use Scaffolder\Compilers\Support\FileToCompile;
use Scaffolder\Compilers\Support\PathParser;
use Scaffolder\Support\Directory;

class ListControllerCompiler extends AbstractCompiler
{
	protected $cachePrefix 	= 'list_controller_';
	protected $stubFilename = 'ListController.js' ;

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
		
		return $this->replaceColumns()
					->store(new FileToCompile(false, $this->modelData->modelHash));
		
	}

	/**
	 * replace columns
	 *
	 * @return $this
	 */
	public function replaceColumns(){

		$columns = $this->getGridColumns();

		$this->stub = str_replace('{{grid_columns}}',  join(",\n ", $columns) , $this->stub); 

		return $this ;

	}

	public function getGridColumns(){

		$columns = [];

		foreach ($this->modelData->fields as $field)
		{
			array_push($columns,  "\t\t\t".sprintf('{ name: vm. $t("%s.columns.%s"), field: "%s%s" }',  $this->modelData->tableName, $field->name, $this->eagerTable, $field->name ) );

			// Check foreign key
			if ($field->foreignKey && isset($field->foreignKey->eager) && $field->foreignKey->eager)
			{
				// search eager fields
				$foreignModelData = $this->getModelData($field->foreignKey->table);
				$foreignControllerCompiler = new ListControllerCompiler($this->scaffolderConfig, $foreignModelData);
				$foreignControllerCompiler->setEagerTable($field->foreignKey->table);
				$eagerColumns 	= $foreignControllerCompiler->getGridColumns();

				$columns = array_merge($columns, $eagerColumns);
			}
		}

		return $columns; 

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

		return $folder .$this->modelData->tableName . '_list.controller.js';
	}

}