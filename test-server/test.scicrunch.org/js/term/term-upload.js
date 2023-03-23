(function(){

	var app = angular.module("termUploadApp", ["errorApp", "ui.bootstrap", "term", 'ngFileUpload']);


	app.controller('termUploadCtrl',
			["$scope", "$log", "errorModalCaller", "term", 'Upload', '$timeout',
	        function($scope, $log, emc, term, Upload, $timeout) {

		var that = this;
		var cid = parseInt($("#cid").val(), 10);

		$scope.error = false;
		$scope.feedback = '';
		$scope.result = '';

	    $scope.uploadTsv = function(file) {
			$scope.error = false;
			$scope.feedback = '';
			$scope.result = '';

	    	if (file === undefined) {
	    		var feedback = "Please choose a JSON file before pressing submit button.";
	    		$scope.feedback =  feedback;
	    		$scope.error = true;
	    		emc.call(feedback);
	    		return false;
	    	}

	        file.upload = Upload.upload({
	          url: '/forms/term-forms/term-bulk-upload.php',
	          data: {"file": file, "cid": cid},
	        });

	        file.upload.then(function (response) {
	        	//$log.log(response)
	        	$scope.result = response.data;
	            $timeout(function () {
	              file.result = response.data;
	            });
	          }, function (response) {
	            if (response.status > 0)
	              $scope.error = true;
	              $scope.feedback = response.status + ': ' + response.data;
	          }, function (evt) {
	            // Math.min is to fix IE which reports 200% sometimes
	            file.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
	          });
	    }

	    $scope.reset = function(){
	    	$scope.result = "";
	    	$scope.tsvFile.result = "";
	    	$scope.tsvFile = undefined;
	    }

	}]);


}());
