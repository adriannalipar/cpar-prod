var tools_used = new Array();
var removed_uploads = new Array();
var removed_rad_uploads = new Array();
var removed_rca_uploads = new Array();
var tasks = new Array();
var tasks_to_add = new Array();
var tasks_to_delete = new Array();

var b_save_only = false;
var success_obj = null;

$(document).ready(function() {
	initSelect2();
	initDatepickers();
	initUpload();
	initTasks();
	initSubmitButtons();
	init_popovers();

	//update date btn
	$("#update_date_btn").click(function() {
		updateAccomplishByDate();
	});

	//after reading successful save message, redirect to CPAR list
	$("div#successful_save_modal").on("hidden.bs.modal", function() {
	    if(b_save_only) {
			window.location.reload();
		} else {
			window.location = "/cpar/";	
		}
	});
	
	$('#btn_ap_attachments').each(function(){
		
		var btn = $(this);
		var cpar_no = $('#cpar_no').val();
		var uid = $('input[name="ap_upload_uid"]').val();
				
		var uploader = new ss.SimpleUpload({
			button: btn,
			url: '/upload/'+cpar_no+'/'+uid,
			name: $(btn).attr('data-upload-name'),
			hoverClass: 'hover',
			focusClass: 'focus',
			responseType: 'json',
			multipart: true,
			startXHR: function() {
			},
			onSubmit: function() {
			},
			onComplete: function( filename, response ) {
				if ( response.success === true ) {
					$(btn).html('<i class="glyphicon glyphicon-upload"></i>&nbsp;Add File');
					
					var append_me = '<div class="file_attachment">' + 
						'<input type="hidden" name="ap_attachments[]" class="cpar_attachment" value="'+response.new_filename+'"/>' +  
						'<span class="file_name">'+filename+'</span>' +  
						'&nbsp;&nbsp;&nbsp;' + 
						'<i class="remove-ap-attachment glyphicon glyphicon-remove" style="cursor:pointer;"></i>' + 
					'</div>';
					
					$(btn).siblings('span').append(append_me);
					
					$('.remove-ap-attachment').click(function(){
						$(this).parent('div.file_attachment').detach();
						$(this).parent('div.uploaded_file').detach();
					});	
		  		} else {
					$(btn).html('<i class="glyphicon glyphicon-upload"></i>&nbsp;Choose Another File');
					if ( response.msg )  {
						alert(response.msg);
					} else {
						alert('An error occurred and the upload failed.');
					}
				}
			}
		});
		
	});
	
	$('.remove-ap-attachment').click(function(){
		$(this).parent('div.file_attachment').detach();
		$(this).parent('div.uploaded_file').detach();
	});
	
	$('#btn_task_attachments').each(function(){
		
		var btn = $(this);
		var cpar_no = $('#cpar_no').val();
				
		var uploader = new ss.SimpleUpload({
			button: btn,
			url: '/upload/task/'+cpar_no,
			name: $(btn).attr('data-upload-name'),
			hoverClass: 'hover',
			focusClass: 'focus',
			responseType: 'json',
			multipart: true,
			startXHR: function() {
			},
			onSubmit: function() {
			},
			onComplete: function( filename, response ) {
				if ( response.success === true ) {
					$(btn).html('<i class="glyphicon glyphicon-upload"></i>&nbsp;Add File');
					
					var append_me = '<div class="file_attachment">' + 
						'<input type="hidden" name="task_attachments[]" class="cpar_attachment" value=\'' + JSON.stringify(response) + '\'/>' +  
						'<span class="file_name">'+filename+'</span>' +  
						'&nbsp;&nbsp;&nbsp;' + 
						'<i class="remove-task-attachment glyphicon glyphicon-remove" style="cursor:pointer;"></i>' + 
					'</div>';
					
					$(btn).siblings('span').append(append_me);
					
					$('.remove-task-attachment').click(function(){
						$(this).parent('div.file_attachment').detach();
						$(this).parent('div.uploaded_file').detach();
					});	
		  		} else {
					$(btn).html('<i class="glyphicon glyphicon-upload"></i>&nbsp;Choose Another File');
					if ( response.msg )  {
						alert(response.msg);
					} else {
						alert('An error occurred and the upload failed.');
					}
				}
			}
		});
		
	});	
	
});

