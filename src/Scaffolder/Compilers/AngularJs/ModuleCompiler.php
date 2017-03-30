<?php

namespace Scaffolder\Compilers\AngularJs;

use Illuminate\Support\Facades\File;
use Scaffolder\Compilers\AbstractCompiler;
use Scaffolder\Compilers\Support\FileToCompile;
use Scaffolder\Compilers\Support\PathParser;
use Scaffolder\Support\Directory;

class ModuleCompiler extends AbstractCompiler
{
	protected $cachePrefix 	= 'module_';
	protected $stubFilename = 'Module.js' ;

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
		
		return $this->store(new FileToCompile(false, $this->modelData->modelHash));
		
	}
	
	/**
	 * Get output filename
	 *
	 *
	 * @return $this
	 */
	protected function getOutputFilename()
	{
		$folder = PathParser::parse($this->scaffolderConfig->generator->paths->pages).$this->modelData->tableName.'/' ;

		Directory::createIfNotExists($folder, 0755, true);

		return $folder .  $this->modelData->tableName . '.module.js';
	}

}