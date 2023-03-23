var app = angular.module("submissionApp", []);

app.controller('submissionCtrl', ['$scope', '$window', '$timeout', '$http', '$sce',
	function($scope, $window, $timeout, $http, $sce) {

		// Instruction information
		var instructionsInfo = {
			'validationABDescription' : 'Example: This antibody has been validated for mouse hippocampus cells in mouse see Fig. 2 of (Smith et al, 2020).',
			'validationABInstructions' : 'Please include the experimental process of antibody validation, the application, and the specificity. If the antibody has previously been validated,  please include citations or reference the antibody validation profile from publicly available databases.',
			'issueABDescription': 'Example: Using P55 mouse hippocampus, in a western blot experiment, this antibody stained two bands in the 42 and 56 kDa range (see Figure 2 of Smith et al, 2020)',
			'issueABInstructions': 'Please describe the issues found such as non-specific and include the information of the application, organism, tissue, and describe the observation in the images.',
			'discontinuedABDescription':  'Example: Please describe where the information was found.',
			'discontinuedABInstructions' : 'Please describe where the information was found.',
			'issueCLDescription': 'Example: Please describe the detailed information about the issues found and where the information was found. We will facilitate the submission of this issue to Cellosaurus/ ICLAC.org.',
			'issueCLInstructions': 'Please describe the detailed information about the issues found and where the information was found. We will facilitate the submission of this issue to Cellosaurus/ ICLAC.org.',
			'validationCLDescription':  'Example: Please provide us STR (short tandem repeat) profiling and mycoplasma contamination testing including methods, results, and the date. We will facilitate the submission to Cellosaurus/ ICLAC.org.',
			'validationCLInstructions' : 'Please provide us STR (short tandem repeat) profiling and mycoplasma contamination testing including methods, results, and the date. We will facilitate the submission to Cellosaurus/ ICLAC.org.',
			'contaminatedCLDescription':  'Example: This cell line is not from the strain BALB/c but from a NIH Swiss strain derivative. The information is documented in the Discussion section in the paper (PMID: 123456)", "Grand-parent cell line (BEL-7404) has been shown to be a HeLa derivative. The information is documented in the cell line profile at XXXXX database.',
			'contaminatedCLInstructions' : 'Please describe the detailed information about the issues found and include the references. We will facilitate the submission of this issue to Cellosaurus / ICLAC.org',
			'misidentifiedCLDescription':  'Example: This cell line was originally thought to be of human origin but found to be from rat and is documented in the results section Fig. 5 in the paper (PMID: 123456)',
			'misidentifiedCLInstructions' : 'Please describe the detailed information about the issues found and include the reference. We will facilitate the submission of this issue to Cellosaurus / ICLAC.org.',
			'discontinuedCLDescription':  'Example: I contacted ABC company and they notified me that this cell line is no longer available for purchase.',
			'discontinuedCLInstructions' : 'Please describe how you discovered the information and provide supporting material if available',
			'otherCLDescription':  'Example: Please describe where the information was found.',
			'otherCLInstructions' : 'Please describe where the information was found.'
		};

		var query = $window.location.search;
		var rrid = query.split('&')[0];
		var prefix = (rrid.split('=')[1]).split('_')[0];

		// Determines options available for the Submission Type field
		// and Submission Instructions
		$scope.prefix = prefix;
		$scope.entity = "";

		switch (prefix) {
			case 'AB':
				$scope.entity = 'Antibody';
				document.getElementById('description').placeholder = instructionsInfo['validationABDescription'];
				document.getElementById('instructions').innerText = instructionsInfo['validationABInstructions'];
				break;
			case 'CVCL':
				$scope.entity = 'Cell Line';
				document.getElementById('description').placeholder = instructionsInfo['validationCLDescription'];
				document.getElementById('instructions').innerText = instructionsInfo['validationCLInstructions'];
				break;
		}

		/** Start of Submission Instructions & Description Placeholder **/

		$scope.changeInstructions = function() {
			var currSubmissionType = document.getElementById('submissionType').value;
			if (prefix == 'AB') {
				switch (currSubmissionType) {
					case 'Validation':
						document.getElementById('description').placeholder = instructionsInfo['validationABDescription'];
						document.getElementById('instructions').innerText = instructionsInfo['validationABInstructions'];
						break;
					case 'Discontinued':
						document.getElementById('description').placeholder = instructionsInfo['discontinuedABDescription'];
						document.getElementById('instructions').innerText = instructionsInfo['discontinuedABInstructions'];
						break;
					case 'Other':
						// document.getElementById('description').placeholder = instructionsInfo['otherCLDescription'];
						// document.getElementById('instructions').innerText = instructionsInfo['otherCLInstructions'];
						break;
					case 'Issue':
						document.getElementById('description').placeholder = instructionsInfo['issueABDescription'];
						document.getElementById('instructions').innerText = instructionsInfo['issueABInstructions'];
						break;
				}
			} else if (prefix == 'CVCL') {
				switch (currSubmissionType) {
					case 'Validation':
						document.getElementById('description').placeholder = instructionsInfo['validationCLDescription'];
						document.getElementById('instructions').innerText = instructionsInfo['validationCLInstructions'];
						break;
					case 'Contaminated':
						document.getElementById('description').placeholder = instructionsInfo['contaminatedCLDescription'];
						document.getElementById('instructions').innerText = instructionsInfo['contaminatedCLInstructions'];
						break;
					case 'Misidentified':
						document.getElementById('description').placeholder = instructionsInfo['misidentifiedCLDescription'];
						document.getElementById('instructions').innerText = instructionsInfo['misidentifiedCLInstructions'];
						break;
					case 'Discontinued':
						document.getElementById('description').placeholder = instructionsInfo['discontinuedCLDescription'];
						document.getElementById('instructions').innerText = instructionsInfo['discontinuedCLInstructions'];
						break;
					case 'Other':
						document.getElementById('description').placeholder = instructionsInfo['otherCLDescription'];
						document.getElementById('instructions').innerText = instructionsInfo['otherCLInstructions'];
						break;
					case 'Issue':
						document.getElementById('description').placeholder = instructionsInfo['issueCLDescription'];
						document.getElementById('instructions').innerText = instructionsInfo['issueCLInstructions'];
						break;
				}
			}
		}

		/** End of Submission Instructions & Description Placeholder **/

}]);

