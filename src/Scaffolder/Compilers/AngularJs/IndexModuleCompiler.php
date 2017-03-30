<?php

namespace Scaffolder\Compilers\AngularJs;

use Illuminate\Support\Facades\File;
use Scaffolder\Compilers\AbstractCompiler;
use Scaffolder\Compilers\Support\FileToCompile;
use Scaffolder\Compilers\Support\PathParser;
use Scaffolder\Support\CamelCase;

class IndexModuleCompiler extends AbstractCompiler
{
	protected $stubFilename = 'IndexModule.js' ;

	protected $stubResourceFilename = 'IndexModuleModel.js' ;
	protected $stubResource  ;


	public function __construct($scaffolderConfig, $modelData = null)
	{
		$this->stubsDirectory = __DIR__ . '/../../../../stubs/AngularJs/';
		parent::__construct($scaffolderConfig, null);
		
		$this->stubResource = File::get($this->stubsDirectory . $this->stubResourceFilename );
	}

	/**
	 * Replace and store the Stub.
	 *
	 * @return string
	 */
	public function replaceAndStore(){}

	/**
	 * Compiles a resource.
	 *
	 * @param      $hash
	 * @param null $extra
	 *
	 * @return string
	 */
	public function compile($extra = null) {}

	/**
	 * Compiles a group of routes.
	 *
	 * @param      $hash
	 * @param null $extra
	 *
	 * @return mixed
	 */
	public function compileGroup($compiledIndexes)
	{

		$this->replaceIndexes($compiledIndexes)
			->store(new FileToCompile(null, null));

		return $this->stub;
	}


	/**
	 * Get output filename
	 *
	 *
	 * @return $this
	 */
	protected function getOutputFilename()
	{
		$folder = PathParser::parse($this->scaffolderConfig->generator->paths->index);

		return $folder  . 'index.module.js';
	}


	/**
	 * Replace the resource.
	 *
	 * @param $this->modelName
	 *
	 * @return string routeStub
	 */
	public function replaceResource($modelData)
	{
		
		$indexStub = str_replace('{{table_name_uc}}', $modelData->modelName, $this->stubResource);
		$indexStub = str_replace('{{table_name}}', $modelData->tableName, $indexStub);
		
		return $indexStub;
	}

	/**
	 * Replace compiled routes.
	 *
	 * @param $compiledRoutes
	 *
	 * @return $this
	 */
	private function replaceIndexes($compiledIndexes)
	{
		$this->stub = str_replace('{{tables}}', $compiledIndexes, $this->stub);

		return $this;
	}

	
}