function initSubmitButtons() {
	
	$('.submit_btn').click(function(){
		b_save_only = false;	
	});
		
	//on submit
	$("#submit_btn").click(function() {
		var isDraft = false;
		submitForm(isDraft);
	});

	//on submit (CPAR Info only)
	$("#save_cpar_changes_btn").click(function() {
		var errors = new Array();
		m_status = CPAR_SUBMIT_S2_SAVE_CPAR_CHANGES;
		errors = validateCparInfoForm();

		if(errors && errors.length > 0) {
			showErrors(errors);
		} else {
			submitCparInfoForm(m_status);
		}
	});
	
	$('#send_reminder').click(function(){
		send_reminder();
	});

	//on submit (Addressee Draft)
	$("#save_draft_btn").click(function() {
		b_save_only = true;
		var isDraft = true;
		submitForm(isDraft);
	});	

	//on submit (TL Review)
	$("#proceed_btn").click(function() {
		var errors = new Array();
		var is_required_suffix = " is required.";

		if(!$("input[name=review_action]:checked").length) {
			errors.push("Please choose a review action.");
		}

		var remarks = $("#tl_review_remarks").val();
		if(!remarks) {
			if(!$("input[name=review_action]:checked").length || $("#mark_inv").is(":checked")) {
				errors.push("Remarks" + is_required_suffix);	
			}
		} else {
			if(remarks.length < MIN_CPAR_REMARKS || remarks.length > MAX_CPAR_REMARKS) {
				errors.push("Remarks (IMS Review) should be " + MIN_CPAR_REMARKS + " to " + MAX_CPAR_REMARKS + " characters.");
			}
		}

		if(errors && errors.length > 0) {
			showErrors(errors);
		} else {
			submitReviewForm();
		}
	});
}

function initDatepickers() {
	$(".datepicker").datepicker();

	$("span.dp_ao.input-group-addon").click(function() {
		var $this = $(this);
		var $input = $this.siblings('input.datepicker');

		if(!$input.is(':disabled')) {
			$input.datepicker('show');
		}
	});

	var accomplish_by = $("#accomplish_by").val();
	if(accomplish_by) {
		$("#accomplish_by").datepicker("setDate", new Date(accomplish_by));
	}
	//do not include days of the past
	/** TEMP
	$(".past_not_allowed").datepicker("setStartDate", new Date());
	*/
}