/** Start of Global Variables **/

// Contains all submitted files from submit form
var fileList = {};

// Max number of MBs acceptable for upload
var maxMB = 50;

/** End of Global Variables **/

// function selectionSubmissionType(value) {
//   // Displays the appropriate  input field, for file or text
//   var element = document.getElementById('otherRecordType');
//
//   if (value == 'Other') {
//     element.style.display = 'block';
//   } else {
//     element.style.display = 'none';
//   }
// }

function selectionSupportingDocs(value) {
  // Displays the appropriate  input field, for file or text
  let fileSubmission = ['pdf', 'image'];
  let idSubmission = ['pmid', 'doi'];
  let id1 = '';
  let id2 = '';

  if (fileSubmission.includes(value)) {
    id1 = 'submitFileInput';
    id2 = 'submitIDInput';
  } else {
    id1 = 'submitIDInput';
    id2 = 'submitFileInput';
  }

  var element1 = document.getElementById(id1);
  var element2 = document.getElementById(id2);

  if (fileSubmission.includes(value)) {
    element2.style.display = 'none';
    element1.style.display = 'block';
  } else {
    element2.style.display = 'none';
    element1.style.display = 'block';
  }
}

function validateDoc(id) {
  // Validates the input from supporting document field
  var docVal = $(id);
  var value = docVal[0].value;
  if (docVal.valid()) {
    return true;
  } else {
    return false;
  }
}

function removeEntry(item) {
	// Removed entire entry
	delete fileList[item.id];
	item.parentNode.remove();

	var pmidListCount = $('#pmidList')[0].childElementCount;
	var doiListCount = $('#doiList')[0].childElementCount;
	var pdfListCount = $('#pdfList')[0].childElementCount;
	var imageListCount = $('#imageList')[0].childElementCount;
	var urlListCount = $('#urlList')[0].childElementCount;

	if (pmidListCount == 0) { document.getElementById('pmidDisplay').style.display = 'none';}
	if (doiListCount == 0) { document.getElementById('doiDisplay').style.display = 'none';}
	if (pdfListCount == 0) { document.getElementById('pdfDisplay').style.display = 'none';}
	if (imageListCount == 0) { document.getElementById('imageDisplay').style.display = 'none';}
	if (urlListCount == 0) { document.getElementById('urlDisplay').style.display = 'none';}

	if ((pmidListCount == 0) && (doiListCount == 0) && (pdfListCount == 0)
			&& (imageListCount == 0) && (urlListCount == 0)) {
		document.getElementById('documentDisplay').style.display = 'none';
	}
}

