		
			if(isset(${{table_name}}Conditions["{{field}}"]))
				$query->where('{{table_name}}.{{field}}', '=', ${{table_name}}Conditions["{{field}}"]);

			if(isset(${{table_name}}Conditions["{{field}}"]) && isset(${{table_name}}Conditions["{{field}}"]["start"]))
				$query->where('{{table_name}}.{{field}}', '>=', ${{table_name}}Conditions["{{field}}"]["start"]);

			if(isset(${{table_name}}Conditions["{{field}}"]) && isset(${{table_name}}Conditions["{{field}}"]["end"]))
				$query->where('{{table_name}}.{{field}}', '<=', ${{table_name}}Conditions["{{field}}"]["end"]);
