		
			if(isset(${{table_name}}Conditions["{{field}}"]))
				$query->where('{{table_name}}.{{field}}', 'LIKE', '%'.${{table_name}}Conditions["{{field}}"].'%');
				