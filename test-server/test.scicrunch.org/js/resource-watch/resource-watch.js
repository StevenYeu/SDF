$(document).ready(function () {
	$('[data-toggle=popover]').popover();

	$("#affiliates").owlCarousel({
		autoPlay: 2000,
		pagination: false
	});

	/** TODO Change to access Resource Watch API Endpoint **/
	var mydata = Array();
	json1 = {"name":'Cellosaurus', "y": 14296, "count": 14296};
	json2 = {"name":'Antibody Registry', "y": 48513,  "count": 48513};
	json3 = {"name":'Antibody Watch', "y": 69,  "count": 69};
	json4 = {"name":'Other', "y": 26918,  "count": 26918};
	mydata.push(json1);
	mydata.push(json2);
	mydata.push(json3);
	mydata.push(json4);


	Highcharts.chart('graph-term', {
		chart: {
			type: 'pie',
			options3d: {
				enabled: true,
				alpha: 45,
				beta: 0
			}
		},
		title: {
			text: 'Resource Watch Distribution by Source',
			style: {
				"color": "grey"
			}

		},
		tooltip: {
			formatter: function() {
				return '<b>' + this.point.options.name + '</b><br>Count: ' + this.point.options.count + " (" + this.percentage.toFixed(2) + "%)";
			}
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				depth: 35,
				dataLabels: {
					enabled: true,
					format: '{point.name}'
				}
			}
		},
		series: [{
			type: 'pie',
			name: 'Percentage',
			data: mydata
		}]
	});

});
