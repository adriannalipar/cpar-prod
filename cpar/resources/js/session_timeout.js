$(document).ready(function() {	

	setInterval(function() { session_timeout(); }, POP_UP_BEFORE_TIMEOUT);

    //  If modal is present
    $('#session_message_modal').on('shown.bs.modal', function (e) {
        var LOGOUT_URL = window.location.origin + '/login/logout';
        EXECUTE_LOGOUT = setInterval(function(){ window.location = LOGOUT_URL; }, IDLE_TIME);
    });

    $('#session_update').click(function(){        
        clearInterval(EXECUTE_LOGOUT);
        $.ajax({
            url: "/cpar_common/sessionUpdate",
        });
    });
});

function session_timeout () {
	session_timeout_message('Your session is about to expire. Would you wish to continue working?');
}

function session_timeout_message(message) {
	$("div#session_message_modal .session_modal_message").html(message);	
	
	$("#session_message_modal").modal('show');
}