		
		// store object {{foreign_model}}
		${{foreign_table}} = new {{foreign_model_name}};
		${{foreign_table}}->fill($vars['{{foreign_table}}']);
		${{foreign_table}}->save();	
		$vars['{{field}}'] = ${{foreign_table}}->{{foreign_field}} ;