function initSelect2() {
	$(".adr_name").select2({
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

function submitForm(isDraft) {
	var url = '/cpar_s2/save';
	if(isDraft) {
		url = '/cpar_s2/saveDraft';
	}

	removeEmptyFileElements();

	var data = new FormData($("form#cpar_form")[0]);

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
	            		success_obj = obj;
            			if(obj.success_msg) {
	            			$("div#successful_save_modal .successful_save_modal_message").html(obj.success_msg);
						} else {
							$("div#successful_save_modal .successful_save_modal_message").html('CPAR successfully saved.');
						}	            		
	            		showSaveSuccessfulModal();
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

function submitReviewForm() {
	var data = new FormData($("form#cpar_form")[0]);

	blockBody();

	$.ajax({
        url: url = '/cpar_s2/saveReview',
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
            			if(obj.success_msg) {
	            			$("div#successful_save_modal .successful_save_modal_message").html(obj.success_msg);
						} else {
							$("div#successful_save_modal .successful_save_modal_message").html('CPAR successfully saved.');
						}	            		
	            		showSaveSuccessfulModal();
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

function removeEmptyFileElements() {
	$("div.attachments_form_group div.file_attachment").each(function() {
		if($(this).css("display") === "none") {
			$(this).remove();
		}
	});
}

function initUpload() {
	//add new input[type=file] element
	$("button#add_attachment").click(function() {
		var file_html = "" +
			"<div class='file_attachment' style='display: none;'> " + 
		  		"<input type='file' name='attachments[]' class='cpar_attachment' style='display: none;' />" +  
		  		"<span class='file_name'></span>" +  
		  		"&nbsp;&nbsp;&nbsp; " + 
		  		"<i id='remove_attachment' class='remove_attachment glyphicon glyphicon-remove'></i> " + 
		  	"</div>";

		$("div#attachments_container").append(file_html);

		var $div = $("div#attachments_container div.file_attachment:last-child");
		$div.find('input[type=file]').click();
	});

	$("button#rad_add_attachment").click(function() {
		var file_html = "" +
			"<div class='file_attachment' style='display: none;'> " + 
		  		"<input type='file' name='rad_attachments[]' class='cpar_attachment' style='display: none;' />" +  
		  		"<span class='file_name'></span>" +  
		  		"&nbsp;&nbsp;&nbsp; " + 
		  		"<i id='remove_rad_attachment' class='remove_attachment glyphicon glyphicon-remove'></i> " + 
		  	"</div>";

		$("div#rad_attachments_container").append(file_html);

		var $div = $("div#rad_attachments_container div.file_attachment:last-child");
		$div.find('input[type=file]').click();
	});

	$("button#rca_add_attachment").click(function() {
		var file_html = "" +
			"<div class='file_attachment' style='display: none;'> " + 
		  		"<input type='file' name='rca_attachments[]' class='cpar_attachment' style='display: none;' />" +  
		  		"<span class='file_name'></span>" +  
		  		"&nbsp;&nbsp;&nbsp; " + 
		  		"<i id='remove_rca_attachment' class='remove_attachment glyphicon glyphicon-remove'></i> " + 
		  	"</div>";

		$("div#rca_attachments_container").append(file_html);

		var $div = $("div#rca_attachments_container div.file_attachment:last-child");
		$div.find('input[type=file]').click();
	});

	//when a file is selected, populate display the selected file
	$("div.attachments_form_group").on("change", "input.cpar_attachment", function() {
		var file_name = $(this).val().split('/').pop().split('\\').pop();

		$(this).siblings("span.file_name").html(file_name);
		$(this).closest("div.file_attachment").slideDown(200);

		removeEmptyFileElements();
	});

	//remove attachment
	$("div.attachments_form_group").on("click", "i#remove_attachment", function() {
		var $div = $(this).closest("div");
		var div_class = $div.prop("class");

		if(div_class === "uploaded_file") {
			removed_uploads.push($div.find('a.file_name').html());
		}

		$div.slideUp("fast", function() { $(this).remove(); } );
	});

	$("div.attachments_form_group").on("click", "i#remove_rad_attachment", function() {
		var $div = $(this).closest("div");
		var div_class = $div.prop("class");

		if(div_class === "uploaded_file") {
			removed_rad_uploads.push($div.find('a.file_name').html());
		}

		$div.slideUp("fast", function() { $(this).remove(); } );
	});

	$("div.attachments_form_group").on("click", "i#remove_rca_attachment", function() {
		var $div = $(this).closest("div");
		var div_class = $div.prop("class");

		if(div_class === "uploaded_file") {
			removed_rca_uploads.push($div.find('a.file_name').html());
		}

		$div.slideUp("fast", function() { $(this).remove(); } );
	});
}

function removeEmptyFileElements() {
	$("div#attachments_form_group div.file_attachment").each(function() {
		if($(this).css("display") === "none") {
			$(this).remove();
		}
	});
}

function showSaveSuccessfulModal() {
	$("#successful_save_modal").modal('show');
}

function validateTask() {
	var errors = new Array();
	var is_required_suffix = " is required.";

	var task = $("#corr_task").val();
	var responsible_person = $("#corr_resp_per").val();
	var due_date = $("#corr_due_date").val();
	var remarks = $("#corr_remarks").val();

	task = task.trim();
	if(!task) {
		errors.push("Task" + is_required_suffix);
	} else {
		if(task.length < MIN_CPAR_TASK || task.length > MAX_CPAR_TASK) {
			errors.push("Task should be " + MIN_CPAR_TASK + " to " + MAX_CPAR_TASK + " characters.");
		}
	}

	responsible_person = responsible_person.trim();
	if(!responsible_person) {
		errors.push("Responsible person" + is_required_suffix);
	}

	due_date = due_date.trim();
	if(!due_date) {
		errors.push("Due date" + is_required_suffix);
	} else {
		var target_start_date = $("#corr_prev_target_start_date").val();
		var target_end_date = $("#corr_prev_target_end_date").val();

		if(target_start_date && target_end_date) {
			due_date = new Date(due_date);
			target_start_date = new Date(target_start_date);
			target_end_date = new Date(target_end_date);

			if(due_date < target_start_date || due_date > target_end_date) {
				errors.push("Due date should be between target start and end dates.");
			}
		} else {
			errors.push("Target start and end dates should be set before inputting action plan details.");
		}
	}

	remarks = remarks.trim();
	if(remarks) {
		if(remarks.length < MIN_CPAR_REMARKS || remarks.length > MAX_CPAR_REMARKS) {
			errors.push("Remarks should be " + MIN_CPAR_REMARKS + " to " + MAX_CPAR_REMARKS + " characters.");
		}
	}

	return errors;
}

function showMiniErrors(errors) {
	var str = "";

	$.each(errors, function(i, error) {
		str += "<i class='glyphicon glyphicon-exclamation-sign'></i>&nbsp;" + error + "<br/>";
	});

	hideModals();
	$("div.mini_error_content").html(str).parent('div.mini_error_container').slideDown();
}

var temp_task_id = 0;

function addTask() {
	var task = $("#corr_task").val();
	var responsible_person = $("#corr_resp_per").val();
	var responsible_person_name = $("#corr_resp_per").select2('data').text;
	var due_date = $("#corr_due_date").val();
	var remarks = $("#corr_remarks").val();
	var attachments = new Array();

	$('#task_attachments input[type="hidden"]').each(function(){
		
		attachments.push($(this).val());
		
	});
	
	var taskObj = new Object();
	taskObj.temp_task_id = temp_task_id;
	taskObj.task = task;
	taskObj.responsible_person = responsible_person;
	taskObj.responsible_person_name = responsible_person_name;
	taskObj.due_date = due_date;
	taskObj.remarks = remarks;
	taskObj.attachments = attachments;

	tasks_to_add.push(taskObj);
	
	//render newly added tasks
	var render_action = $("#add_task_btn").length;
	var str = '' + 
		'<tr class="new_task" style="display: none;" temp_task_id="'+temp_task_id+'">' +
		'	<td><div class="task_popover">' + escapeHtmlEntities(taskObj.task) + '</div></td>' +
		'	<td>' + 
		'		<input type="hidden" class="resp_per_hdn" value="' + taskObj.responsible_person + '" />' + 
		'		<span class="resp_per_name_span">' + taskObj.responsible_person_name + '</span></td>' +
		'	<td class="center">' + new Date(taskObj.due_date).format("mmm dd, yyyy") + '</td>' +
		'	<td><div class="remarks_popover">' + escapeHtmlEntities(taskObj.remarks) + '</div></td>' +
		'	<td>'; 
		
	for(var i = 0; i < attachments.length; i++) {
		json_temp = JSON.parse(attachments[i]);
		str = str + json_temp.new_filename+'<br/>';
	}
		 
		
	str = str + '</td>' +
		((render_action) ? '<td class="center"><a href="#" class="remove_new_task_btn">Remove</a></td>' : '') +
		'</tr>';

	$("#task_tbl tbody").append(str);
	$("#task_tbl tbody").find(".new_task:last-child").fadeIn("fast");

	//remove "No added tasks." row if it is present
	if($("tr.no_added_tasks").length) {
		$("tr.no_added_tasks").remove();
	}

	temp_task_id++;

	clearTask();
	init_popovers();
}

function clearTask() {
	$("#corr_task").val("");
	$("#corr_resp_per").val("");
	$("#corr_due_date").val("");
	$("#corr_remarks").val("");

	$("#corr_resp_per").select2("val", "");
	
	$('#task_attachments span').html('');
}

function removeOldTask(task_id, $row_el) {
	var length = tasks.length;
	for(var i = 0 ; i < length ; i++) {
		var task = tasks[i];
		if(task.id === task_id) {
			tasks.splice(i, 1);
			break;
		}
	}

	//add task_id to tasks_to_delete
	tasks_to_delete.push(task_id);

	//remove row HTML
	removeTaskRowHTML($row_el);
}

function removeNewTask($row_el) {
	var obj = new Object();
	obj.task = $row_el.find("td:eq(0)").find("div.task_popover").html();
	obj.responsible_person = $row_el.find("td:eq(1)").find("input.resp_per_hdn").val();
	obj.due_date = $row_el.find("td:eq(2)").html();
	obj.remarks = $row_el.find("td:eq(3)").find("div.remarks_popover").html();

	var length = tasks_to_add.length;
	for(var i = 0 ; i < length ; i++) {
		var task = tasks_to_add[i];
		if(task.task === obj.task && task.responsible_person === obj.responsible_person &&
			new Date(task.due_date).format("mmm dd, yyyy") === obj.due_date && task.remarks === obj.remarks) {

			tasks_to_add.splice(i, 1);
			break;
		}
	}

	//remove row HTML
	removeTaskRowHTML($row_el);
}

function removeTaskRowHTML($row_el) {
	$row_el.fadeOut("fast", function() { 
		$(this).remove();

		//if last record to be removed, show "No added tasks." row instead.
		var $task_tbl_tbody = $("#task_tbl tbody");
		if(!$task_tbl_tbody.html()) {
			$task_tbl_tbody.html('<tr class="no_added_tasks" style="display:none;"><td colspan="6" class="center">No added tasks.</td></tr>');
			$task_tbl_tbody.find("tr:first-child").slideDown("fast");
		}
	});
}

function displayTasks() {
	var str = '';
	if(tasks && tasks.length <= 0) {
		str = '<tr class="no_added_tasks"><td colspan="6" class="center">No added tasks.</td></tr>';
	} else {
		str = '';
		var length = tasks.length;
		var render_action = $("#add_task_btn").length;

		if(!render_action) {
			$("th.corr_th_remove.center").remove();
			$("div#action_details_input_wrapper").hide();
		}
				
		for(var i = 0 ; i < length ; i++) {
			str += '' + 
				'<tr class="old_task">' +
				'	<td>' + 
				'		<input type="hidden" class="task_id" value="' + tasks[i].id + '" />' + 
				'		<div class="task_popover">' + escapeHtmlEntities(tasks[i].task) + '</div>' +
				'	</td>' +
				'	<td>' + 
				'		<input type="hidden" class="resp_per_hdn" value="' + tasks[i].responsible_person + '" />' + 
				'		<span class="resp_per_name_span">' + tasks[i].responsible_person_name + '</span></td>' +
				'	<td class="center">' + tasks[i].due_date + '</td>' +
				'	<td><div class="remarks_popover">' + escapeHtmlEntities(tasks[i].remarks) + '</div></td>' +
				'	<td>';

			for(var a = 0; a < tasks[i].attachments.length; a++) {
				att = tasks[i].attachments[a].filename;
				str += '<a data-id="' + tasks[i].id + '" href="/file/task/'+att+'" class="task_file_name">'+att+'</a><br/>';
			}
			str += '</td>' +
				((render_action) ? '<td class="center"><a href="#" class="remove_old_task_btn">Remove</a></td>' : '') +
				'</tr>';
		}
	}

	$("table#task_tbl tbody").html(str);
}

function initTasks() {
	var serialized_tasks = $("#serialized_tasks").val();
	if(serialized_tasks) {
		var tasks_arr = JSON.parse(urldecode(serialized_tasks));

		var length = tasks_arr.length;
		var taskObj = null;
		for(var i = 0 ; i < length ; i++) {
			taskObj = new Object();
			taskObj.id = tasks_arr[i].id;
			taskObj.task = tasks_arr[i].task;
			taskObj.responsible_person = tasks_arr[i].responsible_person;
			taskObj.responsible_person_name = tasks_arr[i].responsible_person_name;
			taskObj.due_date = tasks_arr[i].due_date;
			taskObj.remarks = tasks_arr[i].remarks_addr;
			taskObj.attachments = tasks_arr[i].attachments;

			tasks.push(taskObj);
		}

		displayTasks();
	}

	//when Add (Task) is clicked
	$("#add_task_btn").click(function() {
		$("div.mini_error_container").slideUp();

		var errors = new Array();
		errors = validateTask();

		if(errors && errors.length > 0) {
			showMiniErrors(errors);
		} else {
			addTask();
		}
	});

	//remove old task
	$("table#task_tbl").on("click", "a.remove_old_task_btn", function() {
		var $row = $(this).closest("tr");
		var task_id = $row.find('input.task_id').val();

		removeOldTask(task_id, $row);

		return false;
	});

	//remove new task
	$("table#task_tbl").on("click", "a.remove_new_task_btn", function() {
		var $row = $(this).closest("tr");

		removeNewTask($row);

		return false;
	});
}

function urldecode(url) {
	return decodeURIComponent(url.replace(/\+/g, ' '));
}

function validateCparInfoForm() {
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

function submitCparInfoForm(m_status) {
	removeEmptyFileElements();

	var data = new FormData($("form#cpar_info_form")[0]);
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
	            		if(m_status === CPAR_SUBMIT_S2_SAVE_CPAR_CHANGES) {
	            			if(obj.success_msg) {
		            			$("div#successful_save_nr_modal .successful_save_modal_message").html(obj.success_msg);
							} else {
								$("div#successful_save_nr_modal .successful_save_modal_message").html('CPAR successfully saved.');
							}
	            			//if save CPAR changes, no redirect
	            			showSaveSuccessfulModal();
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

function showSaveSuccessfulNrModal() {
	$("#successful_save_nr_modal").modal('show');
}

function isCorrectDateRange(d_from, d_to) {
	var ret = false;

	var d_from_date = new Date(d_from);
	var d_to_date = new Date(d_to);

	return (d_from_date <= d_to_date);
}

function updateAccomplishByDate() {
	var id = $("#cpar_no").val();
	var accomplish_by = $("#accomplish_by").val();

	//validation
	var errors = new Array();
	var is_required_suffix = " is required.";

	if(!id.trim() || id == undefined) {
		errors.push("CPAR no. cannot be empty.");
	}

	if(!accomplish_by.trim() || accomplish_by == undefined) {
		errors.push("Accomplish by" + is_required_suffix);
	}

	if(errors && errors.length > 0) {
		showErrors(errors);
	} else {
		blockBody();

		$.ajax({
	        url: "/cpar_s2/updateDueDate",
	        type: "POST",
	        data: { id : id, accomplish_by : accomplish_by },
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
	            alert("Unable to update form. Please contact admin.");
	            unblockBody();
	        }
	    });
	}
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

function init_popovers() {
	$('.remarks_popover').popover({
	    html : true,
	    title: function() {
	      return $("#remarks_popover_head").html();
	    },
	    content: function() {
	      return $("#remarks_popover_content").html();
	    },
	    placement: 'top'
	});
	
	$(".remarks_popover").on('show.bs.popover', function () {
	  	//hide all other popovers
	  	$('.remarks_popover').not(this).popover('hide');

	  	//get remarks from table
	  	var remarks = $(this).html();
	  	$("textarea#remarks_popover").html(remarks);
	});

	$("body").on('click', '#update_remarks_popover_btn', function() {
		var errors = new Array();
		var new_remarks = $("textarea#remarks_popover").val();
		var $div_popover = $(this).closest("div.popover").siblings("div.remarks_popover");
		
		new_remarks = new_remarks.trim();
		if(new_remarks) {
			if(new_remarks.length < MIN_CPAR_REMARKS || new_remarks.length > MAX_CPAR_REMARKS) {
				errors.push("Remarks should be " + MIN_CPAR_REMARKS + " to " + MAX_CPAR_REMARKS + " characters.");
			}
		}

		if(errors && errors.length > 0) {
			showMiniErrors(errors);
		} else {
			var tr = $div_popover.closest("tr");
			if(tr.hasClass('new_task')) {
				$(tr).find("div.remarks_popover").html(new_remarks);
				refresh_tasks_to_add_remarks(tr, new_remarks);
			} else {
				submit_update_remarks($div_popover);
			}
		}

		$div_popover.popover('hide');
	});

	$("body").on('click', '#cancel_remarks_popover_btn', function() {
		$(this).closest("div.popover").siblings("div.remarks_popover").popover('hide');
	});
	
	//task
	$('.task_popover').popover({
	    html : true,
	    title: function() {
	      return $("#task_popover_head").html();
	    },
	    content: function() {
	      return $("#task_popover_content").html();
	    },
	    placement: 'top'
	});
	$(".task_popover").on('show.bs.popover', function () {
	  	//hide all other popovers
	  	$('.task_popover').not(this).popover('hide');

	  	//get remarks from table
	  	var task = $(this).html();
	  	$("textarea#task_popover").html(task);
	});

	$("body").on('click', '#update_task_popover_btn', function() {
		var errors = new Array();
		var new_task = $("textarea#task_popover").val();
		var $div_popover = $(this).closest("div.popover").siblings("div.task_popover");
		
		new_task = new_task.trim();
		if(new_task) {
			if(new_task.length < MIN_CPAR_TASK || new_task.length > MAX_CPAR_TASK) {
				errors.push("Task should be " + MIN_CPAR_TASK + " to " + MAX_CPAR_TASK + " characters.");
			}
		}

		if(errors && errors.length > 0) {
			showMiniErrors(errors);
		} else {
			var tr = $div_popover.closest("tr");
			if(tr.hasClass('new_task')) {
				$(tr).find("div.task_popover").html(new_task);
				refresh_tasks_to_add_task(tr, new_task);
			} else {
				submit_update_task($div_popover);
			}
		}

		$div_popover.popover('hide');
	});

	$("body").on('click', '#cancel_task_popover_btn', function() {
		$(this).closest("div.popover").siblings("div.task_popover").popover('hide');
	});
	
}

function refresh_tasks_to_add_task(tr, new_task) {
	var temp_id = parseInt(tr.attr('temp_task_id'));
	
	var length = tasks_to_add.length;
	for(var i = 0 ; i < length ; i++) {
		var task = tasks_to_add[i];
		if(task.temp_task_id == temp_id) {
			tasks_to_add[i].task = new_task;
			break;
		}
	}

}

function refresh_tasks_to_add_remarks(tr, new_remarks) {
	var temp_id = parseInt(tr.attr('temp_task_id'));
	
	var length = tasks_to_add.length;
	for(var i = 0 ; i < length ; i++) {
		var task = tasks_to_add[i];
		if(task.temp_task_id == temp_id) {
			tasks_to_add[i].remarks = new_remarks;
			break;
		}
	}
}
 
function submit_update_remarks($div_popover) {
	var new_remarks = $("textarea#remarks_popover").val();
	var data = {
		cpar_no : $("#cpar_no").val(),
		task_id : $div_popover.closest("tr").find("input.task_id").val(),
		new_remarks : new_remarks
	};

	//if addressee remarks or ims remarks
	var url = '/cpar_s2/update_addressee_remarks';

	blockBody();

	$.ajax({
        url: url,
        type: "POST",
        data: data,    
        success: function(result){
        	if(result) {
        		try {
        			var obj = JSON.parse(result);

	            	if(obj.success) {
	            		if(obj.apd) {
	            			render_single_task(obj.apd);
	            		}
            			if(obj.success_msg) {
	            			$("div#successful_save_nr_modal .successful_save_modal_message").html(obj.success_msg);
						} else {
							$("div#successful_save_nr_modal .successful_save_modal_message").html('CPAR successfully saved.');
						}
	            		showSaveSuccessfulNrModal();
	            	} else {
	            		showMiniErrors(obj.errors);
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

function submit_update_task($div_popover) {
	var new_task = $("textarea#task_popover").val();
	var data = {
		cpar_no : $("#cpar_no").val(),
		task_id : $div_popover.closest("tr").find("input.task_id").val(),
		new_task : new_task
	};

	//if addressee remarks or ims remarks
	var url = '/cpar_s2/update_addressee_task';

	blockBody();

	$.ajax({
        url: url,
        type: "POST",
        data: data,    
        success: function(result){
        	if(result) {
        		try {
        			var obj = JSON.parse(result);

	            	if(obj.success) {
	            		if(obj.apd) {
	            			render_single_task(obj.apd);
	            		}
            			if(obj.success_msg) {
	            			$("div#successful_save_nr_modal .successful_save_modal_message").html(obj.success_msg);
						} else {
							$("div#successful_save_nr_modal .successful_save_modal_message").html('CPAR successfully saved.');
						}
	            		showSaveSuccessfulNrModal();
	            	} else {
	            		showMiniErrors(obj.errors);
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


function render_single_task(apd) {
	var $row = $("input.task_id[value=" + apd.id + "]").closest("tr");
	$row.find("div.task_popover").html(apd.task);

	$row.find(".resp_per_hdn").html(apd.responsible_person);
	$row.find(".resp_per_name_span").html(apd.responsible_person_name);
	$row.find("td:eq(2)").html(apd.due_date);

	$row.find("div.remarks_popover").html(apd.remarks_addr);
}