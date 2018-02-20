// multiple auto complete
		var contacts = [];
		vm.querySearchChips = querySearchChips;
		
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
			    //multiple autocomplete
			    contacts.push(response.data.data[i].name);
			}

			vm.allContacts = loadContacts(contacts);
		  }, function errorCallback(response) {
		    console.log('erro');
		 });


		/**
		* Search for contacts; use a random delay to simulate a remote call
		*/
		function querySearchChips (criteria) {
			return criteria ? vm.allContacts.filter(createFilterFor(criteria)) : [];
		}


		/**
		 * Create filter function for a query string
		 */
		function createFilterFor(query) {
			var lowercaseQuery = angular.lowercase(query);

			return function filterFn(contact) {
				return (contact._lowername.indexOf(lowercaseQuery) != -1);
			};

		}

		function loadContacts(contacts) {
			console.log('Contatos: '+contacts);

			return contacts.map(function (c, index) {
				var cParts = c.split(' ');
				var email = '@example.com';
				var hash = "CryptoJS.MD5(email)";

				var contact = {
					name: c,
					email: email,
					image: '//www.gravatar.com/avatar/' + hash + '?s=50&d=retro'
				};
				contact._lowername = contact.name.toLowerCase();
				return contact;
			});
		}
