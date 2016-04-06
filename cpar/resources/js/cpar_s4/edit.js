var tools_used = new Array();
var removed_uploads = new Array();
var removed_rad_uploads = new Array();
var removed_rca_uploads = new Array();
var tasks = new Array();
var tasks_to_add = new Array();
var tasks_to_delete = new Array();
var is_addressee = false;

$(document).ready(function() {
	initSelect2();
	initDatepickers();
	initUpload();
	initTasks();
	initSubmitButtons();
	if($("#is_admin").length > 0) {
		init_popovers();
	}

	//update date btn
	$("#update_date_btn").click(function() {
		updateAccomplishByDate();
	});

	//after reading successful save message, redirect to CPAR list
	$("div#successful_save_modal").on("hidden.bs.modal", function() {
	    window.location = "/cpar/";
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
	//when Proceed button is clicked while in 4A
	$("#proceed_btn").click(function() {
		var errors = new Array();
		errors = validateImsReview();

		if(errors && errors.length > 0) {
			showErrors(errors);
		} else {
			submitReviewForm();
		}
	});

	//when Proceed button is clicked while in 4B
	$("#proceed_4b_btn").click(function() {
		var errors = new Array();
		errors = validateImsReview_3b();

		if(errors && errors.length > 0) {
			showErrors(errors);
		} else {
			submitReviewForm_3b();
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

function showSaveSuccessfulModal() {
	$("#successful_save_modal").modal('show');
}

function showSaveSuccessfulNrModal() {
	$("#successful_save_nr_modal").modal('show');
}

function showMiniErrors(errors) {
	var str = "";

	$.each(errors, function(i, error) {
		str += "<i class='glyphicon glyphicon-exclamation-sign'></i>&nbsp;" + error + "<br/>";
	});

	hideModals();
	$("div.mini_error_content").html(str).parent('div.mini_error_container').slideDown();
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

function submitReviewForm() {
	var data = {
		id : $("#cpar_no").val(),
		review_action : $("input[name=review_action]:checked").val(),
		ff_up_date : $("#ff_up_date").val(),
		pb_stage : $("#pb_stage").val(),
		next_due_date : $("#next_due_date").val(),
		remarks : $("#remarks").val()
	}

	blockBody();

	$.ajax({
        url: url = '/cpar_s3/saveReview',
        type: "POST",
        data: data,    
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

function displayTasks() {
	var str = '';
	var length = tasks.length;
	var render_action = $("#add_task_btn").length;

	if(!render_action) {
		$("th.corr_th_remove.center").remove();
		$("div#action_details_input_wrapper").hide();
	}
		
	if($("#is_admin").length > 0) {
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
				'	<td class="center action-due-date" data-id="' + tasks[i].id + '"><div class="cell-datepicker">' + tasks[i].due_date + '</div></td>' +
				'	<td class="center action-completed-date" data-id="' + tasks[i].id + '"><div class="cell-datepicker">' + tasks[i].completed_date + '</div></td>' +
				'	<td class="center">' + tasks[i].status_name + '</td>' +
				'	<td><div class="remarks_popover">' + escapeHtmlEntities(tasks[i].remarks) + '</div></td>' +
				'	<td>' + escapeHtmlEntities(tasks[i].remarks_ims) + '</td>' +
				'	<td>';

			for(var a = 0; a < tasks[i].attachments.length; a++) {
				att = tasks[i].attachments[a].filename;
				str += '<a data-id="' + tasks[i].id + '" href="/file/task/'+att+'" class="task_file_name">'+att+'</a><br/>';
			}
			str += '</td>' +
				((render_action) ? '<td class="center"><a href="#" class="remove_old_task_btn">Remove</a></td>' : '') +
				'</tr>';
		}
	} else {
		for(var i = 0 ; i < length ; i++) {
			str += '' + 
				'<tr class="old_task">' +
				'	<td>' + 
				'		<input type="hidden" class="task_id" value="' + tasks[i].id + '" />' + escapeHtmlEntities(tasks[i].task) + 
				'	</td>' +
				'	<td>' + 
				'		<input type="hidden" class="resp_per_hdn" value="' + tasks[i].responsible_person + '" />' + 
				'		<span class="resp_per_name_span">' + tasks[i].responsible_person_name + '</span></td>' +
				'	<td class="center action-due-date" data-id="' + tasks[i].id + '"><div class="cell-datepicker">' + tasks[i].due_date + '</div></td>' +
				'	<td class="center action-completed-date" data-id="' + tasks[i].id + '"><div class="cell-datepicker">' + tasks[i].completed_date + '</div></td>' +
				'	<td class="center">' + tasks[i].status_name + '</td>' +
				'	<td>' + escapeHtmlEntities(tasks[i].remarks) + '</td>' +
				'	<td>' + escapeHtmlEntities(tasks[i].remarks_ims) + '</td>' +
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
	
	task = task.replace(/[^a-z0-9 ,.?!]/ig, '');
	remarks = remarks.replace(/[^a-z0-9 ,.?!]/ig, '');
	
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
		'<tr class="new_task" style="display: none;">' +
		'	<td><div class="task_popover">' + escapeHtmlEntities(taskObj.task) + '</div></td>' +
		'	<td>' + 
		'		<input type="hidden" class="resp_per_hdn" value="' + taskObj.responsible_person + '" />' + 
		'		<span class="resp_per_name_span">' + taskObj.responsible_person_name + '</span></td>' +
		'	<td class="center">' + new Date(taskObj.due_date).format("mmm dd, yyyy") + '</td>' +
		'	<td class="center"></td>' +
		'	<td class="center"></td>' +
		'	<td><div class="remarks_popover">' + escapeHtmlEntities(taskObj.remarks) + '</div></td>' +
		'	<td class="center"></td>' +
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
	obj.remarks = $row_el.find("td:eq(5)").find("div.remarks_popover").html();

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
			$task_tbl_tbody.html('<tr class="no_added_tasks" style="display:none;"><td colspan="5" class="center">No added tasks.</td></tr>');
			$task_tbl_tbody.find("tr:first-child").slideDown("fast");
		}
	});
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

			taskObj.status = tasks_arr[i].status;
			taskObj.status_name = tasks_arr[i].status_name;
			taskObj.remarks_ims = tasks_arr[i].remarks_ims;
			taskObj.completed_date = tasks_arr[i].completed_date;
			
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

function validateImsReview() {
	var errors = new Array();
	var is_required_suffix = " is required.";

	if(!$("input[name=review_action]:checked").length) {
		errors.push("Please choose a review action.");
	} else if($("#mark_as_effective").is(":checked")) {
		//do nothing
	} else if($("#push_back").is(":checked")) {
		if(!$("#pb_stage").val()) {
			errors.push("Push Back Stage" + is_required_suffix);
		}
	}

	if(!$("#next_due_date").val()) {
		errors.push("Next Due Date" + is_required_suffix);
	}

	var remarks = $("#remarks").val();
	if(!remarks) {
		if(!$("input[name=review_action]:checked").length || $("#push_back").is(":checked")) {
			errors.push("Remarks" + is_required_suffix);
		}
	} else {
		if(remarks.length < MIN_CPAR_REMARKS || remarks.length > MAX_CPAR_REMARKS) {
			errors.push("Remarks should be " + MIN_CPAR_REMARKS + " to " + MAX_CPAR_REMARKS + " characters.");
		}
	}

	return errors;
}

function validateImsReview_3b() {
	var errors = new Array();
	var is_required_suffix = " is required.";

	if(!$("input[name=review_action]:checked").length) {
		errors.push("Please choose a review action.");
	} else if($("#mark_as_closed").is(":checked")) {
		//do nothing
	} else if($("#push_back").is(":checked")) {
		if(!$("#pb_stage").val()) {
			errors.push("Push Back Stage" + is_required_suffix);
		}

		if(!$("#next_due_date").val()) {
			errors.push("Next Due Date" + is_required_suffix);
		}
	}

	var remarks = $("#remarks").val();
	if(!remarks) {
		if(!$("input[name=review_action]:checked").length || $("#push_back").is(":checked")) {
			errors.push("Remarks" + is_required_suffix);
		}
	} else {
		if(remarks.length < MIN_CPAR_REMARKS || remarks.length > MAX_CPAR_REMARKS) {
			errors.push("Remarks should be " + MIN_CPAR_REMARKS + " to " + MAX_CPAR_REMARKS + " characters.");
		}
	}

	return errors;
}

function submitReviewForm() {
	var data = {
		id : $("#cpar_no").val(),
		review_action : $("input[name=review_action]:checked").val(),
		pb_stage : $("#pb_stage").val(),
		next_due_date : $("#next_due_date").val(),
		remarks : $("#remarks").val()
	}

	blockBody();

	$.ajax({
        url: url = '/cpar_s4/saveReview',
        type: "POST",
        data: data,    
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

function submitReviewForm_3b() {
	var data = {
		id : $("#cpar_no").val(),
		review_action : $("input[name=review_action]:checked").val(),
		pb_stage : $("#pb_stage").val(),
		next_due_date : $("#next_due_date").val(),
		remarks : $("#remarks").val()
	}

	blockBody();

	$.ajax({
        url: url = '/cpar_s4/saveReview_3b',
        type: "POST",
        data: data,    
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
				new_remarks = new_remarks.replace(/[^a-z0-9 ,.?!]/ig, '');
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
				new_task = new_task.replace(/[^a-z0-9 ,.?!]/ig, '');
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