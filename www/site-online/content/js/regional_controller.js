/*
	This regControl class is the main controller for the regional system
*/
window.regControl = window.regControl || {};

//setup
regControl.api_url = "/api/api.php";
regControl.api_path = "/api/";
regControl.data_chunk_size = 10;

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
						   LOGIN : "login" };
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
	$("#product-btn").off().on("click", function(){
		regControl.api_call(	{action:"retreive_product"},
								regControl._ret_prod_cb,
								null);
	});
	$("#store-btn").off().on("click", function(){
		regControl.api_call(	{action:"retreive_store"},
								regControl._ret_store_cb,
								null);
	});
	$("#order-btn").off().on("click", function(){
		regControl.api_call(	{action:"retreive_order_list"},
								regControl._ret_order_list_cb,
								null);
	});
	$("#shipment-btn").off().on("click", function(){
		regControl.api_call(	{action:"retreive_shipped_list"},
								regControl._ret_shipped_list_cb,
								null);
	});
	$("#close-stock-popup").off().on("click", function(){
		if (!$("#view-stock-popup").hasClass("hidden"))
			$("#view-stock-popup").addClass("hidden");
			$('#stock-list-container').html("");
	});
							  
});

regControl.initAddStock = function() {
	var barcode;
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
				regControl.api_call(	{action:"update_stock",barcode:barcode,date:batchdate,quantity:(current-quantity)},
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
// ------------------------------ CALLBACKS ------------------------------
regControl._ret_prod_cb = function(data){
	if (data.status==regControl.constants.OK){
		var i=0;
		var ml='';
		ml+= '<table class = "table" id = "product-list">'
		ml+= '			<tr>';
		ml+= '				<th>Barcode</th>';
		ml+= '				<th>Name</th>';
		ml+= '				<th>Category</th>';
		ml+= '				<th>Manufacturer</th>';
		ml+= '				<th>Cost</th>';
		ml+= '				<th>Stock</th>';
		ml+= '			</tr>';
		for (i = 0; i< data.result.length ; i++) {
			ml+='<tr>';
			for (var propt in data.result[i]) {
				ml+='<td class ="'+propt+'">'+data.result[i][propt]+'</td>';
			}
			ml+='<td class = "view-stock btn btn-inverse" data-barcode='+data.result[i].barcode+' >Add/View Stock</td>';
			ml+="</tr>";
		}
		ml+='</table';
		$('#content-container').html(ml);
		$('.view-stock').off().on("click", function(){
			itemBarcode = $(this).data("barcode");
			regControl.api_call(	{action:"retreive_stock",barcode:itemBarcode},
									regControl._ret_stock_cb,
									null);
			$('#view-stock-popup').removeClass("hidden");
	});	
	}else{
		alert("operation fail");
	}
};

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

regControl._ret_store_cb = function(data){
	if (data.status==regControl.constants.OK){
		var i=0;
		var ml='';
		ml+=	'<table class = "table" id = "store-list">';
		ml+=	'	<tr>';
		ml+=	'		<th>Store ID</th>';
		ml+=	'		<th>Store Name</th>';
		ml+=	'		<th>Location</th>';
		ml+=	'	</tr>';
		for (i = 0; i< data.result.length ; i++) {
			ml+='<tr>';
			for (var propt in data.result[i]) {
				ml+='<td class ="'+propt+'">'+data.result[i][propt]+'</td>';
			}
			ml+="</tr>";
		}
		ml+=	'</table>';
		$('#content-container').html(ml);
	}else{
		alert("operation fail");
	}
};

regControl._ret_order_list_cb = function(data){
	if (data.status==regControl.constants.OK){
		var i=0;
		var ml='';
		ml+=	'<table class = "table" id = "order-list">';
		ml+=	'	<tr>';
		ml+=	'		<th>Barcode</th>';
		ml+=	'		<th>Date</th>';
		ml+=	'		<th>Store ID</th>';
		ml+=	'		<th>Quantity</th>';
		ml+=	'	</tr>';
		for (i = 0; i< data.result.length ; i++) {
			ml+='<tr>';
			for (var propt in data.result[i]) {
				ml+='<td class ="'+propt+'">'+data.result[i][propt]+'</td>';
			}
			ml+="</tr>";
		}
		ml+=	'</table>';
		$('#content-container').html(ml);
	}else{
		alert("operation fail");
	}
};

regControl._ret_shipped_list_cb = function(data){
	if (data.status==regControl.constants.OK){
		var i=0;
		var ml='';
		ml+=	'<table class = "table" id = "shipped-list">';
		ml+=	'	<tr>';
		ml+=	'		<th>Barcode</th>';
		ml+=	'		<th>Date</th>';
		ml+=	'		<th>Store ID</th>';
		ml+=	'		<th>Quantity</th>';
		ml+=	'	</tr>';
		for (i = 0; i< data.result.length ; i++) {
			ml+='<tr>';
			for (var propt in data.result[i]) {
				ml+='<td class ="'+propt+'">'+data.result[i][propt]+'</td>';
			}
			ml+="</tr>";
		}
		ml+=	'</table>';
		$('#content-container').html(ml);
	}else{
		alert("operation fail");
	}
};