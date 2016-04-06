var removed_uploads = new Array();
var b_save_only = false;
var success_obj = null;

$(document).ready(function() {
	initSelect2();
	initDatepickers();

	//add new input[type=file] element
	$("button#add_attachment").click(function() {
		var file_html = "" +
			"<div class='file_attachment' style='display: none;'> " + 
		  		"<input type='file' name='attachments[]' class='cpar_attachment' style='display: none;' />" +  
		  		"<span class='file_name'></span>" +  
		  		"&nbsp;&nbsp;&nbsp; " + 
		  		"<i class='remove_attachment glyphicon glyphicon-remove'></i> " + 
		  	"</div>";

		$("div#attachments_container").append(file_html);

		var $div = $("div#attachments_container div.file_attachment:last-child");
		$div.find('input[type=file]').click();
	});

	//when a file is selected, populate display the selected file
	$("div#attachments_form_group").on("change", "input.cpar_attachment", function() {
		var file_name = $(this).val().split('/').pop().split('\\').pop();

		$(this).siblings("span.file_name").html(file_name);
		$(this).closest("div.file_attachment").slideDown(200);

		removeEmptyFileElements();
	});

	//remove attachment
	$("div#attachments_form_group").on("click", "i.remove_attachment", function() {
		var $div = $(this).closest("div");
		var div_class = $div.prop("class");

		if(div_class === "uploaded_file") {
			removed_uploads.push($div.find('a.file_name').html());
		}

		$div.slideUp("fast", function() { $(this).remove(); } );
	});
	
	$('#send_reminder').click(function(){
		send_reminder();
	});

	//on submit
	$(".submit_btn").click(function() {
		var m_status = "";
		var errors = new Array();
		b_save_only = false;
		var btn_id = $(this).prop("id");
		
		if(btn_id === "save_draft_btn") {
			b_save_only = true;
			m_status = CPAR_MINI_STATUS_DRAFT;
			errors = validateDraft();
		} else if(btn_id === "save_cpar_changes_btn") {
			m_status = CPAR_SUBMIT_S1_SAVE_CPAR_CHANGES;
			var skipImsReview = true;
			errors = validateForm(skipImsReview);
		} else if(btn_id === "save_only_btn") {
			b_save_only = true;
			m_status = CPAR_SUBMIT_S1_SAVE_ONLY;
			errors = validateForm();
		} else if(btn_id === "proceed_btn") {
			m_status = CPAR_SUBMIT_S1_PROCEED;
			errors = validateForm();
		} else {
			errors = validateForm();
		}

		if(errors && errors.length > 0) {
			showErrors(errors);
		} else {
			submitForm(m_status);
		}
	});

	//after reading successful save message, redirect to CPAR list
	$("div#successful_save_modal").on("hidden.bs.modal", function() {
		if(b_save_only) {
			if(success_obj) {
				window.location = "/cpar/edit/"+success_obj.id;	
			} else {
				window.location.reload();
			}
		} else {
			window.location = "/cpar/";	
		}
	});

	//ondelete
	$("#true_delete_btn").click(function() {
		$("form#cpar_delete_form").submit();
	});
	
});

function removeEmptyFileElements() {
	$("div#attachments_form_group div.file_attachment").each(function() {
		if($(this).css("display") === "none") {
			$(this).remove();
		}
	});
}

