vm.files = [];
		vm.file = [];
		vm.fileLength = 0;
		vm.singleFile = true;
		vm.setFileName = setFileName;
		vm.upload = upload;
		vm.getImg = getImg;
		vm.getImages = getImages;
		vm.removeByIndex = removeByIndex;
		vm.removeByValue = removeByValue;
		vm.images = [];

		function getImages(strFileIds) {
			var arrImages = JSON.parse(strFileIds);
			
			arrImages.forEach(function (element, index, array) {
				getImg(element);
			});
		}

		function removeByIndex(index) {
			console.log(index);
			vm.files.splice(index, 1);
			return JSON.stringify(vm.files);
		}

		function removeByValue(value) {
			vm.files.forEach(function (element, index, array) {
				if (element == value) {
					vm.files.splice(index, 1);
					vm.fileLength--;
				}
			});

			return JSON.stringify(vm.files);
		}
		
		function getImg(fileId) {
			var url = vm.api.baseUrl + "/api/file/" + fileId;
			var src = "";

			$http.get(url).success(function(result){
				if (vm.singleFile) {
					vm.files = result.id;
					vm.file = result;
				}
				else {
					vm.files.push(result.id);	
					vm.file.push(result);
					vm.fileLength = vm.files.length;
				}
			});
		}
		
		function setFileName(files, message, flow) {
			var file = "";
			if (vm.singleFile) {
				vm.files = message;
				file = vm.files;
			}
			else {

				vm.files.push(parseInt(message));	
				file = JSON.stringify(vm.files);
			}

			return file;
		}

		function upload(files, event, flow) {
			flow.upload();
		}

		vm.optionsFlow = function (isSingle) {
			vm.singleFile = isSingle;
			return {
				target: vm.api.baseUrl + '/api/upload',
				singleFile: isSingle,
				testChunks: false
			}
		} 