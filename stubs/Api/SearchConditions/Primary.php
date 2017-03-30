		
			if(isset(${{table_name}}Conditions["{{field}}"]))
				$query->where('{{field}}', '=', ${{table_name}}Conditions["{{field}}"]);
