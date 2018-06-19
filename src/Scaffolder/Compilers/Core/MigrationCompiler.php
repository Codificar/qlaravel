<?php

namespace Scaffolder\Compilers\Core;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Scaffolder\Compilers\AbstractCompiler;
use Scaffolder\Compilers\Support\FileToCompile;
use Scaffolder\Compilers\Support\PathParser;

class MigrationCompiler extends AbstractCompiler
{
	private $date;

	protected $cachePrefix 	= 'migration_';
	protected $stubFilename = 'Migration.php' ;

	public function __construct($scaffolderConfig, $modelData = null)
	{
		$this->stubsDirectory = __DIR__ . '/../../../../stubs/Api/';

		parent::__construct($scaffolderConfig, $modelData);

		$this->date = Carbon::now();
	}

	/**
	 * Replace and store the Stub.
	 *
	 * @return string
	 */
	public function replaceAndStore()
	{
		
		return $this->addFields()
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

		return  PathParser::parse($this->scaffolderConfig->generator->paths->migrations) . $this->date->format('Y_m_d_') . str_pad($this->modelData->migrationOrder, 2, 0, STR_PAD_LEFT) . '_create_' . strtolower($this->modelName) . '_table.php';
	}

	/**
	 * Add fields.
	 *
	 * @param $modelData
	 *
	 * @return $this
	 */
	private function addFields()
	{
		// Default primary key
		$fields = "\t\t\t\$table->increments('id');" . PHP_EOL . PHP_EOL;

		// Check primary key
		# TODO FIX, primary
		
		foreach ($this->modelData->fields as $field)
		{
			$parsedModifiers = '';

			if($field->index == "primary")
				continue ;
			if($this->modelData->timeStamps && $field->name == "created_at")
				continue ;
			if($this->modelData->timeStamps && $field->name == "updated_at")
				continue ;

			// Check modifiers
			if (!empty($field->modifiers))
			{
				$modifiersArray = explode(':', $field->modifiers);

				foreach ($modifiersArray as $modifier)
				{
					$modifierAndValue = explode(',', $modifier);

					if (count($modifierAndValue) == 2)
					{
						$parsedModifiers .= '->' . $modifierAndValue[0] . '(' . $modifierAndValue[1] . ')';
					}
					else
					{
						$parsedModifiers .= '->' . $modifierAndValue[0] . '()';
					}
				}
			}

			// Check foreign key for unsigned modifier
			if ($field->foreignKey)
			{
				$parsedModifiers .= '->unsigned()';
			}

			// Check indexes
			if ($field->index != 'none')
			{
				$fields .= sprintf("\t\t\t\$table->%s('%s')%s->%s();" . PHP_EOL, $field->type->db, $field->name, $parsedModifiers, $field->index);
			}
			else
			{
				if ($field->type->db == "enum") {
					$items = '';
					foreach ($field->options as $key => $option) {
						$items .= "'" . $option . "'";
						if ($key < (count($field->options) - 1))
							$items .= ", ";
					}

					$fields .= sprintf("\t\t\t\$table->%s('%s', array(%s));" . PHP_EOL, $field->type->db, $field->name, $items);
				}
				else
					$fields .= sprintf("\t\t\t\$table->%s('%s')%s;" . PHP_EOL, $field->type->db, $field->name, $parsedModifiers);
			}

			// Check foreign key
			if ($field->foreignKey)
			{
				$fields .= sprintf("\t\t\t\$table->foreign('%s')->references('%s')->on('%s');" . PHP_EOL . PHP_EOL, $field->name, $field->foreignKey->field, $field->foreignKey->table);
			}
		}

		if($this->modelData->timeStamps)
			$fields .= PHP_EOL . "\t\t\t\$table->timestamps();" . PHP_EOL;

		$this->stub = str_replace('{{fields}}', $fields, $this->stub);

		return $this;
	}
}