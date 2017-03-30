// checkbox list JSON teste 
	
		vm.checkboxlist = [];
		
		var req = {
			 method: 'GET',
			 url: vm.api.baseUrl+'/api/{{table_from}}/gettree',
			 headers: {
			   'Content-Type': 'application/json'
			 }
		}

		$http(req).then(function successCallback(response) {
		    console.log(response.data);
		    vm.checkboxlist = response.data;
		   	
		  }, function errorCallback(response) {
		    console.log('erro');
		 });

		vm.clickCheck = function(item){
			console.log("clicou no checkbox");
			item.value = !item.value;
			// selecionar todos caso tenha um item filho
			if(item.children){
				angular.forEach(item.children, function(children1){
					children1.value = item.value;
					if(children1.children){
						angular.forEach(children1.children, function(children2){
							children2.value = item.value;
						});
					}
				});
			}
		};