function retrieveValue(docList) {
	for (var i = 0; i < docList.length; i++) {
		var docName = docList[i].innerText.replace('×','');
		document.getElementById("confirmSupportingDocuments").innerHTML += '<li>' + docName+ '</li>'
	}
}

function validateFileSize() {
	var fileSize = (($('#submitFile')[0].files[0].size)/1024)/1024; // Converts to MB
	if (fileSize < maxMB) {
		return true;
	}
	return false;
}

function checkPDFType() {
	if (document.getElementById('pdfTypeError') === null) {
		$('#noteSection')[0].innerHTML += '<p id="pdfTypeError" style="min-width:600px; color:red; font-size:16px"><b>Alert: </b>The selected file is not a PDF</p>';
	} else {
		document.getElementById('pdfTypeError').style.display = 'block';
	}
}

function successfulUpload(status) {
	if (status != 200) {
		exit('Upload failed!');
	}
}

function downloadSupportingDocs(fileList, successfulUpload) {

	for (var key in fileList) {
		var file = fileList[key];
		var formData = new FormData();
		var request = new XMLHttpRequest();
		var userID = $('#userID')[0].value;

		formData.set('userID', userID);
		formData.set('file', file);
		request.open("POST", '/resource-watch/upload.php', true);

		request.onreadystatechange = function () {
			if (request.readyState === 4) {
				if (request.status === 200) {
					successfulUpload(request.status);
				} else {
					successfulUpload(request.status);
				}
			}
		}
		request.send(formData);
	}
}

function downloadFile(file) {
	return new Promise(function (resolve, reject) {
		var formData = new FormData();
		var request = new XMLHttpRequest();
		var userID = $('#userID')[0].value;

		formData.set('userID', userID);
		formData.set('file', file);
		request.open("POST", '/resource-watch/upload.php', true);

		request.send(formData);

		request.onreadystatechange = function () {
			if (request.readyState === 4) {
				resolve(request);
			}
		}
	}).then(function(request){
		return request;
	});
}

function download(fileList) {
	for (var key in fileList) {
		var cur = Promise.resolve(fileList[key]);
		cur = cur.then(function(file) {
			var request = downloadFile(file);
			return request;
		});

		cur.then(function(request) {
			if (request.readyState === 4) {
				if (request.status != 200) {
					console.log("File failed to upload");
					// Write something to UI to show which file failed
				}
			}
		});
	}
	return cur;
}


function clearErrors() {
	if (document.getElementById('imageTypeError') != null) {document.getElementById('imageTypeError').style.display = 'none';}
	if (document.getElementById('fileSizeError') != null) {document.getElementById('fileSizeError').style.display = 'none';}
	if (document.getElementById('pdfTypeError') != null) {document.getElementById('pdfTypeError').style.display = 'none';}
}

