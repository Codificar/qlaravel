	// return {{function_name}} by {{class_name}}Id
	public function {{function_name}}($id) 
	{
		${{class_name_lw}} = {{class_name}}::find($id);
		return ${{class_name_lw}}->{{function_name}}()->get();
	}