function initSelect2() {
	//select2 initialization for addressee/requestor names
	$(".adr_req_name").select2({
		placeholder: "Name",
	    minimumInputLength: 2,
	    allowClear: true,
	    ajax: {
	        url: "/cpar/getUsers",
	        dataType: 'json',
	        type: "GET",
	        quietMillis: 200,
	        data: function (term) {
	            return {
	                term: term,
	            };
	        },
	        results: function (data) {
	            return {
	                results: $.map(data, function (item) {
	                    return {
	                        text: item.full_name,
	                        id: item.id
	                    }
	                })
	            };
	        }
	    },
	    initSelection: function (element, callback) {
		    var elementText = JSON.parse($(element).val());
		    callback(elementText[0]);
		}
	});

	//select2 initialization for addressee/requestor team leads
	$(".adr_req_team_lead").select2({
		placeholder: "Team Leader",
	    minimumInputLength: 2,
	    allowClear: true,
	    ajax: {
	        url: "/cpar/getUsers",
	        dataType: 'json',
	        type: "GET",
	        quietMillis: 200,
	        data: function (term) {
	            return {
	                term: term
	            };
	        },
	        results: function (data) {
	            return {
	                results: $.map(data, function (item) {
	                    return {
	                        text: item.full_name,
	                        id: item.id
	                    }
	                })
	            };
	        }
	    },
	    initSelection: function (element, callback) {
		    var elementText = JSON.parse($(element).val());
		    callback(elementText[0]);
		}
	});

	//select2 initialization for IMS only select2
	$(".assigned_ims_name").select2({
		placeholder: "Name",
	    minimumInputLength: 2,
	    allowClear: true,
	    ajax: {
	        url: "/cpar/getUsers",
	        dataType: 'json',
	        type: "GET",
	        quietMillis: 200,
	        data: function (term) {
	            return {
	                term: term,
	                is_ims: 1
	            };
	        },
	        results: function (data) {
	            return {
	                results: $.map(data, function (item) {
	                    return {
	                        text: item.full_name,
	                        id: item.id
	                    }
	                })
	            };
	        }
	    },
	    initSelection: function (element, callback) {
		    var elementText = JSON.parse($(element).val());
		    callback(elementText[0]);
		}
	});

	//select2 on change event for addressee/requestor name (populate team and team lead)
	$(".adr_req_name").on("change", function(e) {
		var selected_id = e.val;
		var data = { selected_id : selected_id };
		var $that = $(this);

		$.ajax({
	        url: "/cpar/getAddrReqData",
	        type: "GET",
	        data: data,
	        success: function(result){
	        	if(result) {
	        		try {
	        			var obj = JSON.parse(result);

		            	if(obj) {
		            		$that.closest("fieldset").find(".adr_req_team").val(obj.team_id);

		            		var sel2val = '[{"id": "' + obj.team_lead_id + '", "text": "' + obj.team_lead + '"}]';
		            		$that.closest("fieldset").find(".adr_req_team_lead").select2("val", sel2val);
		            	}
	        		} catch(e) {
	        			alert("Invalid response from server. Please contact admin.");
	        		}
	            } else {
	            	alert("No response from server. Please contact admin.");
	            }
	        },
	        error:function(){
	            alert("Unable to get data. Please contact admin.");
	        }
	    });
	});
}

