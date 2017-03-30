// Checkbox list tree JSON example 
	



		vm.checkboxlistTree = [];


		var req = {
			 method: 'GET',
			 url: vm.api.baseUrl+'/api/{{table_from}}/gettree',
			 headers: {
			   'Content-Type': 'application/json'
			 }
		}

		$http(req).then(function successCallback(response) {
		    console.log(response.data);
		    vm.checkboxlistTree = response.data;
		   	
		  }, function errorCallback(response) {
		    console.log('erro');
		 });


		vm.clickMenuItem = function(item){

			$timeout(function(){
				item.closeItems = !item.closeItems;
				console.log(item.closeItems);

				var element_children_icon = angular.element(document.getElementById('icon-cbl-' + item.id));

				if(item.closeItems){
					element_children_icon.addClass('icon-item-list-tree-rotate');
				}else{
					element_children_icon.removeClass('icon-item-list-tree-rotate');
				}

				console.log(element_children_icon); 
			}, 100);
		};