regControl.performanceMetrics ={};

regControl.performanceMetrics.init = function() {
	
	regControl.performanceMetrics.drawPage();
	regControl.api_call(	{action:"retreive_all_barcode" },
								regControl.performanceMetrics._ret_all_bc_cb,
								null);
	regControl.performanceMetrics.init_buttons();
};

regControl.performanceMetrics.init_buttons = function () {
	$("#analayze-btn").off().on("click", function() {
		var barcode  = $("#barcode-selection-options").val();
		regControl.api_call(	{action:"retrieve_product_info", barcode:barcode },
								regControl.performanceMetrics._retrieve_prod_info_met_cb,
								null);
		regControl.api_call(	{action:"performance_metric", barcode:barcode},
								regControl.performanceMetrics._draw_product_chart_cb,
								null);
	});
};



//------------------------ Page Drawing Functions -----------------------//

regControl.performanceMetrics.drawPage = function() {
	var ml = '';
	ml+= '<div class = "clearfix"></div>';
	ml+= '<div id = "barcode-selection" class = "left">';
	ml+= 	'<select id="barcode-selection-options">';
	ml+=	'</select>';
	ml+= 	'<div class = "btn btn-primary" id = "analayze-btn">Analyze</div>';
	ml+= '</div>';	
	ml+= '<div id = "performance-metrics" class = "right"></div>';	
	ml+= '<div class = "clearfix"></div>';
	ml+= '<div id = "product-info-metrics">';
	ml+= 	'<div>Product Barcode:&#9;&#9;<span id = "product-info-metrics-barcode"></span></div>';
	ml+= 	'<div>Product Name:&#9; &#9; &#9;<span id = "product-info-metrics-name"></span></div>';
	ml+= 	'<div>Product Category:&#9; &#9; <span id = "product-info-metrics-category"></span></div>';
	ml+= 	'<div>Product Manufacturer:&#9;<span id = "product-info-metrics-manufacturer"></span></div>';
	ml+= 	'<div>Product Cost:&#9;&#9; &#9;<span id = "product-info-metrics-cost"></span></div>';
	ml+= 	'<div>Product Min-Stock:&#9;&#9;<span id = "product-info-metrics-minstock"></span></div>';
	ml+= '</div>';
	$("#content-container").html(ml);
};

regControl.performanceMetrics.drawProductChart = function(performanceMetrics) {
		var arrayOfData = new Array();
		arrayOfData[0] = new Array();
		arrayOfData[0] = ['Month', 'Sales'];
		arrayOfData[1] = new Array();
		arrayOfData[1] = ['01/01', 0];
		for (var i = 0; i < performanceMetrics.length; i++) {
			arrayOfData[i+2] = new Array();
			arrayOfData[i+2] = [performanceMetrics[i].month+"/"+performanceMetrics[i].year, +performanceMetrics[i].sales];
		}
        var data = google.visualization.arrayToDataTable(arrayOfData);

        var options = {
          title: 'Product Sales Performance',
          hAxis: {title: 'Month',  titleTextStyle: {color: '#333'}},
          vAxis: {minValue: 0}
        };/*
		var data = google.visualization.arrayToDataTable([
          ['Year', 'Sales', 'Expenses'],
          ['2013',  1000,      400],
          ['2014',  1170,      460],
          ['2015',  660,       1120],
          ['2016',  1030,      540]
        ]);

        var options = {
          title: 'Company Performance',
          hAxis: {title: 'Year',  titleTextStyle: {color: '#333'}},
          vAxis: {minValue: 0}
        };*/

        var chart = new google.visualization.AreaChart(document.getElementById('performance-metrics'));
        chart.draw(data, options);
      }
//------------------------ Callback Functions -----------------------//

regControl.performanceMetrics._ret_all_bc_cb = function(data) {
	if (data.status==regControl.constants.OK){
		var i;
		var ml = '';
		for ( i = 0; i < data.result.length; i++) {
			ml += '<option value="'+data.result[i]+'">'+data.result[i]+'</option>';
		}
		$("#barcode-selection-options").html(ml);		
		$("#loading-screen").addClass("hidden");
	} else {		
		alert("operation fail");
	}
};

regControl.performanceMetrics._draw_product_chart_cb = function(data) {
	if (data.status==regControl.constants.OK){
		regControl.performanceMetrics.drawProductChart(data.result);
	} else {		
		alert("operation fail");
	}
}
regControl.performanceMetrics._retrieve_prod_info_met_cb = function(data) {
	if (data.status==regControl.constants.OK){
		$("#product-info-metrics-barcode").html(data.result.barcode);
		$("#product-info-metrics-name").html(data.result.name);
		$("#product-info-metrics-category").html(data.result.category);
		$("#product-info-metrics-manufacturer").html(data.result.manufacturer);
		$("#product-info-metrics-cost").html(data.result.cost);
		$("#product-info-metrics-minstock").html(data.result.minimal_stock);
	} else {		
		alert("operation fail");
	}
}