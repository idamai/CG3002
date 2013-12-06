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

//---------------------paged drawing functions---------------------------
regControl.buildProductPagedArray = function(prodArray) {
	regControl.mlArray =new Array();
	for (i = 0; i< prodArray.length ; i++) {
		regControl.mlArray[i] = '';
		regControl.mlArray[i]+='<tr>';
		for (var propt in prodArray[i]) {
			regControl.mlArray[i]+='<td class ="'+propt+'">'+prodArray[i][propt]+'</td>';
		}
		regControl.mlArray[i]+=	'<td class = "view-stock-btn" data-barcode='+prodArray[i].barcode+' ><span class ="btn btn-small btn-inverse">Stock</span></td>';
		regControl.mlArray[i]+=	'<td class = "edit-product-btn" data-barcode='+prodArray[i].barcode+' ><span class ="btn btn-small btn-inverse">Edit</span></td>';
		regControl.mlArray[i]+=	'<td class = "delete-product-btn" data-barcode='+prodArray[i].barcode+' ><span class ="btn btn-small btn-inverse">Delete</span></td>';
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
		regControl.mlArray[i]+=	'<td class = "edit-store-btn" data-store-id='+storeArray[i].store_id+' ><span class ="btn btn-small btn-inverse">Edit Store</span></td>';
		regControl.mlArray[i]+=		'<td class = "delete-store-btn" data-store-id='+storeArray[i].store_id+' ><span class ="btn btn-small btn-inverse">Delete Store</span></td>';
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
	ml+= '				<th>Properties</th>';
	ml+= '				<th>Edit</th>';
	ml+= '				<th>Delete</th>';
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
			ml+='<td class = "discard-stock-btn" data-barcode = '+barcode+' data-stock = '+data.result[i].stock+' data-batchdate = '+data.result[i].batchdate+' ><span class = "btn btn-danger"  >Discard Stock</span></td>';
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