function validateForm(skipImsReview) {
	var errors = new Array();
	var is_required_suffix = " is required.";

	var date_filed = $("#date_filed").val();
	
	var title = $("#title").val();
	var type = $("#type").val();
	var result_of = $("#result_of").val();
	var result_of_others = $("#result_of_others").val();
	var process = $("#process").val();
	var details = $("#details").val();
	var justification = $("#justification").val();
	var references = $("#references").val();

	var at_name = $("#at_name").val();
	var at_team = $("#at_team").val();
	var at_team_lead = $("#at_team_lead").val();

	var req_name = $("#req_name").val();
	var req_team = $("#req_team").val();
	var req_team_lead = $("#req_team_lead").val();

	date_filed = date_filed.trim();
	if(!date_filed) {
		errors.push("Date Filed" + is_required_suffix);
	}

	title = title.trim();
	if(!title) {
		errors.push("Title" + is_required_suffix);
	} else {
		if(title.length < MIN_CPAR_TITLE || title.length > MAX_CPAR_TITLE) {
			errors.push("Title should be " + MIN_CPAR_TITLE + " to " + MAX_CPAR_TITLE + " characters.");
		}
	}

	if(!type.trim()) {
		errors.push("Type" + is_required_suffix);
	}

	if(!result_of.trim()) {
		errors.push("Raised as a result of" + is_required_suffix);
	}
	
	if(result_of == 10) {
		if(!result_of_others.trim()) {
			errors.push("Raised as a result of (Others)" + is_required_suffix);
		} else {
			if(result_of.length < MIN_CPAR_OTHERS || result_of.length > MAX_CPAR_OTHERS) {
				errors.push("Raised as a result of (Others) should be " + MIN_CPAR_DETAILS + " to " + MAX_CPAR_DETAILS + " characters.");
			}
 		}
	}
	
	if(!process.trim()) {
		errors.push("Process" + is_required_suffix);
	}

	details = details.trim();
	if(details && (details.length < MIN_CPAR_DETAILS || details.length > MAX_CPAR_DETAILS)) {
		errors.push("Details should be " + MIN_CPAR_DETAILS + " to " + MAX_CPAR_DETAILS + " characters.");
	}

	justification = justification.trim();
	if(justification && (justification.length < MIN_CPAR_JUSTIFICATION || justification.length > MAX_CPAR_JUSTIFICATION)) {
		errors.push("Justification should be " + MIN_CPAR_JUSTIFICATION + " to " + MAX_CPAR_JUSTIFICATION + " characters.");
	}

	references = references.trim();
	if(references && (references.length < MIN_CPAR_REFERENCES || references.length > MAX_CPAR_REFERENCES)) {
		errors.push("References should be " + MIN_CPAR_REFERENCES + " to " + MAX_CPAR_REFERENCES + " characters.");
	}
	
	if(!at_name.trim()) {
		errors.push("Addressee name" + is_required_suffix);
	}

	if(!at_team.trim()) {
		errors.push("Addressee team" + is_required_suffix);
	}

	if(!at_team_lead.trim() || at_team_lead == undefined) {
		errors.push("Addressee team lead" + is_required_suffix);
	}

	if(!req_name.trim()) {
		errors.push("Requestor name" + is_required_suffix);
	}

	if(!req_team.trim()) {
		errors.push("Requestor team" + is_required_suffix);
	}

	if(!req_team_lead.trim()) {
		errors.push("Requestor team lead" + is_required_suffix);
	}

	//validation for IMS Review
	if(!skipImsReview && $("#reviewed_by").length) {
		if(!$("input[name=review_action]:checked").length) {
			errors.push("Please choose a review action.");
		} else {
			if($("#mark_as_reviewed").is(":checked")) {
				var reviewed_by = $("#reviewed_by").val();
				var due_date = $("#next_due_date").val();

				if(!reviewed_by.trim()) {
					errors.push("IMS Assignee" + is_required_suffix);
				} else {
					//checking for addressee-IMS conflict
					var ims_id = $("#reviewed_by").select2("data").id;
					var addressee_id = $("#true_at_name").val();

					if(ims_id === addressee_id) {
						errors.push("CPAR Addressee and Assigned IMS cannot be the same person.");
					}
				}

				if(!due_date.trim()) {
					errors.push("Submit Response By" + is_required_suffix);
				} else {
					var comp = due_date.split('/');
					var m = parseInt(comp[0], 10);
					var d = parseInt(comp[1], 10);
					var y = parseInt(comp[2], 10);
					var date = new Date(y,m-1,d);
					if (date.getFullYear() == y && date.getMonth() + 1 == m && date.getDate() == d) {
						//do nothing
					} else {
						errors.push("Submit Response By is an invalid date.");
					}
				}
			} else if($("#push_back").is(":checked")) {
				var stage = $("#pb_stage").val();

				if(!stage.trim()) {
					errors.push("Stage" + is_required_suffix);
				}
			} else if($("#re_assign").is(":checked")) {
				var reviewed_by = $("#re_assign_to").val();
				var due_date = $("#review_by_due_date").val();

				if(!reviewed_by.trim()) {
					errors.push("IMS Assignee" + is_required_suffix);
				} else {
					//checking for addressee-IMS conflict
					var ims_id = $("#re_assign_to").select2("data").id;
					var addressee_id = $("#true_at_name").val();

					if(ims_id === addressee_id) {
						errors.push("CPAR Addressee and Assigned IMS cannot be the same person.");
					}
				}

				if(!due_date.trim()) {
					errors.push("Review By" + is_required_suffix);
				} else {
					//TODO (later) : check if valid date
					var comp = due_date.split('/');
					var m = parseInt(comp[0], 10);
					var d = parseInt(comp[1], 10);
					var y = parseInt(comp[2], 10);
					var date = new Date(y,m-1,d);
					if (date.getFullYear() == y && date.getMonth() + 1 == m && date.getDate() == d) {
						//do nothing
					} else {
						errors.push("Review By is an invalid date.");
					}
				}
			}
		}

		var remarks = $("#remarks").val();
		if(!remarks.trim()) {
			if(!$("input[name=review_action]:checked").length || $("#push_back").is(":checked") || $("#re_assign").is(":checked") || $("#mark_as_invalid").is(":checked")) {
				errors.push("Remarks" + is_required_suffix);	
			}
		} else {
			if(remarks.length < MIN_CPAR_REMARKS || remarks.length > MAX_CPAR_REMARKS) {
				errors.push("Remarks should be " + MIN_CPAR_REMARKS + " to " + MAX_CPAR_REMARKS + " characters.");
			}
		}
	} else if($('input[name="is_ims_creating"]').length > 0) {
		var due_date = $("#next_due_date").val();
	
		if(!due_date.trim()) {
			errors.push("Submit Response By" + is_required_suffix);
		} else {
			var comp = due_date.split('/');
			var m = parseInt(comp[0], 10);
			var d = parseInt(comp[1], 10);
			var y = parseInt(comp[2], 10);
			var date = new Date(y,m-1,d);
			
			if (date.getFullYear() == y && date.getMonth() + 1 == m && date.getDate() == d) {
				//do nothing
			} else {
				errors.push("Submit Response By is an invalid date.");
			}
		}
	}

	return errors;
}

