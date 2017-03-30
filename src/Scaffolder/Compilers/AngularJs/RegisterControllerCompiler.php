<?php

namespace Scaffolder\Compilers\AngularJs;

use Illuminate\Support\Facades\File;
use Scaffolder\Compilers\AbstractCompiler;
use Scaffolder\Compilers\Support\FileToCompile;
use Scaffolder\Compilers\Support\PathParser;
use Scaffolder\Support\Directory;

class RegisterControllerCompiler extends AbstractCompiler
{
	protected $cachePrefix 	= 'register_controller_';
	protected $stubFilename = 'RegisterController.js' ;

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
		
		return $this
					->replaceFileUpload()
					->replaceNewTags()
					->store(new FileToCompile(false, $this->modelData->modelHash));
		
	}

	public function replaceNewTags()
	{
		foreach ($this->modelData->fields as $field)
		{

			if($field->type->ui == 'autoComplete' || $field->type->ui == 'multipleAutoComplete')
			{
				$this->stub = str_replace('{{table_from}}', $field->table_from, $this->stub);
			}


		}

		return $this;
	}

	private function replaceFileUpload()
	{
		$fileStub = File::get($this->stubsDirectory . '/Register/FileRegisterControllerStub.php');
		$this->stub = str_replace('{{file_upload}}', $fileStub, $this->stub);

		$keyAutoComplete = $keyMultipleAutoComplete = $keyCheckbox = $keyCheckboxTree = false;

		foreach ($this->modelData->fields as $field)
		{

			//Verifica os tipos de UI no model.json e realiza a troca das tags pelas funções JS se necessário
			switch ($field->type->ui) {
				case 'autoComplete':
					$autoStub = $this->changeStubDataAndTag('AutoComplete', '{{auto_complete}}', $field->table_from);
					$keyAutoComplete = true;
					break;
				case 'multipleAutoComplete':
					$multipleAutoStub = $this->changeStubDataAndTag('MultipleAutoComplete','{{multiple_auto_complete}}',$field->table_from);
					$keyMultipleAutoComplete = true;
					break;
				case 'checkbox':
					$checkboxStub = $this->changeStubDataAndTag('Checkbox','{{checkbox}}', $field->table_from);
					$keyCheckbox = true;
					break;
				case 'checkboxTree':
					$checkboxTreeStub = $this->changeStubDataAndTag('CheckboxTree','{{checkbox_tree}}', $field->table_from);
					$keyCheckboxTree = true;
					break;
				default:
					break;
			}

			if($field->foreignKey)
			{
				$this->stub = str_replace('{{foreign_table}}', $field->foreignKey->table, $this->stub);
			}


		}
		
		if(!$keyAutoComplete)
		{
			$this->stub = str_replace('{{auto_complete}}', ' ', $this->stub);
		}

		if(!$keyMultipleAutoComplete)
		{
			$this->stub = str_replace('{{multiple_auto_complete}}', ' ', $this->stub);
		}

		if(!$keyCheckbox)
		{
			$this->stub = str_replace('{{checkbox}}', ' ', $this->stub);
		}			

		if(!$keyCheckboxTree)
		{
			$this->stub = str_replace('{{checkbox_tree}}', ' ', $this->stub);
		}

		return $this;
	}
	

	public function changeStubDataAndTag($stubName,$stubTag,$table_from)
	{
		$stubFile = File::get($this->stubsDirectory . '/Register/'.$stubName.'ControllerStub.php');
		$stubFile = str_replace('{{table_from}}', $table_from, $stubFile);
		$this->stub = str_replace($stubTag, $stubFile, $this->stub);
	}

	/**
	 * Get output filename
	 *
	 *
	 * @return $this
	 */
	protected function getOutputFilename()
	{
		$folder = PathParser::parse($this->scaffolderConfig->generator->paths->pages).$this->modelData->tableName.'/register/' ;

		Directory::createIfNotExists($folder, 0755, true);

		return $folder .$this->modelData->tableName . '_register.controller.js';
	}

}