/*
	This regControl class is the main controller for the regional system
*/
window.regControl = window.regControl || {};

//setup
regControl.api_url = "/api/api.php";
regControl.api_path = "/api/";
regControl.data_chunk_size = 10;
regControl.mlArray = new Array();
regControl.mlArraySearch = new Array();
regControl.mlArrayPaged = new Array();


//for future mobile checker
regControl.isMobile = function() {
	var check = false;
	(function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
	return check;
};

//constants
regControl.constants = {   OK : "ok",
						   FAIL : "fail",
						   NETWORK_FAIL : "network",
						   LOGIN : "login",
						   ACTIVE_NONE : "none-active",
						   ACTIVE_PRODUCT : "product-list",
						   ACTIVE_STORE : "store-list",
						   ACTIVE_ORDER : "order-list",
						   ACTIVE_SHIPPED : "shipped-list",
						   ACTIVE_PRICING : "price-list"};
						   
regControl.currentActive;						   
//standardized ajax api call						   
regControl.api_call = function (senddata, callback, ctx){
	$.post(regControl.api_url,
		   {data: senddata},
		   function(data){
			   data._network = regControl.constants.OK;
			   callback.call(ctx || null, data);
		   },
		   "json")
		.error(function(data){
			data._network = regControl.constants.FAIL;
			callback.call(ctx || null, data);
		});
}; 

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
//--------------------api calls-----------------------
regControl.retrieveProductList = function(offset,callback){
	regControl.api_call(	{action:"retrieve_product", offset:offset},
								callback,
								null);
};
regControl.retrieveStoreList = function(offset,callback){
	regControl.api_call(	{action:"retrieve_store", offset:offset},
									callback,
									null);
};
regControl.retrieveOrderList = function(offset,callback){
	regControl.api_call(	{action:"retrieve_order_list", offset:offset},
								callback,
								null);
};
regControl.retrieveShippedList = function(offset, callback){
	regControl.api_call(	{action:"retrieve_shipped_list", offset:offset},
									callback,
									null);
};
regControl.retrievePricingList = function(offset, callback){
	regControl.api_call(	{action:"retrieve_pricing_list", offset:offset},
								callback,
								null);
};
regControl.hideAddProductButton = function() {
	if (!$("#add-new-product-holder").hasClass("hidden"))
		$("#add-new-product-holder").addClass("hidden");
}


regControl.showAddProductButton = function(){
	$("#add-new-product-holder").removeClass("hidden");
}

regControl.hideAddStoreButton = function() {
	if (!$("#add-new-store-holder").hasClass("hidden"))
		$("#add-new-store-holder").addClass("hidden");
}


regControl.showAddStoreButton = function(){
	$("#add-new-store-holder").removeClass("hidden");
}

regControl.hideOrderButtons = function(){
	if (!$("#process-order-btns").hasClass("hidden"))
		$("#process-order-btns").addClass("hidden");
}

regControl.showOrderButtons = function(){
	$("#process-order-btns").removeClass("hidden");
}
regControl.initAddProductPopup = function() {
		$("#add-new-product-btn").off().on('click',function(){
			$("#add-edit-product-cfm").html("Add Product");
			$("#add-edit-product").removeClass("hidden");	
			$("#new-product-barcode").prop('disabled', false);		
			$("#new-product-barcode").val("");
			$("#new-product-name").val("");
			$("#new-product-category").val("");
			$("#new-product-manufacturer").val("");
			$("#new-product-cost").val("");
			$("#new-product-minstock").val("");
		});
		
		$("#add-edit-product-cfm").off().on('click',function() {
			//ajax
			var barcode = $("#new-product-barcode").val();
			var name = $("#new-product-name").val();
			var category = $("#new-product-category").val();
			var manufacturer = $("#new-product-manufacturer").val();
			var cost = $("#new-product-cost").val();
			var minstock = $("#new-product-minstock").val();
			regControl.api_call(	{action:"add_new_product",barcode:barcode,name:name,category:category,manufacturer:manufacturer,cost:cost,minimal_stock:minstock},
									regControl._ret_prod_cb,
									null);
			if (!$("#add-edit-product").hasClass("hidden"))
				$("#add-edit-product").addClass("hidden");
		});
		
		$("#add-product-cncl").off().on('click',function(){
			if (!$("#add-edit-product").hasClass("hidden"))
				$("#add-edit-product").addClass("hidden");
		});
}
regControl.initAddStock = function() {
	var barcode;	
	$('#stock-batchdate').val(new Date().toJSON().slice(0,10));
	
	$("#add-stock-btn").off().on("click",function(){
		barcode = $(this).data("barcode");
		$(".stock-item-barcode").html(barcode);
		$("#add-stock-popup").removeClass("hidden");
	});
	$("#add-stock-cfm").off().on("click",function(){
		batchDate = $("#stock-batchdate").val();
		quantity = $("#stock-quantity").val();
		regControl.api_call(	{action:"receive_stock",barcode:barcode,batchdate:batchDate,quantity:quantity},
									regControl._ret_stock_cb,
									null);
		if (!$("#add-stock-popup").hasClass("hidden"))
			$("#add-stock-popup").addClass("hidden");						
	});
	$("#add-stock-cncl").off().on("click",function(){
		if (!$("#add-stock-popup").hasClass("hidden"))
			$("#add-stock-popup").addClass("hidden");
		$("#stock-batchdate").val("");
		$("#stock-quantity").val("");
	});
}
regControl.initDiscardStock = function(barcode) {
	var batchdate;
	var current;
	var barcode;
	$(".discard-stock-btn").off().on("click",function(){
		barcode = $(this).data("barcode");
		batchdate = $(this).data("batchdate");
		current = $(this).data("stock");
		$(".stock-item-barcode").html(barcode);
		$(".stock-item-batchdate").html(batchdate);
		$("#discard-stock-popup").removeClass("hidden");
	});
	
	$("#discard-stock-cfm").off().on("click",function(){
		quantity = $("#discard-quantity").val();
		if (quantity > current)
			alert("You want to discard more stocks than the available stocks!");
		else {
			var bool = confirm("You will discard batch on "+batchdate+" as many as "+quantity+" units of product "+barcode);
			if (bool) {
				regControl.api_call(	{action:"update_batch_stock",barcode:barcode,date:batchdate,quantity:(current-quantity)},
											regControl._ret_stock_cb,
											null);
			
				if (!$("#discard-stock-popup").hasClass("hidden"))
				$("#discard-stock-popup").addClass("hidden");
			}
		}			
	});
	$("#discard-stock-cncl").off().on("click",function(){
		if (!$("#discard-stock-popup").hasClass("hidden"))
			$("#discard-stock-popup").addClass("hidden");
		$("#discard-quantity").val("");
	});
}

regControl.init_search_bar = function(){
	$("#search-bar").keyup(function(e) {
		  if(e.which == 13) {
				  var userInput = $(this).val();
					/*$("#content-container table tbody tr").map(function(index, value) {
						$(value).toggle($(value).text().toLowerCase().indexOf(userInput) >= 0);
					});*/
				  var callback_fn;
				  var api_call;
				  switch(regControl.currentActive) {
					case regControl.constants.ACTIVE_NONE:
						break;
					case regControl.constants.ACTIVE_PRODUCT:
						callback_fn = regControl._ret_prod_cb;
						api_call = regControl.retrieveProductList;
						break;
					case regControl.constants.ACTIVE_STORE:
						callback_fn = regControl._ret_store_cb;
						api_call = regControl.retrieveStoreList;
						break;			
					case regControl.constants.ACTIVE_ORDER:
						callback_fn = regControl._ret_order_list_cb;
						api_call = regControl.retrieveOrderList;
						break;
					case regControl.constants.ACTIVE_SHIPPED:
						callback_fn = regControl._ret_shipped_list_cb;
						api_call = regControl.retrieveShippedList;
						break;			
					case regControl.constants.ACTIVE_PRICING:
						callback_fn = regControl._retrieve_pricing_list_cb;
						api_call = regControl.retrievePricingList;
						break;
				  }
			  
				if (regControl.currentActive != regControl.constants.ACTIVE_NONE) {
					if (userInput!='' && userInput!= null) {
						regControl.api_call(	{action:"search_data_base",key:userInput,mode:regControl.currentActive},
													callback_fn,
													null);
					} else {
						api_call(0,callback_fn);
					}
				}
		}
	});
};

regControl.initAddStorePopup = function() {
	$("#add-new-store-btn").off().on('click',function(){
		$("#add-edit-store-cfm").html("Add Store");
		$("#add-edit-store").removeClass("hidden");	
		$("#new-store-id").prop('disabled', false);
		$("#new-store-id").val("");
		$("#new-store-name").val("");
		$("#new-store-location").val("");
		$("#new-store-password").val("");
		$("#new-store-confirmPass").val("");
	});
	
	$("#add-edit-store-cfm").off().on('click',function() {
		//ajax
		var storeID = $("#new-store-id").val();
		var name = $("#new-store-name").val();
		var location = $("#new-store-location").val();
		var password = $("#new-store-password").val();
		var confirmPass = $("#new-store-confirmPass").val();
		if (password == confirmPass){
			regControl.api_call(	{action:"add_store",store_id:storeID,name:name,location:location,password:password},
									regControl._ret_store_cb,
									null);
			if (!$("#add-edit-store").hasClass("hidden"))
				$("#add-edit-store").addClass("hidden");
		} else if ((password == null)||(password == "")){
			alert("You can't have an empty password!");
		} else
			alert("Your typed password does not match!");
	});
	
	$("#add-store-cncl").off().on('click',function(){
		if (!$("#add-edit-store").hasClass("hidden"))
			$("#add-edit-store").addClass("hidden");
	});
}

regControl.mlArrayPager = function () {
	var limit = 7;
	var pageCounter = 0;
	var i =0;
	regControl.mlArrayPaged = new Array();
	for (i = 0; i < regControl.mlArray.length; i++){
		if ( i%limit == 0) {
			if (i > 0)
				pageCounter++;
			regControl.mlArrayPaged[pageCounter] = new Array();			
		}
		regControl.mlArrayPaged[pageCounter][i%limit] = regControl.mlArray[i];		
	}
}
//---------------------paged drawing functions---------------------------
regControl.buildProductPagedArray = function(prodArray) {
	regControl.mlArray =new Array();
	for (i = 0; i< prodArray.length ; i++) {
		regControl.mlArray[i] = '';
		regControl.mlArray[i]+='<tr>';
		for (var propt in prodArray[i]) {
			regControl.mlArray[i]+='<td class ="'+propt+'">'+prodArray[i][propt]+'</td>';
		}
		regControl.mlArray[i]+=	'<td class = "view-stock-btn btn btn-small btn-inverse" data-barcode='+prodArray[i].barcode+' >Stock</td>';
		regControl.mlArray[i]+=	'<td class = "edit-product-btn btn btn-small btn-inverse" data-barcode='+prodArray[i].barcode+' >Edit</td>';
		regControl.mlArray[i]+=	'<td class = "delete-product-btn btn btn-small  btn-inverse" data-barcode='+prodArray[i].barcode+' >Delete</td>';
		regControl.mlArray[i]+=	'</tr>';
	}
	regControl.mlArrayPager();
}
regControl.buildStorePagedArray = function(storeArray) {
	regControl.mlArray =new Array();
	for (i = 0; i< storeArray.length ; i++) {
		regControl.mlArray[i] = '';
		regControl.mlArray[i]+='<tr>';
		for (var propt in storeArray[i]) {
			regControl.mlArray[i]+='<td class ="'+propt+'">'+storeArray[i][propt]+'</td>';
		}
		regControl.mlArray[i]+=	'<td class = "edit-store-btn btn btn-small btn-inverse" data-store-id='+storeArray[i].store_id+' >Edit Store</td>';
		regControl.mlArray[i]+=		'<td class = "delete-store-btn btn btn-small btn-inverse" data-store-id='+storeArray[i].store_id+' >Delete Store</td>';
		regControl.mlArray[i]+=	'</tr>';
	}
	regControl.mlArrayPager();
}
regControl.buildOrderPagedArray = function(orderArray) {
	regControl.mlArray =new Array();
	for (i = 0; i< orderArray.length ; i++) {
		regControl.mlArray[i] = '';
		regControl.mlArray[i]+='<tr>';
		for (var propt in orderArray[i]) {
			regControl.mlArray[i]+='<td class ="'+propt+'">'+orderArray[i][propt]+'</td>';
		}
		regControl.mlArray[i]+=	'</tr>';
	}
	regControl.mlArrayPager();
}

regControl.buildShippedPagedArray = function(shippedArray) {
	regControl.mlArray =new Array();
	for (i = 0; i< shippedArray.length ; i++) {
		regControl.mlArray[i] = '';
		regControl.mlArray[i]+='<tr>';
		for (var propt in shippedArray[i]) {
			regControl.mlArray[i]+='<td class ="'+propt+'">'+shippedArray[i][propt]+'</td>';
		}
		regControl.mlArray[i]+=	'</tr>';
	}
	regControl.mlArrayPager();
}

regControl.buildPricingPagedArray = function(pricingArray) {
	regControl.mlArray =new Array();
	for (i = 0; i< pricingArray.length ; i++) {
		regControl.mlArray[i] = '';
		regControl.mlArray[i]+='<tr>';
		for (var propt in pricingArray[i]) {
			regControl.mlArray[i]+='<td class ="'+propt+'">'+pricingArray[i][propt]+'</td>';
		}
		regControl.mlArray[i]+=	'</tr>';
	}
	regControl.mlArrayPager();
}
//---------------------main controller functions---------------------------
regControl.productListController = function (prodArray,totalItems) {
	regControl.buildProductPagedArray(prodArray);
	var pageCounter = Math.ceil(totalItems/7);
	var ml='';
	ml+= '<div class = "table-wrapper"><table class = "table" id = "product-list">';
	ml+='</table></div>';
	ml+='<div id = "main-page-controller"></div>';
	$('#content-container').html(ml);
	if (regControl.mlArrayPaged.length>=1)
		regControl.drawProductList(0);
	else
		alert ("You do not have any product data");
	regControl.pageController(pageCounter,regControl.drawProductList,regControl.retrieveProductList,regControl._ret_prod_paged_cb);
	
}
regControl.storeListController = function (storeArray,totalItems) {
	regControl.buildStorePagedArray(storeArray);
	var pageCounter = Math.ceil(totalItems/7);
	var ml='';
	ml+= '<div class = "table-wrapper"><table class = "table" id = "store-list">';
	ml+='</table></div>';
	ml+='<div id = "main-page-controller"></div>';
	$('#content-container').html(ml);
	if (regControl.mlArrayPaged.length>=1)
		regControl.drawStoreList(0);
	else
		alert ("You do not have any store data");
	regControl.pageController(pageCounter,regControl.drawStoreList,regControl.retrieveStoreList,regControl._ret_store_paged_cb);
}

regControl.orderListController = function (orderArray,totalItems) {
	regControl.buildOrderPagedArray(orderArray);
	var pageCounter = Math.ceil(totalItems/7);
	var ml='';
	ml+= '<div class = "table-wrapper"><table class = "table" id = "order-list">';
	ml+='</table></div>';
	ml+='<div id = "main-page-controller"></div>';
	$('#content-container').html(ml);
	if (regControl.mlArrayPaged.length>=1)
		regControl.drawOrderList(0);
	else
		alert ("You do not have any order data");
	regControl.pageController(pageCounter,regControl.drawOrderList,regControl.retrieveOrderList,regControl._ret_order_list_paged_cb);
}
regControl.shippedListController = function (shippedArray,totalItems) {
	regControl.buildShippedPagedArray(shippedArray);
	var pageCounter = Math.ceil(totalItems/7);
	var ml='';
	ml+= '<div class = "table-wrapper"><table class = "table" id = "shipped-list">';
	ml+='</table></div>';
	ml+='<div id = "main-page-controller"></div>';
	$('#content-container').html(ml);
	if (regControl.mlArrayPaged.length>=1)
		regControl.drawShippedList(0);
	else
		alert ("You do not have any shipment data");
	regControl.pageController(pageCounter,regControl.drawShippedList,regControl.retrieveShippedList,regControl._ret_shipped_list_paged_cb);
}

regControl.pricingListController = function (pricingArray,totalItems) {
	regControl.buildPricingPagedArray(pricingArray);
	var pageCounter = Math.ceil(totalItems/7);
	var ml='';
	ml+= '<div class = "table-wrapper"><table class = "table" id = "pricing-list">';
	ml+='</table></div>';
	ml+='<div id = "main-page-controller"></div>';
	$('#content-container').html(ml);
	if (regControl.mlArrayPaged.length>=1)
		regControl.drawPricingList(0);
	else
		alert ("You do not have any pricing data");
	regControl.pageController(pageCounter,regControl.drawPricingList,regControl.retrievePricingList,regControl._ret_pricing_list_paged_cb);
}

regControl.pageController = function(pageCounter,drawFunction,apiCall,callback){
	var perPageLimit = 10;
	var i;
	var ml='';	
	for (i =0; i< pageCounter;i++) {
		if (i%perPageLimit == 0){
			if (i > 0){
				if (i<pageCounter-1)
					ml+='<span class = "next-paging">Next</span>';
				ml+='</div>';
			}
			ml+= '<div id="paging-'+(i/perPageLimit)+'" data-cur-paging = '+i/perPageLimit+' class = "page-scroller hidden">';
			if (i > 0) {
				ml+= '<span class = "prev-paging">Prev</span>';
			}
		}
		ml+='<span class = "page-controller" data-page='+i+'>'+(i+1)+'</span>';
	}
	
	$("#main-page-controller").html(ml);
	$("#paging-0").removeClass("hidden");
	$(".next-paging").off().on("click",function(){
		var nextPaging = $(this).parent().data("cur-paging")+1;
		$(this).parent().addClass("hidden");
		var offset = nextPaging*70;
		apiCall(offset,callback);
		$("#paging-"+nextPaging).removeClass("hidden");
	});
	$(".prev-paging").off().on("click",function(){
		var nextPaging = $(this).parent().data("cur-paging")-1;
		$(this).parent().addClass("hidden");
		var offset = nextPaging*70;
		apiCall(offset,callback);
		$("#paging-"+nextPaging).removeClass("hidden");
	});
	$(".page-controller").off().on("click",function(){
		var curPage =  $(this).data("page");
		$(".active").removeClass("active");
		$(this).addClass("active");
		//regControl.drawProductList(curPage);
		curPage = curPage%10;
		drawFunction(curPage);
	});
}

regControl.initCRUDProdBtn = function() {
	$('.view-stock-btn').off().on("click", function(){
		itemBarcode = $(this).data("barcode");
		regControl.api_call(	{action:"retrieve_stock",barcode:itemBarcode},
								regControl._ret_stock_cb,
								null);
		$('#view-stock-popup').removeClass("hidden");
	});	
	$('.edit-product-btn').off().on("click", function(){
		itemBarcode = $(this).data("barcode");
		regControl.api_call(	{action:"retrieve_product_info",barcode:itemBarcode},
								regControl._edit_prod_cb,
								null);
	});	
	$('.delete-product-btn').off().on("click", function(){
		itemBarcode = $(this).data("barcode");
		var r = confirm('Are you sure you want to delete product '+itemBarcode+'? \n This process cannot be undone');
		if (r)
			regControl.api_call(	{action:"delete_product",barcode:itemBarcode},
									regControl._del_prod_cb,
									null);
	});	
}
regControl.initCRUDStoreBtn = function() {
	$('.edit-store-btn').off().on("click", function(){
		storeID = $(this).data("store-id");
		regControl.api_call(	{action:"retrieve_store_info",store_id:storeID},
								regControl._edit_store_cb,
								null);
	});	
	$('.delete-store-btn').off().on("click", function(){
		storeID = $(this).data("store-id");
		var r = confirm('Are you sure you want to delete store '+storeID+'? \n This process cannot be undone');
		if (r)
			regControl.api_call(	{action:"delete_store",store_id:storeID},
									regControl._del_store_cb,
									null);
	});	
}
regControl.drawProductList = function (page){
	var i=0;
	var j =0;
	var ml='';
	ml+= '			<tr>';
	ml+= '				<th>Barcode</th>';
	ml+= '				<th>Name</th>';
	ml+= '				<th>Category</th>';
	ml+= '				<th>Manufacturer</th>';
	ml+= '				<th>Cost</th>';
	ml+= '				<th style="width:150px">Properties</th>';
	//ml+= '				<th>Edit</th>';
	//ml+= '				<th>Delete</th>';
	ml+= '			</tr>';
	
	for (j = 0; j < regControl.mlArrayPaged[page].length; j++)
		ml+=regControl.mlArrayPaged[page][j];
	
	$('#product-list').html(ml);
	regControl.initCRUDProdBtn();
}

regControl.drawStoreList = function (page){
	var i=0;
	var j =0;
	var ml='';
	ml+=	'	<tr>';
	ml+=	'		<th>Store ID</th>';
	ml+=	'		<th>Store Name</th>';
	ml+=	'		<th>Location</th>';		
	ml+= 	'		<th>Properties</th>';
	//ml+= 	'		<th>Delete</th>';
	ml+=	'	</tr>';
	for (j = 0; j < regControl.mlArrayPaged[page].length; j++)
		ml+=regControl.mlArrayPaged[page][j];
	
	$('#store-list').html(ml);
	regControl.initCRUDStoreBtn();

}

regControl.drawOrderList = function (page){
	var i=0;
	var j =0;
	var ml='';
	ml+=	'	<tr>';
	ml+=	'		<th>Barcode</th>';
	ml+=	'		<th>Date</th>';
	ml+=	'		<th>Store ID</th>';
	ml+=	'		<th>Quantity</th>';
	ml+=	'	</tr>';
	for (j = 0; j < regControl.mlArrayPaged[page].length; j++)
		ml+=regControl.mlArrayPaged[page][j];	
	$('#order-list').html(ml);
}

regControl.drawShippedList = function (page){
	var i=0;
	var j =0;
	var ml='';
	ml+=	'	<tr>';
	ml+=	'		<th>Barcode</th>';
	ml+=	'		<th>Date</th>';
	ml+=	'		<th>Store ID</th>';
	ml+=	'		<th>Quantity</th>';
	ml+=	'	</tr>';
	for (j = 0; j < regControl.mlArrayPaged[page].length; j++)
		ml+=regControl.mlArrayPaged[page][j];	
	$('#shipped-list').html(ml);

}

regControl.drawPricingList = function (page){
	var i=0;
	var j =0;
	var ml='';
	ml+=	'	<tr>';
	ml+=	'		<th>Barcode</th>';
	ml+=	'		<th>Margin Multiplier</th>';
	ml+=	'		<th>Q*</th>';
	ml+=	'	</tr>';
	for (j = 0; j < regControl.mlArrayPaged[page].length; j++)
		ml+=regControl.mlArrayPaged[page][j];
	
	$('#pricing-list').html(ml);

}

// ------------------------------ CALLBACKS ------------------------------
//retrieve lists call backs
regControl._ret_prod_cb = function(data){
	if (data.status==regControl.constants.OK){
		if (data.result!= null)
			regControl.productListController(data.result, data.total);
		else
			alert("You do not have any product");
		$("#loading-screen").addClass("hidden");
	}else{
		alert("operation fail");
	}
};


regControl._ret_store_cb = function(data){
	if (data.status==regControl.constants.OK){		
		
		if (data.result!=null)
			regControl.storeListController(data.result, data.total);
		else
			alert("You don't have any store");
		$("#loading-screen").addClass("hidden");
	}else{
		alert("operation fail");
	}
};


regControl._ret_shipped_list_cb = function(data){
	if (data.status==regControl.constants.OK){
		if (data.result!=null)
			regControl.shippedListController(data.result, data.total);
		else
			alert("You don't have any shipment list!");
		$("#loading-screen").addClass("hidden");
	}else{
		alert("operation fail");
	}
};

regControl._retrieve_pricing_list_cb = function(data) {
	if (data.status==regControl.constants.OK){
		if (data.result!=null)
			regControl.pricingListController(data.result, data.total);
		else
			alert("You don't have any pricing list!");
		$("#loading-screen").addClass("hidden");
	}else{
		alert("operation fail");
	}
}

regControl._ret_order_list_cb = function(data){
	if (data.status==regControl.constants.OK){
		if (data.result!=null)
			regControl.orderListController(data.result, data.total);
		else
			alert("You have no unprocessed order");
		$("#loading-screen").addClass("hidden");
	}else{
		alert("operation fail");
	}
};
regControl._ret_prod_paged_cb = function(data){
	if (data.status==regControl.constants.OK){
		if (data.result!=null) {
			regControl.buildProductPagedArray(data.result);
			regControl.drawProductList(0);
		} else {
			alert("You have no more product");
		}
		$("#loading-screen").addClass("hidden");
	}else{
		alert("operation fail");
	}
}
regControl._ret_store_paged_cb = function(data){
	if (data.status==regControl.constants.OK){
		if (data.result!=null) {
			regControl.buildStorePagedArray(data.result);
			regControl.drawStoreList(0);
		} else {
			alert("You have no more store");
		}
		$("#loading-screen").addClass("hidden");
	}else{
		alert("operation fail");
	}
}
regControl._ret_order_list_paged_cb = function(data){
	if (data.status==regControl.constants.OK){
		if (data.result!=null) {
			regControl.buildOrderPagedArray(data.result);
			regControl.drawOrderList(0);
		} else {
			alert("You have no more unprocessed order");
		}
		$("#loading-screen").addClass("hidden");
	}else{
		alert("operation fail");
	}
}
regControl._ret_shipped_list_paged_cb = function(data){
	if (data.status==regControl.constants.OK){
		if (data.result!=null) {
			regControl.buildShippedPagedArray(data.result);
			regControl.drawShippedList(0);
		} else {
			alert("You have no more shipped order");
		}
		$("#loading-screen").addClass("hidden");
	}else{
		alert("operation fail");
	}
}
regControl._ret_pricing_list_paged_cb = function(data){
	if (data.status==regControl.constants.OK){
		if (data.result!=null) {
			regControl.buildPricingPagedArray(data.result);
			regControl.drawPricingList(0);
		} else {
			alert("You have no more pricing order");
		}
		$("#loading-screen").addClass("hidden");
	}else{
		alert("operation fail");
	}
}

regControl._edit_prod_cb = function(data) {
	if (data.status==regControl.constants.OK){
		$("#add-edit-product-cfm").html("Edit Product");
		$("#add-edit-product").removeClass("hidden");
		$("#new-product-barcode").prop('disabled', true);
		$("#new-product-barcode").val(data.result.barcode);
		$("#new-product-name").val(data.result.name);
		$("#new-product-category").val(data.result.category);
		$("#new-product-manufacturer").val(data.result.manufacturer);
		$("#new-product-cost").val(data.result.cost);
		$("#new-product-minstock").val(data.result.minimal_stock);
		
		
		$("#add-edit-product-cfm").off().on('click',function() {
		//ajax
			var barcode = $("#new-product-barcode").val();
			var name = $("#new-product-name").val();
			var category = $("#new-product-category").val();
			var manufacturer = $("#new-product-manufacturer").val();
			var cost = $("#new-product-cost").val();
			var minstock = $("#new-product-minstock").val();
			regControl.api_call(	{action:"edit_product",barcode:barcode,name:name,category:category,manufacturer:manufacturer,cost:cost,minimal_stock:minstock},
									regControl._ret_prod_cb,
									null);
			if (!$("#add-edit-product").hasClass("hidden"))
				$("#add-edit-product").addClass("hidden");
		});
	}else{
		alert("operation fail");
	}
}

regControl._del_prod_cb = function(data) {
	if (data.status==regControl.constants.OK){
		alert("Deleted product "+data.deletedBarcode);
		regControl.productListController(data.result);
		$("#loading-screen").addClass("hidden");
	}else{
		alert("operation fail");
	}
}

regControl._edit_store_cb = function(data) {
	if (data.status==regControl.constants.OK){
		$("#add-edit-store-cfm").html("Edit Store");
		$("#new-store-id").prop('disabled', true);
		$("#add-edit-store").removeClass("hidden");
		$("#new-store-id").val(data.result.store_id);
		$("#new-store-name").val(data.result.name);
		$("#new-store-location").val(data.result.location);
		$("#new-store-password").val("");
		$("#new-store-confirmPass").val("");
		$(".edit-store-msg").removeClass("hidden");
		$("#add-edit-store-cfm").off().on("click",function() {
			//ajax			
			var storeID = $("#new-store-id").val();
			var name = $("#new-store-name").val();
			var location = $("#new-store-location").val();
			var password = $("#new-store-password").val();
			var confirmPass = $("#new-store-confirmPass").val();
			if (password == confirmPass){
				regControl.api_call(	{action:"edit_store",store_id:storeID,name:name,location:location,password:password},
										regControl._ret_store_cb,
										null);
				if (!$("#add-edit-store").hasClass("hidden"))
					$("#add-edit-store").addClass("hidden");
				if (!$(".edit-store-msg").hasClass("hidden"))
					$(".edit-store-msg").removeClass("hidden");
			} else {
				alert("Your typed password does not match!");
			}
		});
	}else{
		alert("operation fail");
	}
}

regControl._del_store_cb = function(data) {
	if (data.status==regControl.constants.OK){
		alert("Succesfully deleted store "+data.store_id);
		regControl.storeListController(data.result);
		$("#loading-screen").addClass("hidden");
	}else {
		alert("Operation fail");
	}
}

regControl._ret_stock_cb = function(data){
	if (data.status==regControl.constants.OK){
		var i=0;
		barcode = data.barcode;
		var ml='';
		ml+=	'<table class = "table" id = "stock-list">';
		ml+=	'	<tr>';
		ml+=	'		<th>Batch Date</th>';
		ml+=	'		<th>Stock</th>';
		ml+=	'		<th>Discard</th>';
		ml+=	'	</tr>';
		for (i = 0; i< data.result.length ; i++) {
			ml+='<tr>';
			for (var propt in data.result[i]) {
				ml+='<td class ="'+propt+'">'+data.result[i][propt]+'</td>';
			}
			ml+='<td class = "discard-stock-btn btn btn-danger" data-barcode = '+barcode+' data-stock = '+data.result[i].stock+' data-batchdate = '+data.result[i].batchdate+' >Discard Stock</td>';
			ml+="</tr>";
		}
		ml+=	'</table>';
		$('#stock-list-container').html(ml);
		$('#add-stock-btn').data("barcode",barcode);
		regControl.initAddStock();
		regControl.initDiscardStock();
	}else{
		alert("operation fail");
	}
};



regControl._process_all_order_cb = function(data){
	if (data.status==regControl.constants.OK){
		if (data.result!=null)
			regControl.orderListController(data.result);
		else
			alert("You have no unprocessed order");
		if (data.leftover_order)
			alert("Some of the product cannot be processed due to insufficient stock. Please review this manually.");
		$("#loading-screen").addClass("hidden");
	}else{
		alert("operation fail");
	}
};
regControl._populate_unprocessed_order_date_cb = function(data) {
	if (data.status==regControl.constants.OK){
		var i;
		var ml = '';
		ml += 	'<select>';
		for ( i = 0; i < data.result.length; i++) {
			ml += '<option value="'+data.result[i]+'">'+data.result[i]+'</option>';
		}
		ml +=	'</select>';
		$("#order-date-input-selection").html(ml);
		$("#process-date-popup").removeClass("hidden");
		$("#loading-screen").addClass("hidden");
	} else {		
		alert("operation fail");
	}
};

regControl._populate_unprocessed_order_barcode_cb = function(data) {
	if (data.status==regControl.constants.OK){
		var i;
		var ml = '';
		ml += 	'<select>';
		for ( i = 0; i < data.result.length; i++) {
			ml += '<option value="'+data.result[i]+'">'+data.result[i]+'</option>';
		}
		ml +=	'</select>';
		$("#order-barcode-input-selection").html(ml);
		$("#process-barcode-popup").removeClass("hidden");
		$("#loading-screen").addClass("hidden");
	} else {		
		alert("operation fail");
	}
};
