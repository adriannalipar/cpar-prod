$(document).ready(function() {
	$("input.datepicker").datepicker();

	initCalendarAddons();

	//results per page is changed
	$("#cpar_list_wrapper").on('change', '#pgtn_results_per_page', function() {
		var rpp = $("#pgtn_results_per_page").val(); //results per page
		var stage = $("ul#cpar_tabs li.active a.cpar_tab").data("stage");
		updateCparList(rpp, null, null, null, null, stage);
	});

	$("#cpar_search_btn").click(function() {
		var stage = $("ul#cpar_tabs li.active a.cpar_tab").data("stage");
		updateCparList(null, null, $("form#cpar_search_form").serialize(), null, null, stage);

		return false;
	});

	$("#cpar_list_wrapper").on('click', 'ul.pagination li a', function() {
		var rpp = $("#pgtn_results_per_page").val();
		var pn = $(this).data('page');
		var sort = $("#sort").val();
		var sort_by = $("#sort_by").val();
		var stage = $("ul#cpar_tabs li.active a.cpar_tab").data("stage");

		if(!pn) return;

		updateCparList(rpp, pn, null, sort, sort_by, stage);
	});

	//show errors onload (if there's any)
	if($("input#hasErrors_hdn").val() === "true") {
		$("div.error_container").show();
	}

	//show errors onload (if there's any)
	if($("input#hasSuccessMsgs_hdn").val() === "true") {
		$("div.success_msgs_container").show();
	}

	$("#cpar_list_wrapper").on('click', '.cpar_sortable', function() {
		var rpp = $("#pgtn_results_per_page").val();
		var sort = $("#sort").val() === "ASC" ? "DESC" : "ASC";
		var sort_by = $(this).data("sortby");
		var stage = $("ul#cpar_tabs li.active a.cpar_tab").data("stage");

		updateCparList(rpp, null, null, sort, sort_by, stage);
	});

	$("#cpar_list_wrapper").on('click', 'a.cpar_tab', function() {
		var stage = $(this).data('stage');

		var change_tab = true;
		clearSearchForm();
		updateCparList(null, null, null, null, null, stage, change_tab);

		return false;
	});

	$("#cpar_list_wrapper").on('click', '#export_btn', function() {
		var rpp = $("#pgtn_results_per_page").val();
		var pn = $("ul.pagination li.active a").html();
		var sort = $("#sort").val();
		var sort_by = $("#sort_by").val();
		var stage = $("ul#cpar_tabs li.active a.cpar_tab").data("stage");

		exportToCSV(rpp, pn, sort, sort_by, stage);
	});
	
	$("#cpar_search_form input, #cpar_search_form select").keypress(function(e) {
	    if(e.which == 13) {
	        $("#cpar_search_btn").trigger("click");
	    }
	});
	
});

function initCalendarAddons() {
	$("span.dp_ao.input-group-addon").click(function() {
		var $this = $(this);
		var $input = $this.siblings('input.datepicker');

		if(!$input.is(':disabled')) {
			$input.datepicker('show');
		}
	});
}

function updateCparList(rpp, pn, serializedSearchForm, sort, sort_by, stage, change_tab) {
	var data = serializedSearchForm ? serializedSearchForm : "";
	data += rpp ? "&rpp=" + rpp : "";
	data += pn ? "&pn=" + pn : "";
	data += sort ? "&sort=" + sort : "";
	data += sort_by ? "&sort_by=" + sort_by : "";
	data += stage ? "&tab=" + stage : "";

	if(change_tab) {
		data += "&change_tab=true";
	}

	blockElementById("cpar_list_wrapper");

	$.ajax({
	    url: "/cpar/search",
	    type: "get",
	    data: data,
	    success: function(result) {
	    	if(result) {
	    		$("div#cpar_list_wrapper").html(result);
	        } else {
	        	alert("No response from server. Please contact admin.");
	        }

	        unblockElementById("cpar_list_wrapper");
	    },
	    error:function() {
	        alert("Unable to load page. Please contact admin.");
	        unblockElementById("cpar_list_wrapper");
	    }
	});
}

function exportToCSV(rpp, pn, sort, sort_by, stage) {
	var data = "";
	data += rpp ? "rpp=" + rpp : "";
	data += pn ? "&pn=" + pn : "";
	data += sort ? "&sort=" + sort : "";
	data += sort_by ? "&sort_by=" + sort_by : "";
	data += stage ? "&tab=" + stage : "";

	blockElementById("cpar_list_wrapper");
	document.location.href = "/cpar/exportToCSV?" + data;
	unblockElementById("cpar_list_wrapper");
}

function clearSearchForm() {
	$("div#searchform input").val("");
	$("div#searchform select").val("All");

	//revert is_search value
	$("input#is_search").val("true");
}