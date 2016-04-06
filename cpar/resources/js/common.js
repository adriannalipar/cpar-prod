$(document).ready(function() {
	window.onbeforeunload = function() {
    	return "Attention!\nYou're about to leave CPAR System.";
	};
	
	$('a[rel!=ext]').click(function() { window.onbeforeunload = null; });
	$('form').submit(function() { window.onbeforeunload = null; });

	$("button.custom_alert_hide").click(function() {
		$(this).parent("div.alert").slideUp();
	});

	$('.modal').on('show.bs.modal', function() {
	    $(this).appendTo('body');
	})

	$(".top-link").click(function() {
		scrollToTop();
	});

	initDownloadEvent();
	
	toggle_result_of();
	
	$('select[name="result_of"]').change(function(){
		toggle_result_of();
	});
	
	$('.date-filed-picker').datepicker()
	.on('changeDate', function(e){
		var target = e.target;
		var date = e.date;
	
		if(!isNaN(date.getTime())) {
			$(target).siblings('input[type="hidden"]').val(date.getFullYear() + '-' + ('0' + (date.getMonth()+1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2));
		} else {
			$(target).siblings('input[type="hidden"]').val('');
		}
		
		if(!$(target).val()) {
			$(target).siblings('input[type="hidden"]').val('');
		}
	});
	
	$('.date-filed-picker').change(function(e){
		if(!$(this).val()) {
			$(this).siblings('input[type="hidden"]').val('');
		}
	});
	
	if($('#date_filed') && $('#date_filed').val()) {

		var date_filed = new Date($('#date_filed').val());
	
		$('#dp_date_filed').datepicker('update', date_filed);
	} else {
		var today = new Date();
		
		$('#dp_date_filed').datepicker('update', today);
		$('#dp_date_filed').siblings('input[type="hidden"]').val(today.getFullYear() + '-' + ('0' + (today.getMonth()+1)).slice(-2) + '-' + ('0' + today.getDate()).slice(-2));
	}
});

function show_message_modal(message, reload) {

	$("div#message_modal .successful_save_modal_message").html(message);
	
	if(reload) {
		$("div#message_modal").on("hidden.bs.modal", function() {
			window.location.reload();
		});
	} else {
		$("div#message_modal").on("hidden.bs.modal", function() {
			
		});
	}
	
	$("#message_modal").modal('show');

}

function toggle_result_of() {
	if($('select[name="result_of"] option:selected').text() == 'Others') {
		$('input[name="result_of_others"]').closest('div.form-group').show();
		$('input[name="result_of_others"]').removeAttr('disabled');
	} else {
		$('input[name="result_of_others"]').closest('div.form-group').hide();
		$('input[name="result_of_others"]').attr('disabled', 'disabled');
	}
}

function showErrors(errors) {
	var str = "";

	$.each(errors, function(i, error) {
		str += "<i class='glyphicon glyphicon-exclamation-sign'></i>&nbsp;" + error + "<br/>";
	});

	hideModals();
	scrollToTop();
	$("div.error_content").html(str).parent('div.error_container').slideDown();
}

function scrollToTop() {
	$("html, body").animate({ scrollTop: 0 }, "slow");
}

function hideModals() {
	$('.modal').modal('hide');
}

function isValidEmail(str) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(str);
}

function blockElementById(id) {
	$("#" + id).block({ 
		message: '<h4>Fetching Data...</h4>', 
		css: {
			'font-size': '12px',
			border: 'none', 
			padding: '15px', 
			backgroundColor: '#000', 
			'-webkit-border-radius': '10px', 
			'-moz-border-radius': '10px', 
			opacity: .5, 
			color: '#fff' 
		}
    });
}

function blockBody() {
	$("div.navbar-static-top").css("opacity", "0.5");
	$("div.page-wrap").block({ 
		message: '<h4>Saving Data...</h4>', 
		css: {
			'font-size': '12px',
			border: 'none', 
			padding: '15px', 
			backgroundColor: '#000', 
			'-webkit-border-radius': '10px', 
			'-moz-border-radius': '10px', 
			opacity: .5, 
			color: '#fff' 
		}
    });
}

function unblockBody() {
	$("div.navbar-static-top").css("opacity", "1");
	$("div.page-wrap").unblock();
}

function blockBodyWithMessage(msg) {
	$("div.navbar-static-top").css("opacity", "0.5");
	$("div.page-wrap").block({ 
		message: '<h4>'+msg+'</h4>', 
		css: {
			'font-size': '12px',
			border: 'none', 
			padding: '15px', 
			backgroundColor: '#000', 
			'-webkit-border-radius': '10px', 
			'-moz-border-radius': '10px', 
			opacity: .5, 
			color: '#fff' 
		}
    });
}

function unblockElementById(id) {
	$("#" + id).unblock();
}

