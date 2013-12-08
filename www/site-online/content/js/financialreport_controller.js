regControl.financialReport = {};

regControl.financialReport.init = function() {
	regControl.financialReport.drawPage();
	regControl.api_call(	{action:"retrieve_financial_report"},
								regControl.financialReport._finance_data_callback,
								null);
	
};


//--------------------------- Report Controller Page Drawer  -------------------------
regControl.financialReport.drawPage = function() {
	var current_year = new Date().getFullYear();
	var ml = '';
	ml+= '<h2>Financial Report</h2>';
	ml+= '<h4>For year ending on 31 December '+current_year+'</h4>';
	ml+= '<div id = "report-table-wrapper">';
	ml+= 	'<table class = "table" id = "report-table">';	
	ml+= '			<tr>';
	ml+= '				<th>Accounts</th>';                
	ml+= '				<th>Debit</th>';
	ml+= '				<th>Credit</th>';
	ml+= '			</tr>';
	ml+= 	'</table>';
	ml+= '</div>';
	ml+= '<div class = "graph-analytics"></div>';
	$("#content-container").html(ml);
};

//draw table
regControl.financialReport.drawTable = function(financeData) {
	var ml='';
	if (financeData != null) {
		for (i = 0; i< financeData.length ; i++) {
			ml+='<tr>';
			for (var propt in financeData[i]) {
				ml+='<td class ="'+propt+'">'+financeData[i][propt]+'</td>';
			}
			ml+='</tr>';
		}
	}
	$('#report-table').append(ml);
}

//draw graph

//--------------------------- Financial Reports Callbacks -------------------------

regControl.financialReport._finance_data_callback = function(data) {
	if (data.status==regControl.constants.OK){
		regControl.financialReport.drawTable(data.result);		
		$("#loading-screen").addClass("hidden");
	} else {		
		alert("operation fail");
	}
};