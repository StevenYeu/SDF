function myFunction34(component_data_value) {
	document.getElementById('filelist0').innerHTML = component_data_value;
	$('#example').show();

	document.getElementById('filelist00').innerHTML = component_data_value;
	$('#example2').show();
}

$(function() {
            // Setup html5 version
     var uploadInitialized = false;

     function validateForm() {
        if ($('#category_required').val() == 1) {
            var pcat = $('[name="predictioncategory"]:checked').length;
            if (pcat < 1) {
                alert ('You must select a prediction method');
                return false;
            } else {
                return true;
            }
        }
        return true;
     }

     function validateProtoForm() {
        if ($('#protocol_type_required').val() == 1) {
            var pcat = $('[name="proto-type"]:checked').length;
            if (pcat < 1) {
                alert ('You must select a prediction method');
                return false;
            } else {
                return true;
            }
        }
        return true;
     }

	$("#uploader").pluploadQueue({
		// General settings
		runtimes : 'html5, html4',
		url : '/php/d3r_upload.php',
//		chunk_size : '2mb',
//multiple_queues: true,        // Specify what files to browse for

		unique_names : true,
        filters : {
            max_file_size : '50mb',
			// Specify what files to browse for
            mime_types: [
                {title : "Image files", extensions : "jpg,gif,png"},
                {title : "Zip files", extensions : "zip,gz,tgz"},
                {title : "Text files", extensions : "csv,txt"},
                {title : "Excel files", extensions : "xls,xlsx"}
            ]
        },

		// PreInit events, bound before any internal events
		preinit : {
			Init: function(up, info) {
//				log('[Init]', 'Info:', info, 'Features:', up.features);
			},
			UploadFile: function(up, file) {
                var category_required = $('#category_required').val();
                var category = $('input:radio[name=predictioncategory]:checked').val();
                var anonymous = $('input:checkbox[name=anonymous]:checked').val();
                var pp =$("#menu-pp").val();
                var ls =$("#menu-ls").val();
                var fe =$("#menu-fe").val();
                
				// You can override settings before the file is uploaded
                 up.setOption('url', '/php/d3r_upload.php?action=upload&id=' + file.id + '&component=' + document.getElementById('filelist0').innerHTML);
                 up.setOption('multipart_params', {param1: category_required, param2 : category, 
                 param3: anonymous, pp:pp, ls:ls, fe:fe});
			},
			UploadComplete: function (up, file) {
				//plupload_add
				$(".plupload_buttons").css("display", "inline");
				$(".plupload_upload_status").css("display", "inline");
			}
		},
		// Post init events, bound after the internal events
		init : {
            StateChanged: function(up) {
                if (!uploadInitialized && up.state == plupload.STARTED) {
                        if (!validateForm()) {
                            up.stop();
                            $(".plupload_buttons").css("display", "inline");
                            $(".plupload_upload_status").css("display", "inline");
                        } else {
                            uploadInitialized = true;
                        }
                    } else {
                        if (up.files.length === (up.total.uploaded + up.total.failed)) {
//                            $('#subform')[0].submit();
                        }
                    }
                
            },

			PostInit: function() {
				// Called after initialization is finished and internal event handlers bound
			//	log('[PostInit]');
						},

            UploadProgress: function(up, file) {
                // Called while file is being uploaded
              //  log('[UploadProgress]', 'File:', file, "Total:", up.total);
            },
            FileUploaded: function(up, file, info, response) {
                // Called when file has finished uploading
             //   log('[FileUploaded] File:', file, "Info:", info);
                
                
                $.ajax({
					type: "POST",
					dataType: "json",
					url: "/php/d3r_filter.php", //Relative or absolute path to response.php file
					//data: data,
					success: function(data) {
						if(typeof(info.response) != "undefined" && info.response !== null) {
							$(".the-return").html(info.response);
                            $(".plupload_buttons").css("display", "inline");
                            $(".plupload_upload_status").css("display", "inline");


//                            if (info.response.substring(0, 4) == 'Warn') {
//                                $("<p><strong>status: Submission accepted with warnings</strong></p>").appendTo( ".the-return" );
//                                alert ("Submission accepted with warnings.");
//                                var nohash = window.location.href.split('#')[0];
//                                var noquestion = window.location.href.split('?')[0]
//                                $("<p>Click <a href='" + location.href + "'>reload</a> to see submission listing</p>").appendTo( ".the-return" );
                                //location.href = noquestion + "?nocache=" + (new Date()).getTime();
                            //}
						} else {	
							$("<strong>status: no validation errors found</strong>").appendTo( ".the-return" );
							alert ("No errors");
							var nohash = window.location.href.split('#')[0];
							var noquestion = window.location.href.split('?')[0]
							location.href = noquestion + "?nocache=" + (new Date()).getTime() + '#submissions';
						} 

//						if (info.response.length > 0) {
					}				
				});
					
            },
			UploadComplete: function(up, files) {
				// Called when all files are either uploaded or failed
  //              log('[UploadComplete]');
			},
            Error: function(up, args) {
                // Called when error occurs
                log('[Error] ', args);
            }
		}
	});

	$("#protocoluploader").pluploadQueue({
		// General settings
		runtimes : 'html5, html4',
		url : '/php/d3r_upload.php',
//		chunk_size : '2mb',
//multiple_queues: true,        // Specify what files to browse for

		unique_names : true,
        filters : {
            max_file_size : '1mb',
			// Specify what files to browse for
            mime_types: [
                {title : "Text files", extensions : "txt"},
            ]
        },

		// PreInit events, bound before any internal events
		preinit : {
			Init: function(up, info) {
//				log('[Init]', 'Info:', info, 'Features:', up.features);
			},
			UploadFile: function(up, file) {
                var category_required = $('#protocol_type_required').val();
                var category = $('input:radio[name=proto-type]:checked').val();

				// You can override settings before the file is uploaded
                 up.setOption('url', '/php/d3r_upload.php?action=upload&id=' + file.id + '&component=' + document.getElementById('filelist0').innerHTML);
                 up.setOption('multipart_params', {param1: protocol_type_required, param2 : category});
			},
			UploadComplete: function (up, file) {
				//plupload_add
				$(".plupload_buttons").css("display", "inline");
				$(".plupload_upload_status").css("display", "inline");
			}
		},
		// Post init events, bound after the internal events
		init : {
            StateChanged: function(up) {
                if (!uploadInitialized && up.state == plupload.STARTED) {
                        if (!validateProtoForm()) {
                            up.stop();
                            $(".plupload_buttons").css("display", "inline");
                            $(".plupload_upload_status").css("display", "inline");
                        } else {
                            uploadInitialized = true;
                        }
                    } else {
                        if (up.files.length === (up.total.uploaded + up.total.failed)) {
//                            $('#protoform')[0].submit();
                        }
                    }
                
            },

			PostInit: function() {
				// Called after initialization is finished and internal event handlers bound
			//	log('[PostInit]');
						},

            UploadProgress: function(up, file) {
                // Called while file is being uploaded
              //  log('[UploadProgress]', 'File:', file, "Total:", up.total);
            },
            FileUploaded: function(up, file, info, response) {
                // Called when file has finished uploading
             //   log('[FileUploaded] File:', file, "Info:", info);
                
                
                $.ajax({
					type: "POST",
					dataType: "json",
					url: "/php/d3r_filter.php", //Relative or absolute path to response.php file
					//data: data,
					success: function(data) {
						if(typeof(info.response) != "undefined" && info.response !== null) {
							$(".the-return").html(info.response);
                            $(".plupload_buttons").css("display", "inline");
                            $(".plupload_upload_status").css("display", "inline");

//                            if (info.response.substring(0, 4) == 'Warn') {
//                                $("<p><strong>status: Submission accepted with warnings</strong></p>").appendTo( ".the-return" );
//                                alert ("Submission accepted with warnings.");
//                                var nohash = window.location.href.split('#')[0];
//                                var noquestion = window.location.href.split('?')[0]
//                                $("<p>Click <a href='" + location.href + "'>reload</a> to see submission listing</p>").appendTo( ".the-return" );
                                //location.href = noquestion + "?nocache=" + (new Date()).getTime();
                            //}
						} else {	
							$("<strong>status: no validation errors found</strong>").appendTo( ".the-return" );
							alert ("No errors");
							var nohash = window.location.href.split('#')[0];
							var noquestion = window.location.href.split('?')[0]
							window.location.href = noquestion + "?nocache=" + (new Date()).getTime() + '#protocols';
						} 

//						if (info.response.length > 0) {
					}				
				});
					
            },
			UploadComplete: function(up, files) {
				// Called when all files are either uploaded or failed
  //              log('[UploadComplete]');
			},
            Error: function(up, args) {
                // Called when error occurs
                log('[Error] ', args);
            }
		}
	});

	function log() {
		var str = "";
		plupload.each(arguments, function(arg) {
			var row = "";

			if (typeof(arg) != "string") {
				plupload.each(arg, function(value, key) {
					// Convert items in File objects to human readable form
					if (arg instanceof plupload.File) {
						// Convert status to human readable
						switch (value) {
							case plupload.QUEUED:
								value = 'QUEUED';
								break;

							case plupload.UPLOADING:
								value = 'UPLOADING';
								break;

							case plupload.FAILED:
								value = 'FAILED';
								break;

							case plupload.DONE:
								value = 'DONE';
								break;
						}
					}

					if (typeof(value) != "function") {
						row += (row ? ', ' : '') + key + '=' + value;
					}
				});

				str += row + " ";
			} else { 
				str += arg + " ";
			}
		});
		var log = $('#log');
		log.append(str + "\n");
		log.scrollTop(log[0].scrollHeight);
	}
});