function escapeTags( str ) {
	return String( str )
		.replace( /&/g, '&amp;' )
		.replace( /"/g, '&quot;' )
		.replace( /'/g, '&#39;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' );
}


function initDownloadEvent() {
	$("a.file_name").click(function() {
		var file_name = $(this).attr("href").split("/")[3];
		
		$("input#download_file_name").val(file_name);
		$("form#download_form").submit();

		return false;
	});
	
	$("a.ap_file_name").click(function() {
		var file_name = $(this).attr("href").split("/")[3];
				
		$('#ap_download_form input[name="file_name"]').val(file_name);
		$('#ap_download_form input[name="cpar_no"]').val($('#cpar_no').val());
		$("form#ap_download_form").submit();

		return false;
	});
	
	$("a.task_file_name").click(function() {
		var file_name = $(this).attr("href").split("/")[3];
				
		$('#task_download_form input[name="file_name"]').val(file_name);
		$('#task_download_form input[name="cpar_no"]').val($('#cpar_no').val());
		$('#task_download_form input[name="id"]').val($(this).attr("data-id"));
		$("form#task_download_form").submit();

		return false;
	});
	
	$("a.file_name_s3").click(function() {
		var file_name = $(this).attr("href").split("/")[3];
		
		$("form#download_form").prop("action", "/file/get_s3");
		$("input#download_file_name").val(file_name);
		$("form#download_form").submit();

		return false;
	});
}

function escapeHtmlEntities (str) {
  if (typeof jQuery !== 'undefined') {
    // Create an empty div to use as a container,
    // then put the raw text in and get the HTML
    // equivalent out.
    return jQuery('<div/>').text(str).html();
  }

  // No jQuery, so use string replace.
  return str
    .replace(/&/g, '&amp;')
    .replace(/>/g, '&gt;')
    .replace(/</g, '&lt;')
    .replace(/"/g, '&quot;');
}

function isCorrectDateRange(d_from, d_to) {
	var ret = false;

	var d_from_date = new Date(d_from);
	var d_to_date = new Date(d_to);

	return (d_from_date <= d_to_date);
}

function urldecode(url) {
	return decodeURIComponent(url.replace(/\+/g, ' '));
}

/*
 * Date Format 1.2.3
 * (c) 2007-2009 Steven Levithan <stevenlevithan.com>
 * MIT license
 *
 * Includes enhancements by Scott Trenda <scott.trenda.net>
 * and Kris Kowal <cixar.com/~kris.kowal/>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 */
var dateFormat=function(){var e=/d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,t=/\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,n=/[^-+\dA-Z]/g,r=function(e,t){e=String(e);t=t||2;while(e.length<t)e="0"+e;return e};return function(i,s,o){var u=dateFormat;if(arguments.length==1&&Object.prototype.toString.call(i)=="[object String]"&&!/\d/.test(i)){s=i;i=undefined}i=i?new Date(i):new Date;if(isNaN(i))throw SyntaxError("invalid date");s=String(u.masks[s]||s||u.masks["default"]);if(s.slice(0,4)=="UTC:"){s=s.slice(4);o=true}var a=o?"getUTC":"get",f=i[a+"Date"](),l=i[a+"Day"](),c=i[a+"Month"](),h=i[a+"FullYear"](),p=i[a+"Hours"](),d=i[a+"Minutes"](),v=i[a+"Seconds"](),m=i[a+"Milliseconds"](),g=o?0:i.getTimezoneOffset(),y={d:f,dd:r(f),ddd:u.i18n.dayNames[l],dddd:u.i18n.dayNames[l+7],m:c+1,mm:r(c+1),mmm:u.i18n.monthNames[c],mmmm:u.i18n.monthNames[c+12],yy:String(h).slice(2),yyyy:h,h:p%12||12,hh:r(p%12||12),H:p,HH:r(p),M:d,MM:r(d),s:v,ss:r(v),l:r(m,3),L:r(m>99?Math.round(m/10):m),t:p<12?"a":"p",tt:p<12?"am":"pm",T:p<12?"A":"P",TT:p<12?"AM":"PM",Z:o?"UTC":(String(i).match(t)||[""]).pop().replace(n,""),o:(g>0?"-":"+")+r(Math.floor(Math.abs(g)/60)*100+Math.abs(g)%60,4),S:["th","st","nd","rd"][f%10>3?0:(f%100-f%10!=10)*f%10]};return s.replace(e,function(e){return e in y?y[e]:e.slice(1,e.length-1)})}}();dateFormat.masks={"default":"ddd mmm dd yyyy HH:MM:ss",shortDate:"m/d/yy",mediumDate:"mmm d, yyyy",longDate:"mmmm d, yyyy",fullDate:"dddd, mmmm d, yyyy",shortTime:"h:MM TT",mediumTime:"h:MM:ss TT",longTime:"h:MM:ss TT Z",isoDate:"yyyy-mm-dd",isoTime:"HH:MM:ss",isoDateTime:"yyyy-mm-dd'T'HH:MM:ss",isoUtcDateTime:"UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"};dateFormat.i18n={dayNames:["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],monthNames:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec","January","February","March","April","May","June","July","August","September","October","November","December"]};Date.prototype.format=function(e,t){return dateFormat(this,e,t)}