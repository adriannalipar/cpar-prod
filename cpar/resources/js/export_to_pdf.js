$(document).ready(function() {
	//export to PDF
	$("#export_to_pdf_btn").click(function() {
		var cpar_no = $("#cpar_no").val();
		var data = "";
		data += "cpar_no=" + cpar_no;		

		blockBody();
		document.location.href = "/cpar_common/exportToPdf?" + data;
	    unblockBody();
	});
});