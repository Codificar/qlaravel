	//get
	//returns ids and names with children or not using column 'parent_id'
	public function getTree(Request $request)
	{
		// get query parameters
		$params = $request->all();
		$json = [];
		$key = false;


		//Check if table have parent_id column
		if(Schema::hasColumn('{{class_name_lw}}', 'parent_id')){
			${{class_name_lw}}Data = {{model_name}}::orderBy('parent_id','asc')->get();
		} else {
			${{class_name_lw}}Data = {{model_name}}::orderBy('name','asc')->get();
			$key = true;
		}

		

		foreach (${{class_name_lw}}Data as ${{class_name_lw}}) {

			//Table have parent_id column
			if(!$key)
			{
				//Raiz
				if(${{class_name_lw}}->parent_id == null)
				{
					
					//getChildrenList
					${{class_name_lw}}Children = {{model_name}}::where('parent_id','=',${{class_name_lw}}->id)->get();

					
					if(sizeof(${{class_name_lw}}Children) > 0)
					{
						$item = ['id' => ${{class_name_lw}}->id, 'text' => ${{class_name_lw}}->name, 'value' => false, 'children' => $this->searchForChildren(${{class_name_lw}}Children, 1)];
						
						//array_push($item,);
						//$this->searchForChildren($typelistChildren, 1);

					} else {
						//No children

						$item = ['id' => ${{class_name_lw}}->id, 'text' => ${{class_name_lw}}->name, 'value' => false];
					}

					array_push($json,$item);
				}
			} else {

				$item = ['id' => ${{class_name_lw}}->id, 'text' => ${{class_name_lw}}->name, 'value' => false];

				array_push($json,$item);	

			}

			
		}

		return $json;
	}

	public function searchForChildren(${{class_name_lw}}Array, $i)
	{
		$i = $i;
		$arrayItem = [];

		foreach (${{class_name_lw}}Array as $key => ${{class_name_lw}}) {
			
			${{class_name_lw}}Father = {{model_name}}::find(${{class_name_lw}}->parent_id);

			
			//getChildrenList
			${{class_name_lw}}Children = {{model_name}}::where('parent_id','=',${{class_name_lw}}->id)->get();


			if(sizeof(${{class_name_lw}}Children) > 0)
			{	
				$item = ['id' => ${{class_name_lw}}->id, 'text' => ${{class_name_lw}}->name, 'value' => false, 'class' => "children-checkbox-".$i, 'children' => $this->searchForChildren(${{class_name_lw}}Children, $i+1)];
				//var_dump($item);
				//$item += $this->searchForChildren($typelistChildren, $i+1);	
				//$test = [];
				array_push($arrayItem, $item);
				//array_push($test, $this->searchForChildren($typelistChildren, $i+1));
				//var_dump($item);
			} else {
				$item = ['id' => ${{class_name_lw}}->id, 'text' => ${{class_name_lw}}->name, 'value' => false, 'class' => "children-checkbox-".$i];
				array_push($arrayItem, $item);
				//var_dump($item);
			}
			//var_dump($item);
		}
		return $arrayItem;
	}