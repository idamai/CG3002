
$(document).ready(function(){
	
	
	regControl.init_search_bar();
	regControl.initAddProductPopup();
	regControl.initAddStorePopup();
	regControl.currentActive = regControl.constants.ACTIVE_NONE;
	
	$("#product-btn").off().on("click", function(){
		$("#loading-screen").removeClass("hidden");
		regControl.hideOrderButtons();
		regControl.retrieveProductList(0,regControl._ret_prod_cb);
		regControl.showAddProductButton();
		regControl.hideAddStoreButton();
		regControl.currentActive = regControl.constants.ACTIVE_PRODUCT;
		
	});
	$("#restock-all-product-btn").off().on("click", function(){
		$("#loading-screen").removeClass("hidden");
		regControl.hideOrderButtons();
		regControl.api_call(	{action:"restock_all_product"},
								regControl._ret_prod_cb,
								null);
		regControl.showAddProductButton();
		regControl.hideAddStoreButton();
	});
	$("#store-btn").off().on("click", function(){
		$("#loading-screen").removeClass("hidden");
		regControl.hideOrderButtons();
		regControl.retrieveStoreList(0,regControl._ret_store_cb);
		regControl.hideAddProductButton();
		regControl.showAddStoreButton();
		regControl.currentActive = regControl.constants.ACTIVE_STORE;
	});
	$("#order-btn").off().on("click", function(){
		$("#loading-screen").removeClass("hidden");
		regControl.showOrderButtons();
		regControl.retrieveOrderList(0,regControl._ret_order_list_cb);
		regControl.hideAddProductButton();
		regControl.hideAddStoreButton();
		regControl.currentActive = regControl.constants.ACTIVE_ORDER;
	});
	$("#shipment-btn").off().on("click", function(){
		$("#loading-screen").removeClass("hidden");
		regControl.hideOrderButtons();
		regControl.retrieveShippedList(0,regControl._ret_shipped_list_cb);
		regControl.hideAddProductButton();
		regControl.hideAddStoreButton();
		regControl.currentActive = regControl.constants.ACTIVE_SHIPMENT;
	});
	$("#pricing-btn").off().on("click", function(){
		$("#loading-screen").removeClass("hidden");
		regControl.hideOrderButtons();
		regControl.retrievePricingList(0,regControl._retrieve_pricing_list_cb);
		regControl.hideAddProductButton();
		regControl.hideAddStoreButton();
		regControl.currentActive = regControl.constants.ACTIVE_SHIPMENT;
	});
	$("#analytics-btn").off().on("click", function(){
		$("#loading-screen").removeClass("hidden");
		regControl.hideOrderButtons();
		regControl.performanceMetrics.init();
		regControl.hideAddProductButton();
		regControl.hideAddStoreButton();
		regControl.currentActive = regControl.constants.ACTIVE_METRICS;
	});
	$("#close-stock-popup").off().on("click", function(){
		if (!$("#view-stock-popup").hasClass("hidden"))
			$("#view-stock-popup").addClass("hidden");
			$('#stock-list-container').html("");
	});
	$("#import-all-btn").off().on("click", function(){
		$("#loading-screen").removeClass("hidden");
		regControl.showOrderButtons();
		regControl.api_call(	{action:"import_order_list"},
								regControl._ret_order_list_cb,
								null);
		
		regControl.hideAddProductButton();
		regControl.hideAddStoreButton();
	});
	$("#process-all-btn").off().on("click", function(){		
		$("#loading-screen").removeClass("hidden");
		regControl.api_call(	{action:"process_order_unprocessed"},
								regControl._process_all_order_cb,
								null);
	});
	$("#process-date-btn").off().on("click", function(){
		$("#loading-screen").removeClass("hidden");
		regControl.api_call(	{action:"populate_unprocessed_order_date"},
								regControl._populate_unprocessed_order_date_cb,
								null);
	});
	$("#process-barcode-btn").off().on("click", function(){
		$("#loading-screen").removeClass("hidden");
		regControl.api_call(	{action:"populate_unprocessed_order_barcode"},
								regControl._populate_unprocessed_order_barcode_cb,
								null);
	});
	$("#process-barcode-cfm").off().on("click",function(){
		$("#loading-screen").removeClass("hidden");
		var barcode;
		barcode = $("#order-barcode-input-selection select").val();
		regControl.api_call(	{action:"process_order_barcode", barcode:barcode},
									regControl._process_all_order_cb,
									null);
		if (!$("#process-barcode-popup").hasClass("hidden"))
				$("#process-barcode-popup").addClass("hidden");
		$("#order-barcode-input-selection").html("");		
	});
	$("#process-barcode-cancel").off().on("click",function(){
		if (!$("#process-barcode-popup").hasClass("hidden"))
				$("#process-barcode-popup").addClass("hidden");
		$("#order-barcode-input-selection").html("");		
	});
	$("#process-date-cfm").off().on("click",function(){
		$("#loading-screen").removeClass("hidden");
		var date;
		date = $("#order-date-input-selection select").val();
		regControl.api_call(	{action:"process_order_date", date:date},
									regControl._process_all_order_cb,
									null);
		if (!$("#process-date-popup").hasClass("hidden"))
				$("#process-date-popup").addClass("hidden");
		$("#order-date-input-selection").html("");		
	});
	$("#process-date-cancel").off().on("click",function(){
		if (!$("#process-date-popup").hasClass("hidden"))
				$("#process-date-popup").addClass("hidden");
		$("#order-date-input-selection").html("");
			if (!$("#process-date-popup").hasClass("hidden"))
				$("#process-date-popup").addClass("hidden");			
	});
});