$(document).ready(function() {
  /** Start of Input Validation **/

  $('#otherValue').focusout(function() {
    validateDoc('#otherValue');
  });

  $('#submitID').focusout(function() {
    validateDoc('#submitID');
  });

  $('#submitFile').focusout(function() {
    validateDoc('#submitFile');
  });

  $('#description').focusout(function() {
    validateDoc('#description');
  });

  /** End of Input Validation **/


	$('#supportDocType').change(function () {
		// Clears previous errors
		clearErrors();

		var selectedVal = $('#supportDocType')[0].value;
		if (selectedVal == 'image') {
			document.getElementById('imageNote').style.display = 'block';
		} else {
			document.getElementById('imageNote').style.display = 'none';
		}

		if (selectedVal == 'pdf') {
			document.getElementById('pdfNote').style.display = 'block';
		} else {
			document.getElementById('pdfNote').style.display = 'none';
		}

	});

  /** Start of Managing Support Documentation **/

  $('#addDocBttn').click(function() {

		var currDocType = document.getElementById('supportDocType').value;
		var pmidListCount = $('#pmidList')[0].childElementCount;
		var doiListCount = $('#doiList')[0].childElementCount;
		var pdfListCount = $('#pdfList')[0].childElementCount;
		var imageListCount = $('#imageList')[0].childElementCount;
		var urlListCount = $('#urlList')[0].childElementCount;
		var idVal = document.getElementById('submitID').value;
		var fileVal = $('#submitFile')[0].value.split('\\')[2];

		if (($('#submitIDInput')[0].style.display == 'block')) {
			// If ID input is in focus
			if (idVal == '' || idVal.match(/\s+\s+/)) {
				document.getElementById('submitID').className += " invalid";
				return false;
			}
		}

		if (($('#submitFileInput')[0].style.display == 'block')) {
			// If File input is in focus
			if (fileVal == '' || fileVal == undefined || fileVal.match(/\s+\s+/)) {
				document.getElementById('submitFile').className += " invalid";
				return false;
			}
		}

		switch (currDocType) {
			case 'pmid':
				if (pmidListCount == 0) { document.getElementById('pmidDisplay').style.display = 'block';}

				$('#pmidList')[0].innerHTML += '<li><input type="hidden" name="pmidSupportingDocs[]" value="'+ idVal + '">'+ idVal + '</input> <span id="'+ idVal +'" class="removeEntry" onclick="removeEntry(this)">&times;</span></li>';
				document.getElementById('documentDisplay').style.display = 'block';
				break;

			case 'doi':
				if (doiListCount == 0) { document.getElementById('doiDisplay').style.display = 'block';}

				$('#doiList')[0].innerHTML += '<li><input type="hidden" name="doiSupportingDocs[]" value="'+ idVal + '">'+ idVal + '</input> <span id="'+ idVal +'" class="removeEntry" onclick="removeEntry(this)">&times;</span></li>';
				document.getElementById('documentDisplay').style.display = 'block';
				break;

			case 'url':
				if (urlListCount == 0) { document.getElementById('urlDisplay').style.display = 'block';}

				$('#urlList')[0].innerHTML += '<li><input type="hidden" name="urlSupportingDocs[]" value="'+ idVal + '">'+ idVal + '</input> <span id="'+ idVal +'" class="removeEntry" onclick="removeEntry(this)">&times;</span></li>';
				document.getElementById('documentDisplay').style.display = 'block';
				break;

			case 'pdf':
				// Clear out previous alerts
				if (document.getElementById('imageTypeError') != null) {document.getElementById('imageTypeError').style.display = 'none';}

				var fileType = ($('#submitFile')[0].files[0].type).split('/')[1];
				var fileData = $('#submitFile')[0].files[0];
				if (validateFileSize()) {

					if (document.getElementById('fileSizeError') != null) {document.getElementById('fileSizeError').style.display = 'none';}

					if (fileType == 'pdf') {
						// Adds pdf to list of Supporting Documents
						if (pdfListCount == 0) { document.getElementById('pdfDisplay').style.display = 'block';}
						$('#pdfList')[0].innerHTML += '<li><input type="hidden" name="pdfSupportingDocs[]" value="'+ fileVal + '">'+ fileVal + '</input> <span id="'+ fileVal +'" class="removeEntry" onclick="removeEntry(this)">&times;</span></li>';
						document.getElementById('documentDisplay').style.display = 'block';
						fileList[fileVal] = fileData;

					} else {
						checkPDFType()
					}

				} else {

					if (fileType == 'pdf') {
						if (document.getElementById('pdfTypeError') != null) {document.getElementById('pdfTypeError').style.display = 'none';}
					} else {
						checkPDFType()
					}

					// Displays Alert message when file is too large
					var fileSize = Math.round(((($('#submitFile')[0].files[0].size)/1024)/1024) * 100) / 100;

					if (document.getElementById('fileSizeError') === null) {
						$('#noteSection')[0].innerHTML += '<p id="fileSizeError" style="min-width:700px; color:red; font-size:16px"><b>Alert: </b>You have exceeded the maximum file size of 50MB. Current file size: ' + fileSize + 'MB</p>';
					} else {
						document.getElementById('fileSizeError').innerHTML = '<b>Alert: </b>You have exceeded the maximum file size of 50MB. Current file size: ' + fileSize + 'MB';
						document.getElementById('fileSizeError').style.display = 'block';
					}
				}
				break;

			case 'image':
				// Clear out previous alerts
				if (document.getElementById('pdfTypeError') != null) {document.getElementById('pdfTypeError').style.display = 'none';}
				if (document.getElementById('fileSizeError') != null) {document.getElementById('fileSizeError').style.display = 'none';}

				var fileType = ($('#submitFile')[0].files[0].type).split('/')[1];
				var fileData = $('#submitFile')[0].files[0];

				if (fileType == 'png' || fileType == 'jpg' || fileType == 'jpeg') {

					// Adds image to list of Supporting Documents
					if (imageListCount == 0) { document.getElementById('imageDisplay').style.display = 'block';}
					$('#imageList')[0].innerHTML += '<li><input type="hidden" name="imageSupportingDocs[]" value="'+ fileVal + '">'+ fileVal + '</input> <span id="'+ fileVal +'" class="removeEntry" onclick="removeEntry(this)">&times;</span></li>';
					document.getElementById('documentDisplay').style.display = 'block';

					if (document.getElementById('imageTypeError') != null) {document.getElementById('imageTypeError').style.display = 'none';}
					fileList[fileVal] = fileData;

				} else {

					if (document.getElementById('imageTypeError') === null) {
						$('#noteSection')[0].innerHTML += '<p id="imageTypeError" style="min-width:600px; color:red; font-size:16px"><b>Alert: </b>File must be a PNG, JPG, JPEG</p>';
					} else {
						document.getElementById('imageTypeError').style.display = 'block';
					}
				}
				break;
		}

		document.getElementById('submitID').value = '';
		document.getElementById('submitFile').value = '';
  });

	/** End of Managing Support Documentation **/

	$('#confirmSubmission').click(function () {

		// Ensure Require Fields are filled out
		var description = $("#description").val();
		var descriptionLen = description.length;
		var pmidListLen =  $('#pmidList')[0].children.length;
		var doiListLen =  $('#doiList')[0].children.length;
		var pdfListLen =  $('#pdfList')[0].children.length;
		var imageListLen =  $('#imageList')[0].children.length;
		var urlListLen =  $('#urlList')[0].children.length;

		if ((descriptionLen > 0 && !(description.match(/\s+\s+/)) && description != '')
		    && (pmidListLen > 0 || doiListLen > 0 || pdfListLen > 0
					|| imageListLen > 0 || urlListLen > 0))
		{
			$('#confirmation').modal('show');
		} else {
			// User needs to fill in Required Fields
			if (descriptionLen == 0) {
				document.getElementById('description').className += " invalid";
				return false
			}

			if ((pmidListLen == 0 && doiListLen == 0 && pdfListLen == 0 && imageListLen == 0)) {

				document.getElementById('submitID').className += " invalid";
				document.getElementById('submitFile').className += " invalid";
				return false
			}

		}

		var rrid = document.getElementById('rrid').value;
		var vendor = document.getElementById('vendor').value;
		var catalogNumber = document.getElementById('catalogNumber').value;
		var submissionType = document.getElementById('submissionType').value;

		var catalogNumber = document.getElementById('catalogNumber').value;
		var submissionType = document.getElementById('submissionType').value;

		document.getElementById("confirmRRID").value = rrid;
		document.getElementById("confirmVendor").value = vendor;
		document.getElementById("confirmCatalogNumber").value = catalogNumber;
		document.getElementById("confirmSubmissionType").value = submissionType;

		retrieveValue($('#pmidList')[0].children);
		retrieveValue($('#doiList')[0].children);
		retrieveValue($('#pdfList')[0].children);
		retrieveValue($('#imageList')[0].children);
		retrieveValue($('#urlList')[0].children);

	});

	$('#submitSubmission').click(function () {
		download(fileList);
		$('#submitForm').submit();
	});

	$('#cancelSubmission').click(function () {
		document.getElementById("confirmSupportingDocuments").innerHTML = "";
	});

	$('#submitID, #submitfile').blur(function () {
		var pmidListLen =  $('#pmidList')[0].children.length;
		var doiListLen =  $('#doiList')[0].children.length;
		var pdfListLen =  $('#pdfList')[0].children.length;
		var imageListLen =  $('#imageList')[0].children.length;
		var urlListLen =  $('#urlList')[0].children.length;

		if (pmidListLen > 0 || doiListLen > 0 || pdfListLen > 0
			  || imageListLen > 0 || urlListLen > 0 ) {
			document.getElementById('submitID').classList.remove("invalid");
			document.getElementById('submitFile').classList.remove("invalid");
			$('em').remove();
		}
	});

})
