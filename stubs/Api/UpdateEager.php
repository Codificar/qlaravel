		
		// update object {{foreign_model}}
		${{foreign_table}} = {{foreign_model_name}}::findOrNew($vars['{{field}}']);
		${{foreign_table}}->fill($vars['{{foreign_table}}']);
		${{foreign_table}}->save();
		$vars['{{field}}'] = ${{foreign_table}}->{{foreign_field}} ;