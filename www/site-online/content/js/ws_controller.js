regControl.webStore = {};

regControl.webStore.init = function() {
	regControl.webStore.drawPage();
	regControl.webStore.initControl();
	$("#loading-screen").addClass("hidden");
}

regControl.webStore.initControl = function() {
	$("#import-webstore-order").off().on("click", function() {
		$("#loading-screen").removeClass("hidden");
		regControl.api_call(	{action:"webstore_import_request" },
								regControl.webStore._import_cb,
								null);
	});
	
	$("#process-webstore-order").off().on("click", function() {
		$("#loading-screen").removeClass("hidden");
		regControl.api_call(	{action:"webstore_process_request" },
								regControl.webStore._shipment_cb,
								null);
	});
}
//--------------------------- Page Drawing Function --------------------------
regControl.webStore.drawPage = function() {
	var ml = '';
	ml+= '<div class = "clearfix"></div>';
	//need to add webstore metrics?
	ml+= '<div class = "btn btn-primary" id = "import-webstore-order">Import Webstore Order</div>';
	ml+= '<div class = "btn btn-primary" id = "process-webstore-order">Process Webstore Order</div>';
	$("#content-container").html(ml);
};

//--------------------------- Callback Function --------------------------
regControl.webStore._import_cb = function (data) {
	if (data.status==regControl.constants.OK){
		alert("WebStore Orders Imported");
		$("#loading-screen").addClass("hidden");
	} else {		
		alert("operation fail");
	}
};

regControl.webStore._shipment_cb = function (data) {
	if (data.status==regControl.constants.OK){
		alert("WebStore Orders Imported");
		$("#loading-screen").addClass("hidden");
	} else {		
		alert("operation fail");
	}
};