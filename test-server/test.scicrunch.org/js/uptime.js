$( document ).ready(function() {

	// uptime robot
	// api: https://uptimerobot.com/api
	// url: https://uptimerobot.com

	//uptime robot configs
	//v1
	//https://api.uptimerobot.com/getMonitors?apiKey=u261955-e9d7fc8fe0bfcfc073d54ea2&logs=1&customUptimeRatio=1-3-7-30&responseTimes=1&format=json&monitors=777180010-777180005-777180009-777180013-777180015-777180016-777180019-777180018-777181081-777181085-777180034-777180030-777180027&_=1446499206408
//	urBaseUrl 	= 'https://api.uptimerobot.com';
//	urApiKey 	= 'apiKey=u261955-e9d7fc8fe0bfcfc073d54ea2';
//	urOptions	= 'logs=1&customUptimeRatio=1-3-7-30-365&showTimezone=1';
//	urFormat 	= 'format=json&noJSONCallback=1';
//	urMonitors 	= 'monitors=777180010-777180005-777180009-777180013'
//				+ '-777180015-777180016-777180019-777180018-777181081'
//				+ '-777181085-777180034-777180030-777180027-777220674-777220537';

	// v2
	ur_base_url 	= 'https://api.uptimerobot.com/v2/getMonitors';
	ur_api_key 	= 'api_key=u261955-e9d7fc8fe0bfcfc073d54ea2';
	ur_options	= 'logs=1&custom_uptime_ratios=1-3-7-30&timezone=1';
	ur_format 	= 'format=json&no_json_callback=1';
	ur_monitors 	= 'monitors=777180010-777180005-777180009-777180013'
				+ '-777180015-777180016-777180019-777180018-777181081'
				+ '-777181085-777180034-777180030-777180027-777220674-777220537';

	$('html, body').css("cursor", "wait");

	$(window).on('load', function () {
		updateDashboard();
	});

	$('body').on('click', '.refresh', function(e) {
		$('html, body').css("cursor", "wait");
		updateDashboard();
	});

//	$('body').on('click', '#all', function(e) {
//		alert('here');
//	});

    // popover
    $('[data-toggle="popover"]').popover({
        html: true,
        trigger: 'hover',
        placement: 'auto'
    });

    // dynamically generated popovers
    $('body').popover({
        selector: '[data-toggle=popover]',
        html: true,
        trigger: 'hover',
        placement: 'auto',
    });

	// close popovers containg highcharts when clicked on "x"
	$(document).on('click', '.popover-hide', function(){
		$('.popover').popover('hide');
	});
	// close popovers containg highcharts when clicked on "x" for mobile
	$(document).on('tap', '.popover-hide', function(){
		$('.popover').popover('hide');
	});

	// close popovers containg highcharts when clicked outside
	// closes chart when reseting zoom, disabling until find a fix
//	$('body').on('click', function (e) {
//		alert($(e.target).attr("class"));
//		if( $(e.target).hasClass('highcharts-button')) {
//			alert('here');
//		}
//	    //did not click a popover toggle or popover
//	    if ($(e.target).data('toggle') !== 'popover'
//	        && $(e.target).parents('.popover.in').length === 0) {
//	        $('.popover').popover('hide');
//	    }
//	});

	$("body").bind("DOMNodeInserted", function() {
		var elem = $(this).find('.popover-chart').parent();
		elem.parent().addClass('popover-centered');
		$('.popover-centered').find('.arrow').addClass('hide');
		//elem.parent().wrapInner('<div class="popover-parent"></div>');

		var elem2 = $(this).find('#all-hosts').parent();
		elem2.parent().addClass('popover-centered');
		$('.popover-centered').find('.arrow').addClass('hide');
	});

	$("td.service").click(function() {
		var url = $(this).children().data('url');
		//alert(url);

		$.ajax({
			type: 'GET',
			url: url,
			success: function(response) {
				$('html, body').css("cursor", "auto");
				if (response) {
					$('#details-modal-title').html(response.host);
					$('#details-modal-body').html("<pre>"+JSON.stringify(response, undefined, 2)+"</pre>");
					$('#details-modal').modal('show');
					//alert(JSON.stringify(response));
				}
			},
            error: function(xhr, status, err) {
            	$('html, body').css("cursor", "auto");
            	alert(err);
            	alert( xhr.responseText);
            }
		});

	});

	function updateDashboard () {
		// get machines status from uptime robot
		// uptime robot v1 call (GET):
		//var url = urBaseUrl + "/getMonitors?" + urApiKey + '&' + urFormat + '&' + urMonitors + '&' + urOptions;

		// v2:
		var url = ur_base_url;
		var data = ur_api_key + '&' + ur_format + '&' + ur_monitors + '&' + ur_options;
		var downresources = [];

		// clear bootstrap alerts from previous call
		$('#alert').removeClass("alert alert-success alert-danger alert-info").html('');

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            cache: false,
            dataType: 'json',
            success: function(result){
            	$('html, body').css("cursor", "auto");
               //console.log(JSON.stringify(result));
            	var tz_offset = result.timezone;
            	// v1
            	//var monitors = result.monitors.monitor;

            	var monitors = result.monitors;
            	//console.log(monitors[0]);

            	if ( monitors.length > 0 ) {
            		var allHostsChart = '<table id="all-hosts"><thead>' +
            								'<th>Host</th>' +
            								'<th>Data</th></thead><tbody>';
            		var datasets = [],
            		$hosts = [];

	                $.each(monitors, function(i, item) {
	                	//alert(i + ' ' + item.url);
	                	var $host = $('#'+item.id).val();
	                	var hostparts = $host.split('.');
	                	$hosts.push(hostparts[0]);

	                	var tr = $('span.hostname:contains("'+$host+'")').closest('tr');
	                	//alert(tr.html());

	                	var span = '';
	                	if (item.status == 2){
	                		span = '<span class="glyphicon glyphicon-thumbs-up glyphicon-success" data-toggle="popover" data-content="It is up!"></span>';
	                	} else {
	                		downresources.push($host);
	                		span = '<span class="glyphicon glyphicon-thumbs-down glyphicon-danger" data-toggle="popover" data-content="It is down!"></span>';
	                	}
	                	span += ' <i class="fa fa-medkit glyphicon-info" data-toggle="popover" data-content="Debug / create github issue" data-host="'+$host+'"></i>';
	                	tr.children('.status').html(span);

	                	// uptime ratio charts
	                	// v1
	                	//var arr = item.customuptimeratio.split('-');

	                	var arr = item.custom_uptime_ratio.split('-');
	            		var html = "<span data-content='Click to toggle graph' data-toggle='popover' class='glyphicon glyphicon-signal glyphicon-info'></span> " +
	    				"<span>[1: "+parseFloat(arr[0]).toFixed(1)+"]&nbsp; [3: "+parseFloat(arr[1]).toFixed(1)+"]&nbsp; [7: "+parseFloat(arr[2]).toFixed(1)+"]</span>";

	                	tr.children('.percents')
	                		.html(html);


	            		function getUpDownChartData(container, logs) {
	            			return {
	            		        chart: {
	            		            type: 'area',
	                	            renderTo: container,
	                	            height: 150,
	                	            zoomType: 'x',
	                	            resetZoomButton: {
	                	                position: {
	                	                    align: 'left', // right by default
	                	                    verticalAlign: 'top',
	                	                    x: 10,
	                	                    y: 10
	                	                },
	                	                relativeTo: 'chart'
	                	            }
	            		        },
	            		        credits: {
	            		            enabled: false
	            		        },
	            		        title: {
	            		            text: 'Status Change Logs'
	            		        },
	            		        subtitle: {
	            		        	text: '[hold mouse down and drag to zoom]'
	            		        },
	            		        legend: {
	            		            enabled: false,
	            		        },
	            		        xAxis: {
	            		            allowDecimals: true,
	            		            type: 'datetime',
		            	            dateTimeLabelFormats : {
		            	            	day: '%b %e'
			            	        },
			            	        labels: {
			            	        	rotation: 315,
			            	        }
	            		        },
	            	            labels:{
	            	            	formatter :function(){
		            	                //console.log('here' + this.value);
		            	                return Highcharts.dateFormat('%H:%M', this.value);
	            	            	}
	            	            },
	            		        yAxis: {
	            		            title: {
	            		                text: 'Status'
	            		            },
	            		            gridLineColor: 'transparent',
	            		            tickPositions: [1, 0],
	            		        },
	            		        tooltip: {
	            		            pointFormat: 'Status: <b>{point.y:,.0f}</b>'
	            		        },
	            		        plotOptions: {
	            		            area: {
	            		                marker: {
	            		                    enabled: false,
	            		                    symbol: 'circle',
	            		                    radius: 1,
	            		                    states: {
	            		                        hover: {
	            		                            enabled: true
	            		                        }
	            		                    }
	            		                }
	            		            }
	            		        },
	            		        series: [{
	            		            name: 'Status Logs',
	            		            data: logs,
	            		            step: true,
	            		            showInLegend: false,
	            		        }]
	            			}
	            		}

	                    function getRatioData (container, percent, label, color){
	                    	return {
	                            chart: {
	                                type: 'solidgauge',
	                                renderTo: container,
	                            },
	                            credits: {
	                                enabled: false
	                            },
	                            title: null,
	                            pane: {
	                                center: ['50%', '85%'],
	                                size: '100%',
	                                startAngle: -90,
	                                endAngle: 90,
	                                background: {
	                                    backgroundColor: '#fff',
	                                    innerRadius: '60%',
	                                    outerRadius: '100%',
	                                    shape: 'arc'
	                                }
	                            },
	                            tooltip: {
	                                enabled: true
	                            },
	                            // the value axis
	                            yAxis: {
	                                lineWidth: 0,
	                                minorTickInterval: null,
	                                tickPixelInterval: 400,
	                                tickWidth: 0,
	                                title: {
	                                    y:0
	                                },
	                                min: 0,
	                                max: 100,
	                                title: {
	                                    text: label,
	                                    y: -30
	                                },
	                                labels: {
	                                    enabled: false
	                                },
	                                stops: [
	                                    [0.1, color], // green
	                                    [0.5, color], // yellow
	                                    [0.9, color] // red
	                                ],
	                            },
	                            series: [{
	                                name: label,
	                                data: [parseFloat(percent)],
	                                dataLabels: {
	                                    format: '{y} %',
	                                    y: 25
	                                },
	                                tooltip: {
	                                    valueSuffix: ' %'
	                                }
	                            }],
	                            plotOptions: {
	                                solidgauge: {
	                                    dataLabels: {
	                                        y: 0,
	                                        borderWidth: 0,
	                                        useHTML: true
	                                    }
	                                }
	                            }
	                            //

	                    	}
	                    }

	                	var logs = [];
	                	// v1
	                	//$.each(item.log, function(ii, log){
	                	$.each(item.logs, function(ii, log){
	                		if(log.type == 2 || log.type == 1) {
	                			var tmp = parseInt(log.type);
	                			var type = 1;
	                			if (tmp == 1) {
	                				type = 0;
	                			}
	                			var datetime = new Date(log.datetime + tz_offset/60);
	                			//console.log(datetime);
	                			var time = parseInt(Date.parse(datetime));
	                			//console.log(time);
	                			logs.push([time, type]);
	                			//logs.push([log.datetime, type]);
	                		}
	                	});
	                    logs.sort(function(a, b) {
	                        return a[0] - b[0];
	                    });
	                    datasets.push(logs);

	            		var scid = "status-chart" + item.id;
	                    var d1id = 'd1chart' + item.id;
	                    var d3id = 'd3chart' + item.id;
	                    var d7id = 'd7chart' + item.id
	                    var d30id = 'd30chart' + item.id;
	                    var graphs = '<div style="width: 430px; height: 150px; margin: 0 auto">' +
	                    			 	'<div class="text-center" style="font-size:20px;">Uptime Ratios</div>' +
	                    				'<div id="'+d1id+'" style="width: 100px; height: 100px;  float: left"></div>' +
	                    				'<div id="'+d3id+'" style="width: 100px; height: 100px;  float: left"></div>' +
	                    				'<div id="'+d7id+'" style="width: 100px; height: 100px;  float: left"></div>' +
	                    				'<div id="'+d30id+'" style="width: 100px; height: 100px;  float: left"></div>' +
	                    			'</div>' +
	                    			'<div id="'+scid+'" class="popover-chart"> </div>';

	            		var pop = tr.children('.percents').popover({
	            		    content: graphs,
	            			title: '<a class="close popover-hide">&times;</a><h4>' + $host + '</h4>',
	            		    html: true,
	            		    mode:'single',
	            		}).click(function() {
	            			var d1data = getRatioData(d1id,arr[0], '1 Day','#f7a35c');
	            			var d1chart = new Highcharts.Chart(d1data);
	            			var d3data = getRatioData(d3id,arr[1], '3 Days','#8085e9');
	            			var d3chart = new Highcharts.Chart(d3data);
	            			var d7data = getRatioData(d7id,arr[2], '7 Days','#990000');
	            			var d7chart = new Highcharts.Chart(d7data);
	            			var d30data = getRatioData(d30id,arr[2], '30 Days','#bce8f1');
	            			var d30chart = new Highcharts.Chart(d30data);
	            		    var status_chart_data = getUpDownChartData(scid, logs);
	            		    var schart = new Highcharts.Chart( status_chart_data );
	            		    $(this).show();
	            		});

	                	tr.children('.uptimes').html('<span class="glyphicon glyphicon-stats glyphicon-info" data-toggle="popover" data-content="Coming soon!"></span>');

	                	var hostparts = $host.split('.');
	                	//allHostsChart += '<tr><th>'+$host+'</th><td id="'+hostparts[0]+'" data-sparkline="'+logs+ ';'+hostparts[0]+'" /></tr>';
	                	allHostsChart += '<tr><th>'+$host+'</th><td /></tr>';
	                });

	                allHostsChart += '</tbody></table>';

            	}

            	var alert = "<span class='pull-right' id='all-charts'>[ Overall Graph ]</span> <br>";
                //var alert = "";
            	if (downresources.length > 0) {
                	alert += "<span class='pull-right refresh' data-toggle='popover' data-content='Click to refresh data'>[ Refresh Data ]</span><span><strong>These resources are down:</strong><br> " + downresources.join(", ") + "</span>";
                	$('#alert').html(alert).addClass("alert alert-danger");
                }
                else {
                	alert += "<span class='pull-right refresh' data-toggle='popover' data-content='Click to refresh data'>[ Refresh Data ]</span><span><strong>All resources are up!</strong></span>";
                	$('#alert').html(alert).addClass("alert alert-success");
                }


                var popover = $('#all-charts').popover({
        		    content: allHostsChart,
        			title: '<a class="close popover-hide">&times;</a><h4>Overall Status Change Logs</h4>',
        		    html: true,
        		    mode:'single',
        		}).click(function() {

        			var charts = [],
        		    $containers = $('#all-hosts td');

        			$.each(datasets, function(i, dataset) {
        			    charts.push(new Highcharts.Chart({
        			    	chart: {
                                renderTo:  $containers[i],
                                backgroundColor: null,
                                borderWidth: 0,
                                type: 'area',
                                margin: [2, 0, 2, 0],
                                width: 200,
                                height: 25,
                                style: {
                                    overflow: 'visible'
                                },
                                //skipClone: true
                            },
                            title: {
                                text: ''
                            },
                            credits: {
                                enabled: false
                            },
            	            labels:{
            	            	formatter :function(){
	            	                //console.log('here' + this.value);
	            	                return Highcharts.dateFormat('%H:%M', this.value);
            	            	}
            	            },
                            xAxis: {
                                labels: {
                                    enabled: false
                                },
                                title: {
                                    text: null
                                },
                                startOnTick: false,
                                endOnTick: false,
                                tickPositions: [1, 0],
                                type: 'datetime',
                                dateTimeLabelFormats : {
	            	            	day: '%b %e'
		            	        },
                            },
                            yAxis: {
                                endOnTick: false,
                                startOnTick: false,
                                labels: {
                                    enabled: false
                                },
                                title: {
                                    text: null
                                },
                                tickPositions: [1, 0],
            		            gridLineColor: 'transparent',
                            },
                            legend: {
                                enabled: false
                            },
                            tooltip: {
                                backgroundColor: 'white',
                                borderWidth: 0,
                                shadow: false,
                                useHTML: false,
                                hideDelay: 0,
                                shared: true,
                                padding: 0,
                                positioner: function (w, h, point) {
                                    return { x: point.plotX - w / 2, y: point.plotY - h };
                                	//return { x: 10, y: 10 };
                                }
                            },
                            plotOptions: {
                                series: {
                                    animation: true,
                                    lineWidth: 1,
                                    shadow: false,
                                    states: {
                                        hover: {
                                            lineWidth: 1
                                        }
                                    },
                                    marker: {
                                        radius: 1,
                                        states: {
                                            hover: {
                                                radius: 1
                                            }
                                        }
                                    },
                                    fillOpacity: 0
                                },
                                column: {
                                    negativeColor: '#910000',
                                    borderColor: 'black'
                                }
                            },
                            series: [{
                                data: dataset,
                                step: true,
                                showInLegend: false,
                            }],
                            tooltip: {
                            	useHTML: true,
                                pointFormat: 'Status: {point.y:,.0f} (' + $hosts[i] + ') '
                            },


        			    }));
        			});

        		    $(this).show();
        		});

            },
            error: function(xhr, status, err) {
            	$('html, body').css("cursor", "auto");
            	alert(err);
            	alert( xhr.responseText);
            }
        });
	}

});
