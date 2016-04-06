$(document).ready(function() {
	initSaveWholeCparBtn();
	
	$('#send_reminder').click(function(){
		send_reminder();
	});
});

function initSaveWholeCparBtn() {
	//on submit (Save CPAR Changes (whole CPAR))
	$("#save_whole_cpar_btn").click(function() {
		var errors = new Array();
		errors = validateWholeCpar();
		
		if(errors && errors.length > 0) {
			showErrors(errors);
		} else {
			submitWholeCparForm();
		}
	});
}

function validateWholeCpar() {
	var errors = new Array();
	errors = validateWholeCpar_info();
	errors = errors.concat(validateWholeCpar_corr_actions());

	return errors;
}

function validateWholeCpar_info() {
	var errors = new Array();
	var is_required_suffix = " is required.";

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

	return errors;
}

function validateWholeCpar_corr_actions() {
	var errors = new Array();
	var is_required_suffix = " is required.";

	//CAR only fields
	if($("#car_fields_wrapper").length) {
		//Remedial Action Details
		var immediate_remedial_action = $("#immediate_remedial_action").val();
		var implemented_by = $("#implemented_by").val();
		var date_implemented = $("#date_implemented").val();

		immediate_remedial_action = immediate_remedial_action.trim();
		if(!immediate_remedial_action) {
			errors.push("Immediate remedial action" + is_required_suffix);
		} else {
			if(immediate_remedial_action.length < MIN_CPAR_IMMEDIATE_REMEDIAL_ACTION || immediate_remedial_action.length > MAX_CPAR_IMMEDIATE_REMEDIAL_ACTION) {
				errors.push("Immediate remedial action should be " + MIN_CPAR_IMMEDIATE_REMEDIAL_ACTION + " to " + MAX_CPAR_IMMEDIATE_REMEDIAL_ACTION + " characters.");
			}
		}

		implemented_by = implemented_by.trim();
		if(!implemented_by) {
			errors.push("Implemented by" + is_required_suffix);
		}

		date_implemented = date_implemented.trim();
		if(!date_implemented) {
			errors.push("Date implemented" + is_required_suffix);
		}

		//Root Cause Analysis
		tools_used = new Array();
		$('input[name="tools_used[]"]').each(function() {
			var $this = $(this);
			if($this.is(":checked")) {
				if($this.val() == TOOLS_USED_OTHERS_ID) {
					var others_textbox = $("#other_tools_used").val();
					if(others_textbox) {
						if(others_textbox.length < MIN_CPAR_OTHERS || others_textbox.length > MAX_CPAR_OTHERS) {
							errors.push("Others (textbox) should be " + MIN_CPAR_OTHERS + " to " + MAX_CPAR_OTHERS + " characters.");
						} else {
							tools_used.push($this.val());
						}
					} else {
						errors.push("Others (textbox)" + is_required_suffix);
					}
				} else {
					tools_used.push($this.val());
				}
			}
		});
		
		if(!(typeof is_stage_2 !== 'undefined')) {
			var rca_details = $("#rca_details").val();
	
			var investigated_by = $("#investigated_by").val();
			var date_investigation_started = $("#date_investigation_started").val();
			var date_investigation_ended = $("#date_investigation_ended").val();
	
			rca_details = rca_details.trim();
			if(!rca_details) {
				errors.push("Details / Result of Root Cause Analysis" + is_required_suffix);
			}
	
			investigated_by = investigated_by.trim();
			if(!investigated_by) {
				errors.push("Investigated by" + is_required_suffix);
			}
	
			date_investigation_started = date_investigation_started.trim();
			if(!date_investigation_started) {
				errors.push("Date Investigation (Started)" + is_required_suffix);
			}
	
			date_investigation_ended = date_investigation_ended.trim();
			if(!date_investigation_ended) {
				errors.push("Date Investigation (Ended)" + is_required_suffix);
			}
	
			if(date_investigation_started && date_investigation_ended) {
				if(!isCorrectDateRange(date_investigation_started, date_investigation_ended)) {
					errors.push("Invalid date range. Date Investigation (Ended) should not be earlier than Date Investigation (Started).");
				}
			}
		} else {
			if(date_investigation_started && date_investigation_ended) {
				if(!isCorrectDateRange(date_investigation_started, date_investigation_ended)) {
					errors.push("Invalid date range. Date Investigation (Ended) should not be earlier than Date Investigation (Started).");
				}
			}
		}
	}
	
	var corr_prev_action = $("#corr_prev_action").val();
	var corr_prev_proposed_by = $("#corr_prev_proposed_by").val();
	var corr_prev_target_start_date = $("#corr_prev_target_start_date").val();
	var corr_prev_target_end_date = $("#corr_prev_target_end_date").val();
	
	if(!(typeof is_stage_2 !== 'undefined')) {
		//common fields
		corr_prev_action = corr_prev_action.trim();
		if(!corr_prev_action) {
			errors.push("Corrective/Preventive (Continual Improvement) action" + is_required_suffix);
		} else {
			if(corr_prev_action.length < MIN_CPAR_CORR_PREV_ACTION || corr_prev_action.length > MAX_CPAR_CORR_PREV_ACTION) {
				errors.push("Corrective/Preventive (Continual Improvement) action should be " + MIN_CPAR_CORR_PREV_ACTION + " to " + MAX_CPAR_CORR_PREV_ACTION + " characters.");
			}
		}
	
		corr_prev_proposed_by = corr_prev_proposed_by.trim();
		if(!corr_prev_proposed_by) {
			errors.push("Proposed by" + is_required_suffix);
		}
	
		corr_prev_target_start_date = corr_prev_target_start_date.trim();
		if(!corr_prev_target_start_date) {
			errors.push("Target Start Date" + is_required_suffix);
		}
	
		corr_prev_target_end_date = corr_prev_target_end_date.trim();
		if(!corr_prev_target_end_date) {
			errors.push("Target End Date" + is_required_suffix);
		}
	
		if(corr_prev_target_start_date && corr_prev_target_end_date) {
			if(!isCorrectDateRange(corr_prev_target_start_date, corr_prev_target_end_date)) {
				errors.push("Invalid date range. Target End Date should not be earlier than Target Start Date.");
			}
		}
	
		//tasks
		if($('#task_count').length > 0 && $('#task_count').val() > 0) {
			
		} else {
			if((!tasks || tasks.length <= 0) && (!tasks_to_add || tasks_to_add.length <= 0)) {
				errors.push("Please input at least 1 Corrective/Preventive (Continual Improvement) Action Detail.");
			}
		}

	} else {

		corr_prev_action = corr_prev_action.trim();
		if(corr_prev_action) {
			if(corr_prev_action.length < MIN_CPAR_CORR_PREV_ACTION || corr_prev_action.length > MAX_CPAR_CORR_PREV_ACTION) {
				errors.push("Corrective/Preventive (Continual Improvement) action should be " + MIN_CPAR_CORR_PREV_ACTION + " to " + MAX_CPAR_CORR_PREV_ACTION + " characters.");
			}
		}
		
		if(corr_prev_target_start_date && corr_prev_target_end_date) {
			if(!isCorrectDateRange(corr_prev_target_start_date, corr_prev_target_end_date)) {
				errors.push("Invalid date range. Target End Date should not be earlier than Target Start Date.");
			}
		}

	}

	return errors;
}

