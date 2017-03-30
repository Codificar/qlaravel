(function ()
{
	/*
	 *		- Scaffolder code generate - 
	 *			por Codificar Sistemas Tecnologicos
	 *
	 *		{{class_name}} Controller Register
	 *		Angular Controller to Register view 
	 *
	 */

	'use strict';

	angular
		.module('app.{{table_name}}.register')
		.controller('{{class_name}}Controller', {{class_name}}Controller);

	function {{class_name}}Controller($stateParams, $http, {{table_name}}, {{class_name}}Resource, api, $mdDialog, $location, $translate, $filter, $mdConstant, $timeout)
	{
		var vm = this;
		vm.$t = $filter('translate');
		vm.api = api;

		// Variables
		vm.{{table_name}} = {{table_name}} ;


		// Mehods 
		vm.sendForm = sendForm;
		vm.confirmDeleteAction = confirmDeleteAction;
		vm.cancelAction = cancelAction;

		{{file_upload}}

		{{auto_complete}}

		{{multiple_auto_complete}}

		{{checkbox}}

		{{checkbox_tree}}
		
		// Use common key codes found in $mdConstant.KEY_CODE...
		vm.keys = [$mdConstant.KEY_CODE.ENTER, $mdConstant.KEY_CODE.COMMA];
		vm.tags = [];

		function sendForm()
		{
			console.log(vm.{{table_name}});

			var alert =	$mdDialog.alert()
					.parent(angular.element(document.querySelector('#popupContainer')))
					.clickOutsideToClose(true)
					.ok(vm.$t('common_words.ok'));

			if(vm.{{table_name}}.id){
				alert.title(vm.$t('register.dialogs.edit_item.success.title', { entity: ""+vm.$t("{{table_name}}.singular")+"" }));
				{{class_name}}Resource.update(vm.{{table_name}}.id, vm.{{table_name}}).$promise.then(function(data){
					alert.textContent(vm.$t('register.dialogs.edit_item.success.content', { entity: ""+vm.$t("{{table_name}}.singular")+"" }));
					$mdDialog.show(alert);
					$location.path('/{{table_name}}/list'); 
				}, function(error){
					alert.title(vm.$t('register.dialogs.edit_item.error.title'));
					alert.textContent(vm.$t('register.dialogs.edit_item.error.content', { entity: ""+vm.$t("{{table_name}}.singular")+"" }));
					$mdDialog.show(alert);
				});

			}else{
				alert.title(vm.$t('register.dialogs.add_item.success.title', { entity: ""+vm.$t("{{table_name}}.singular")+"" }));
				{{class_name}}Resource.create(vm.{{table_name}}).then(function(data){
					alert.textContent(vm.$t('register.dialogs.add_item.success.content', { entity: ""+vm.$t("{{table_name}}.singular")+"" }));
					$mdDialog.show(alert); 
					$location.path('/{{table_name}}/list');
				}, function(error){
					alert.title(vm.$t('register.dialogs.add_item.error.title'));
					console.log(error);
					if(error.statusText)
						alert.textContent(vm.$t('register.dialogs.add_item.error.content_with_log', { entity: ""+vm.$t("{{table_name}}.singular")+"" }) + error.statusText);
					else
						alert.textContent(vm.$t('register.dialogs.add_item.error.content', { entity: ""+vm.$t("{{table_name}}.singular")+"" }));
					$mdDialog.show(alert);
				});
			}

			// Clear object
			vm.form = {};
		}    

		function confirmDeleteAction(ev){
			var confirm = $mdDialog.confirm()
				.title(vm.$t('register.dialogs.remove_item.title', { entity: ""+vm.$t("{{table_name}}.singular")+"" }))
				.textContent(vm.$t('register.dialogs.remove_item.content', { entity: ""+vm.$t("{{table_name}}.singular")+"" }))
				.targetEvent(ev)
				.ok(vm.$t('common_words.yes'))
				.cancel(vm.$t('common_words.no'));
			$mdDialog.show(confirm).then(function() {
				{{class_name}}Resource.delete(vm.{{table_name}}.id);
				$location.path('/{{table_name}}/list');
			});
		}

		function cancelAction(){
			$location.path('/{{table_name}}/list');
		}

	}

})();