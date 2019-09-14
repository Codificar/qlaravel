<?php

namespace Scaffolder\Compilers\Core;

use Illuminate\Support\Facades\File;
use Scaffolder\Compilers\AbstractCompiler;
use Scaffolder\Compilers\Support\FileToCompile;
use Scaffolder\Compilers\Support\PathParser;
use Scaffolder\Support\Directory;
use Scaffolder\Support\CamelCase;

class FormRequestCompiler extends AbstractCompiler
{
	protected $cachePrefix 	= 'form_request_';
	protected $stubFilename = 'FormRequest/FormRequest.php' ;

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
				->addRules()
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
		$folder = PathParser::parse($this->scaffolderConfig->generator->paths->formrequests) ;

		return $folder .  $this->modelName . 'Request.php';
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

		$this->stub = str_replace('{{validations}}', $fields, $this->stub);

		return $this;
	}
}