$(document).ready(function() {
	$("#user_list_wrapper").on('change', '#pgtn_results_per_page', function() {
		var rpp = $("#pgtn_results_per_page").val(); //results per page

		updateUserList(rpp);
	});

	$("#user_list_wrapper").on('click', 'ul.pagination li a', function() {
		var rpp = $("#pgtn_results_per_page").val();
		var pn = $(this).data('page');
		var sort = $("#sort").val();
		var sort_by = $("#sort_by").val();

		if(!pn) return;

		updateUserList(rpp, pn, null, sort, sort_by);
	});

	$("#user_search_btn").click(function() {
		updateUserList(null, null, $("form#user_search_form").serialize());

		return false;
	});

	$("#user_list_wrapper").on('click', '.user_delete_button', function(e) {
		var $el = $(event.target);
		var id = $el.closest("td").find("input.user_id_hdn").val();

		$("form#user_delete_form input#user_id_hdn").val(id);
		$("div#confirm_delete_modal").modal("show");
	});

	$("#true_delete_btn").click(function() {
		$("form#user_delete_form").submit();
	});

	//show errors onload (if there's any)
	if($("input#hasErrors_hdn").val() === "true") {
		$("div.error_container").show();
	}

	//show errors onload (if there's any)
	if($("input#hasSuccessMsgs_hdn").val() === "true") {
		$("div.success_msgs_container").show();
	}

	$("#user_list_wrapper").on('click', '.user_sortable', function() {
		var rpp = $("#pgtn_results_per_page").val();
		var sort = $("#sort").val() === "ASC" ? "DESC" : "ASC";
		var sort_by = $(this).data("sortby");

		updateUserList(rpp, null, null, sort, sort_by);
	});
	
	$("#user_search_form input, #user_search_form select").keypress(function(e) {
	    if(e.which == 13) {
	        $("#user_search_btn").trigger("click");
	    }
	});

});

function updateUserList(rpp, pn, serializedSearchForm, sort, sort_by) {
	var data = serializedSearchForm ? serializedSearchForm : "";
	data += rpp ? "&rpp=" + rpp : "";
	data += pn ? "&pn=" + pn : "";
	data += sort ? "&sort=" + sort : "";
	data += sort_by ? "&sort_by=" + sort_by : "";

	blockElementById("user_list_wrapper");

	$.ajax({
	    url: "/user/search",
	    type: "get",
	    data: data,
	    success: function(result) {
	    	if(result) {
	    		$("div#user_list_wrapper").html(result);
	        } else {
	        	alert("No response from server. Please contact admin.");
	        }

	        unblockElementById("user_list_wrapper");
	    },
	    error:function() {
	        alert("Unable to load page. Please contact admin.");
	        unblockElementById("user_list_wrapper");
	    }
	});
}