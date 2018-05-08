		
		if(isset($filters["{{table_name}}"])){
			${{table_name}}Conditions = $filters["{{table_name}}"] ;

			if(isset($params["simpleFilter"]) && $params["simpleFilter"])
			{
				{{simple_filter}}
			} else {
				// field by field condition
				{{field_conditions}}
			}

		}