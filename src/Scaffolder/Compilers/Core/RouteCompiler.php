<?php

namespace Scaffolder\Compilers\Core;

use Illuminate\Support\Facades\File;
use Scaffolder\Compilers\AbstractCompiler;
use Scaffolder\Compilers\Support\FileToCompile;
use Scaffolder\Compilers\Support\PathParser;
use Scaffolder\Support\CamelCase;

class RouteCompiler extends AbstractCompiler
{
	protected $stubFilename = 'Routes.php' ;

	protected $stubResourceFilename = 'ResourceRoute.php' ;
	protected $stubResource  ;


	public function __construct($scaffolderConfig, $modelData = null)
	{
		$this->stubsDirectory = __DIR__ . '/../../../../stubs/Api/';
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
	public function compileGroup($compiledRoutes)
	{

		$this->replaceRoutes($compiledRoutes)
			->replaceRoutePrefix()
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
		$folder = PathParser::parse($this->scaffolderConfig->generator->paths->routes);

		return $folder  . 'routes.php';
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
		
		$routeStub = str_replace('{{resource_lw}}', strtolower($modelData->modelName), $this->stubResource);
		$routeStub = str_replace('{{resource}}', $modelData->modelName, $routeStub);
		$routeStub = str_replace('{{reverseRelationships}}', $this->replaceReverseRelationships($modelData), $routeStub);
		$routeStub = str_replace('{{enum}}', $this->replaceEnum($modelData), $routeStub);

		return $routeStub;
	}

	/**
	 * Replace the reverse relationships.
	 *
	 * @param $this->modelData
	 *
	 * @return string functions
	 */
	public function replaceReverseRelationships($modelData)
	{
		$functions = '';
		if(isset($modelData->reverseRelationships) && $modelData->reverseRelationships){
		
		foreach ($modelData->reverseRelationships as $relationship)
		{
			if ($relationship)
			{
				$functionName = '';
				if ($relationship->type == "hasOne")
					$functionName = strtolower($relationship->modelName);
				elseif ($relationship->type == "belongsToMany") 
					$functionName = CamelCase::pluralize(strtolower($relationship->relatedTable));
				else 
					$functionName = CamelCase::pluralize(strtolower($relationship->modelName));

				$method = "\tRoute::get('{{resource_lw}}/{id}/{{function_name}}', '{{resource}}Controller@{{function_name}}');\n";
				$method = str_replace('{{resource_lw}}', strtolower($modelData->modelName), $method);
				$method = str_replace('{{function_name}}', $functionName, $method);
				$method = str_replace('{{resource}}', $modelData->modelName, $method);

				$functions .= $method;
			}
		}
	}
		return $functions;
	}

	/**
	 * Replace the enum.
	 *
	 * @param $this->modelData
	 *
	 * @return string functions
	 */
	public function replaceEnum($modelData)
	{
		$functions = '';

		foreach ($modelData->fields as $field)
		{
			if ($field->type->db == "enum")
			{
				$method = "\tRoute::get('{{resource_lw}}/{{field_name}}', '{{resource}}Controller@get{{field_name_uc}}Options');\n";
				$method = str_replace('{{resource_lw}}', strtolower($modelData->modelName), $method);
				$method = str_replace('{{field_name_uc}}', CamelCase::convertToCamelCase($field->name), $method);
				$method = str_replace('{{field_name}}', $field->name, $method);
				$method = str_replace('{{resource}}', $modelData->modelName, $method);

				$functions .= $method;
			}
		}

		return $functions;
	}


	/**
	 * Replace compiled routes.
	 *
	 * @param $compiledRoutes
	 *
	 * @return $this
	 */
	private function replaceRoutes($compiledRoutes)
	{
		$this->stub = str_replace('{{routes}}', $compiledRoutes, $this->stub);

		return $this;
	}

	
}
