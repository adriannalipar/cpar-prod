(function(){
	var controllers = angular.module('cparNg.controllers', [])
	
	.controller('cparController', function($scope, cparAPIservice) {
		
		this.updateDateFiled = function() {
				$.ajax({
					url: "/api/updateDateFiled",
					type: "post",
					data: {cpar_no: $('#cpar_no').val(), date_filed: $('#date_filed').val()},
					dataType:'json',
					success: function(response) {
						if(response.result) {
							alert(response.message);
						} else {
							alert(response.message);
						}
					
					},
					error:function() {
						alert("An error has occured. Please contact system administrator.");
					}
				});

		};
		
	})
	
	.controller('actionController', function($scope, cparAPIservice) {
		
		this.actions = [];
		
		this.initializeTable = function() {
			this.actions = JSON.parse(urldecode($("#serialized_tasks").val()));
		};
		
		this.downloadFile = function(clickEvent, task_id, file_name) {
			var obj = clickEvent.currentTarget;
							
			$('#task_download_form input[name="file_name"]').val(file_name);
			$('#task_download_form input[name="cpar_no"]').val($('#cpar_no').val());
			$('#task_download_form input[name="id"]').val(task_id);
			$("form#task_download_form").submit();

			return false;
		};
				
	});
	
})();