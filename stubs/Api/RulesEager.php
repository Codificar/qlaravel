		
		// object {{foreign_table}}
		foreach({{foreign_model_name}}::$rules as $field => $rule){
			$rules['{{foreign_table}}.'.$field] = $rule ;
		}
		{{unique_eager_rules}}