function submitForm(m_status) {
	removeEmptyFileElements();

	var data = new FormData($("form#cpar_form")[0]);
	data.append("m_status", m_status);

	//append to-be-removed files if not empty
	if(removed_uploads && removed_uploads.length > 0) {
		data.append("removed_uploads", JSON.stringify(removed_uploads));
	}

	blockBody();

	$.ajax({
        url: "/cpar/save",
        type: "POST",
        data: data,
        cache: false,
        contentType: false,
        processData: false,     
        success: function(result){
        	if(result) {
        		try {
        			var obj = JSON.parse(result);

	            	if(obj.success) {
	            		success_obj = obj;
	            		if(m_status === CPAR_SUBMIT_S1_SAVE_CPAR_CHANGES) {
	            			if(obj.success_msg) {
		            			$("div#successful_save_nr_modal .successful_save_modal_message").html(obj.success_msg);
							} else {
								$("div#successful_save_nr_modal .successful_save_modal_message").html('CPAR successfully saved.');
							}
	            			//update true_at_name (used when comparing Addressee and assigned IMS)
	            			$("#true_at_name").val($("#at_name").val());
	            			//if save CPAR changes, no redirect
	            			showSaveSuccessfulNrModal();
	            		} else {
	            			if(obj.success_msg) {
		            			$("div#successful_save_modal .successful_save_modal_message").html(obj.success_msg);
							} else {
								$("div#successful_save_modal .successful_save_modal_message").html('CPAR successfully saved.');
							}
	            			showSaveSuccessfulModal();
	            		}
	            	} else {
	            		showErrors(obj.errors);
	            	}
        		} catch(e) {
        			alert("Invalid response from server. Please contact admin.");
        			unblockBody();
        		}            	
            } else {
            	alert("No response from server. Please contact admin.");
            }

            unblockBody();
        },
        error:function(e){
            if(e.status == 413) {
	        	showErrors(new Array('Files uploaded may be too large. Please upload smaller files.'));
        	} else {
	        	alert("Unable to save form. Please contact admin.");
        	}
            unblockBody();
        }
    });
}

function initDatepickers() {
	if($("input.datepicker").length) {
		$("input.datepicker").datepicker();

		$("span.dp_ao.input-group-addon").click(function() {
			var $this = $(this);
			var $input = $this.siblings('input.datepicker');

			if(!$input.is(':disabled')) {
				$input.datepicker('show');
			}
		});

	}
}

function showSaveSuccessfulModal() {
	$("#successful_save_modal").modal('show');
}

function showSaveSuccessfulNrModal() {
	$("#successful_save_nr_modal").modal('show');
}

function validateDraft() {
	var errors = new Array();
	var is_required_suffix = " is required.";

	var title = $("#title").val();
	var type = $("#type").val();

	title = title.trim();
	if(!title) {
		errors.push("Title" + is_required_suffix);
	} else {
		if(title.length < MIN_CPAR_TITLE || title.length > MAX_CPAR_TITLE) {
			errors.push("Title should be " + MIN_CPAR_TITLE + " to " + MAX_CPAR_TITLE + " characters.");
		}
	}

	if(!type.trim()) {
		errors.push("Type" + is_required_suffix);
	}

	return errors;
}

function send_reminder() {
	var _cpar_no = null;
	
	if(!_cpar_no && $('#cpar_no').length > 0 && $('#cpar_no').val() != '') {
		_cpar_no = $('#cpar_no').val();
	
		blockBodyWithMessage('Sending Reminder...');
		
		$.ajax({
	        url: "/cpar_common/send_reminder",
	        type: "POST",
	        data: {cpar_no:_cpar_no},
	        cache: false,
	        success: function(result){
	        	if(result) {
	        		try {
	        			var obj = JSON.parse(result);
	
		            	if(obj.success) {
	            			if(obj.success_msg) {
		            			$("div#successful_save_nr_modal .successful_save_modal_message").html(obj.success_msg);
							} else {
								$("div#successful_save_nr_modal .successful_save_modal_message").html('CPAR successfully saved.');
							}
							showSaveSuccessfulNrModal();	            	
						} else {
		            		showErrors(obj.errors);
		            	}
		            	
		            	unblockBody();
	        		} catch(e) {
	        			alert("Invalid response from server. Please contact admin.");
	        			unblockBody();
	        		}            	
	            } else {
	            	alert("No response from server. Please contact admin.");
	            }
	            
	            unblockBody();
	
	        },
	        error:function(){
	            alert("There was a problem sending the reminder. Please contact admin.");
	            unblockBody();
	        }
	    });
	} 
}