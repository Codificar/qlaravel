<?php

namespace Scaffolder\Compilers\AngularJs;

use Illuminate\Support\Facades\File;
use Scaffolder\Compilers\AbstractCompiler;
use Scaffolder\Compilers\Support\FileToCompile;
use Scaffolder\Compilers\Support\PathParser;
use Scaffolder\Support\Directory;

class IndexApiCompiler extends AbstractCompiler
{
	protected $cachePrefix 	= 'index_api_';
	protected $stubFilename = 'IndexApi.js' ;

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
		return $this->store(new FileToCompile(false, $this->cachePrefix));
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
		if (File::exists(base_path('scaffolder-config/cache/' . $this->cachePrefix  . self::CACHE_EXT)))
		{
			return $this->store(new FileToCompile(true, $this->cachePrefix));
		}
		else
		{

			return $this->replaceAndStore();
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
		$folder = PathParser::parse($this->scaffolderConfig->generator->paths->index);

		return $folder  . 'index.api.js';
	}

}