function submitWholeCparForm() {
	var url = '/cpar_common/saveWholeCpar';

	removeEmptyFileElements();

	var data = new FormData($("form#cpar_form")[0]);

	//append to-be-removed files if not empty
	if(removed_uploads && removed_uploads.length > 0) {
		data.append("removed_uploads", JSON.stringify(removed_uploads));
	}

	//append to-be-removed files if not empty
	if(removed_rad_uploads && removed_rad_uploads.length > 0) {
		data.append("removed_rad_uploads", JSON.stringify(removed_rad_uploads));
	}

	if(removed_rca_uploads && removed_rca_uploads.length > 0) {
		data.append("removed_rca_uploads", JSON.stringify(removed_rca_uploads));
	}

	//append tasks
	data.append("tasks", JSON.stringify(tasks));
	data.append("tasks_to_add", JSON.stringify(tasks_to_add));
	data.append("tasks_to_delete", JSON.stringify(tasks_to_delete));

	blockBody();

	$.ajax({
        url: url,
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
	            		if(tasks_to_add) {
	            			tasks_to_add = new Array();
	            		}

	            		if(tasks_to_delete) {
	            			tasks_to_delete = new Array();
	            		}
            				       
						if(obj.success_msg) {
							show_message_modal(obj.success_msg, true);
						} else {
							show_message_modal('CPAR successfully saved.', true);
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