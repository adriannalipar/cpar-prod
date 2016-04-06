var user_id = null;
$(document).ready(function() {
	$("#true_save_btn").click(function() {
		submitForm();

		return false;
	});

	$("#save_btn").click(function() {
		var errors = validateForm();

		if(errors && errors.length > 0) {
			showErrors(errors);
		} else {
			var $mr_flag_input = $("input#mr_flag");
			if($mr_flag_input.is(":checked") && !$mr_flag_input.prop("disabled")) {
				$("#confirm_modal").modal("show");
			} else {
				$("#true_save_btn").click();
			}
		}
	});

	$("div#successful_save_modal").on("hidden.bs.modal", function() {
	    if(!user_id) {
	    	window.location.reload();
	    } else {
		   window.location = "/user/edit/"+user_id; 
	    }
	})

	$("div#confirm_modal").on("show.bs.modal", function() {
		$("span#replacement_mr_user").html($("input#fname").val() + " " + $("input#lname").val());
	});

	$("#team_lead").select2({
		placeholder: "Team Leader",
	    minimumInputLength: 2,
	    allowClear: true,
	    ajax: {
	        url: "/user/getTLs",
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
});

function validateForm() {
	var errors = new Array();
	var is_required_suffix = " is required.";

	var fname = $("#fname").val();
	var mname = $("#mname").val();
	var lname = $("#lname").val();
	var pos_title = $("#pos_title").val();
	var location = $("#location").val();
	var team = $("#team").val();
	var email = $("#email").val();
	var access_level = $("#access_level").val();

	if(!fname.trim()) {
		errors.push("First Name" + is_required_suffix);
	} else {
		if(fname.length < MIN_USER_FNAME || fname.length > MAX_USER_FNAME) {
			errors.push("First Name should be " + MIN_USER_FNAME + " to " + MAX_USER_FNAME + " characters.");
		}
	}

	if(mname.trim()) {
		if(mname.length < MIN_USER_MNAME || mname.length > MAX_USER_MNAME) {
			errors.push("Middle Name should be " + MIN_USER_MNAME + " to " + MAX_USER_MNAME + " characters.");
		}
	}

	if(!lname.trim()) {
		errors.push("Last Name" + is_required_suffix);
	} else {
		if(lname.length < MIN_USER_LNAME || lname.length > MAX_USER_LNAME) {
			errors.push("Last Name should be " + MIN_USER_LNAME + " to " + MAX_USER_LNAME + " characters.");
		}
	}

	if(!pos_title.trim()) {
		errors.push("Position Title" + is_required_suffix);
	} else {
		if(pos_title.length < MIN_USER_POS_TITLE || pos_title.length > MAX_USER_POS_TITLE) {
			errors.push("Position Title should be " + MIN_USER_POS_TITLE + " to " + MAX_USER_POS_TITLE + " characters.");
		}
	}

	if(!location.trim()) {
		errors.push("Location" + is_required_suffix);
	}

	if(!team.trim()) {
		errors.push("Team" + is_required_suffix);
	}

	if(!email.trim()) {
		errors.push("Email" + is_required_suffix);
	} else if(email.length < MIN_USER_EMAIL_ADDRESS || email.length > MAX_USER_EMAIL_ADDRESS) {
		errors.push("Email Address should be " + MIN_USER_EMAIL_ADDRESS + " to " + MAX_USER_EMAIL_ADDRESS + " characters.");
	} else if(!isValidEmail(email.trim())) {
		errors.push("Invalid email address.");
	}

	if(!access_level.trim()) {
		errors.push("Access Level" + is_required_suffix);
	}

	if(!$("input[name=user_status]:checked").val().trim()) {
		errors.push("User Status" + is_required_suffix);
	}

	if(errors.length <= 0 && $("#user_status-0").is(":checked") && $("#mr_flag").is(":checked")) {
		errors.push("Cannot set an inactive user as Management Representative.");
	}

	return errors;
}

function submitForm() {
	var data = $("form#user_form").serialize();
	if($("input#mr_flag").is(":disabled")) {
		data += "&mr_flag=" + $("input#mr_flag").val();
	}

	$.ajax({
        url: "/user/save",
        type: "post",
        data: data,
        success: function(result){
        	if(result) {
        		try {
        			var obj = JSON.parse(result);

	            	if(obj.success) {
	            		$(".modal").modal('hide');
	            		user_id = obj.id;
            			if(obj.success_msg) {
	            			$("div#successful_save_modal .successful_save_modal_message").html(obj.success_msg);
						} else {
							$("div#successful_save_modal .successful_save_modal_message").html('User successfully saved.');
						}
						showSaveSuccessfulModal();
	            	} else {
	            		showErrors(obj.errors);
	            	}
        		} catch(e) {
        			alert("Invalid response from server. Please contact admin.");
        		}            	
            } else {
            	alert("No response from server. Please contact admin.");
            }
        },
        error:function(e){
            if(e.status == 413) {
	        	showErrors(new Array('Files uploaded may be too large. Please upload smaller files.'));
        	} else {
	        	alert("Unable to save form. Please contact admin.");
        	}
        }
    });
}

function showSaveSuccessfulModal() {
	$("#successful_save_modal").modal('show');
}