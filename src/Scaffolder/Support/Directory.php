<?php

namespace Scaffolder\Support;

use Illuminate\Support\Facades\File;

class Directory
{
	public static function createIfNotExists($path, $mode = 0755, $recursive = false, $force = false)
	{
		if (!File::isDirectory($path))
		{
			File::makeDirectory($path, $mode, $recursive , $force );
		}
	}


}

