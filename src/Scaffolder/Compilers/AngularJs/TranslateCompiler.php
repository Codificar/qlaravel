<?php

namespace Scaffolder\Compilers\AngularJs;

use Illuminate\Support\Facades\File;
use Scaffolder\Compilers\AbstractCompiler;
use Scaffolder\Compilers\Support\FileToCompile;
use Scaffolder\Compilers\Support\PathParser;
use Scaffolder\Support\Directory;
use Scaffolder\Support\CamelCase;
use stdClass ;


class TranslateCompiler extends AbstractCompiler
{
	protected $cachePrefix 	= 'translate_';
	protected $stubFilename = 'Translate.js' ;

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
					->replaceArea()
					->store(new FileToCompile(false, $this->modelData->modelHash));
		
	}

	/**
	 * replace columns
	 *
	 * @return $this
	 */
	public function replaceArea(){
		$this->stub = str_replace('{{singular}}', ucwords($this->modelData->tableName), $this->stub); 
		$this->stub = str_replace('{{plural}}', ucwords(CamelCase::pluralize($this->modelData->tableName)), $this->stub); 
		return $this;
	}

	/**
	 * replace columns
	 *
	 * @return $this
	 */
	public function replaceColumns(){

		$columns = [];

		foreach ($this->modelData->fields as $field)
		{
			array_push($columns,  "\t\t".sprintf('"%s" : "%s"',  $field->name,  CamelCase::convertToCamelCase($field->name) ));
		}

		$this->stub = str_replace('{{columns}}', join(",\n", $columns), $this->stub); 

		return $this ;

	}
	
	/**
	 * Get output filename
	 *
	 *
	 * @return $this
	 */
	protected function getOutputFilename()
	{
		$folder = PathParser::parse($this->scaffolderConfig->generator->paths->pages).$this->modelData->tableName.'/i18n/' ;

		Directory::createIfNotExists($folder, 0755, true);

		return $folder . 'en.json';
	}

}