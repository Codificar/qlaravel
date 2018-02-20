		// autocomplete
		vm.items = [];
		vm.simulateQuery = false;
		vm.isDisabled    = false;

		vm.querySearch   = querySearch;
		vm.selectedItemChange = selectedItemChange;
		vm.searchTextChange   = searchTextChange;

		var req = {
			 method: 'POST',
			 url: vm.api.baseUrl+'/api/{{table_from}}/queryfilters',
			 headers: {
			   'Content-Type': 'application/json'
			 },
			 data: {"pagination": {"actual": 1, "itensPerPage": 20}, "fields": ["name"], "orderBy": "name" }
		}

		$http(req).then(function successCallback(response) {
		    console.log(response.data.data);

		    var size = response.data.data.length;

		    for(var i = 0; i < size; i++)
		    {
		    	//autocomplete
		    	var item = {'text': response.data.data[i].name};

		    	vm.items.push(item);
		    	console.log(response.data.data[i].name);
		    }
		  }, function errorCallback(response) {
		    console.log('erro');
		 });

		function querySearch (query) {
		   var results = query ? self.states.filter( createFilterFor(query) ) : self.states,
		    deferred;
		   if (self.simulateQuery) {
		   deferred = $q.defer();
		   $timeout(function () { deferred.resolve( results ); }, Math.random() * 1000, false);
		   return deferred.promise;
		   } else {
		   return results;
		   }
		}

		function searchTextChange(text) {
		   
		}

		function selectedItemChange(item) {
		   
		}