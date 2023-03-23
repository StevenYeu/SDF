(function(){

	var app = angular.module("termDashboardApp", ["ui.bootstrap", "term"]);


	app.controller('termDashboardCtrl',
			["$scope", "$log", "term", "termTypeCounts", "termCurieCounts", "termAffiliates",
	        function($scope, $log, term, termTypeCounts, termCurieCounts, termAffiliates) {

		var that = this;
		$("#affiliates").owlCarousel({
            autoPlay: 2000,
            pagination: false
		});

//	    // popover
//	    $('[data-toggle="popover"]').popover({
//	        html: true,
//	        trigger: 'hover',
//	        placement: 'auto'
//	    });
//
//	    // dynamically generated popovers
//	    $('body').popover({
//	        selector: '[data-toggle=popover]',
//	        html: true,
//	        trigger: 'hover',
//	        placement: 'auto',
//	    });
//
//		// close popovers containg highcharts when clicked on "x"
//		$(document).on('click', '.popover-hide', function(){
//			$('.popover').popover('hide');
//		});
//		// close popovers containg highcharts when clicked on "x" for mobile
//		$(document).on('tap', '.popover-hide', function(){
//			$('.popover').popover('hide');
//		});

//		$scope.affiliates = [];
//		termAffiliates.fetch().then(function(r){
//			$log.log(r.data);
//			$scope.affiliates = r.data;
//		});

		var data = [];
		termCurieCounts.fetch('term').then(function(r){
			d = r.data;
			var mydata = Array();
			for(i=0;i<d.length;i++) {
				if(d[i].name == 'Total') {
					//$scope.total = d[i].count;
					continue;
				}
				// console.log(d[i]);
				var json = {"name":d[i].prefix, "y":d[i].percent, "long_name":d[i].name, "count":d[i].count};
				mydata.push(json);
			}

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
			        text: 'InterLex distribution by Term source'
			    },
			    tooltip: {
			    	formatter: function() {
			    		return '<b>' + this.point.options.long_name + '</b><br>Count:' + this.point.options.count + " (" + this.percentage.toFixed(3) + "%)";
			        }
			        //pointFormat: '{series.name}: <br>{series.drilldown}{point.percentage:.2f}%'
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
		// termCurieCounts.fetch('cde').then(function(r){
		// 	d = r.data;
		// 	var mydata = Array();
		// 	for(i=0;i<d.length;i++) {
		// 		if(d[i].name == 'Total') {
		// 			//$scope.total = d[i].count;
		// 			continue;
		// 		}
		// 		var json = {"name":d[i].prefix, "y":d[i].percent, "long_name":d[i].name, "count":d[i].count};
		// 		mydata.push(json);
		// 	}
		// 	Highcharts.chart('graph-cde', {
		// 	    chart: {
		// 	        type: 'pie',
		// 	        options3d: {
		// 	            enabled: true,
		// 	            alpha: 45,
		// 	            beta: 0
		// 	        }
		// 	    },
		// 	    title: {
		// 	        text: 'InterLex distribution by CDE source'
		// 	    },
		// 	    tooltip: {
		// 	    	formatter: function() {
		// 	    		return '<b>' + this.point.options.long_name + '</b><br>Count:' + this.point.options.count + " (" + this.percentage.toFixed(3) + "%)";
		// 	        }
		// 	        //pointFormat: '{series.name}: <br>{series.drilldown}{point.percentage:.2f}%'
		// 	    },
		// 	    plotOptions: {
		// 	        pie: {
		// 	            allowPointSelect: true,
		// 	            cursor: 'pointer',
		// 	            depth: 35,
		// 	            dataLabels: {
		// 	                enabled: true,
		// 	                format: '{point.name}'
		// 	            }
		// 	        }
		// 	    },
		// 	    series: [{
		// 	        type: 'pie',
		// 	        name: 'Percentage',
		// 	        data: mydata
		// 	    }]
		// 	});
		// });
	}]);
}());
