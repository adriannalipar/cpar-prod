$(function(){
	$('.cell-datepicker').parent('td').datepicker({autoclose: true})
		.on('changeDate', function(e){
			var date = e.date;
			var display_date = $.format.date(date, 'MMM dd, yyyy');
			var formatted_date = $.format.date(date, 'yyyy-MM-dd');
			$(this).find('div.cell-datepicker').html(display_date);
			
			var url = "";
			
			if($(this).hasClass('review-history')) {
				url = '/api/updateReviewDate';
			} else if($(this).hasClass('ff-date')) {
				url = '/api/updateFFDate';
			} else if($(this).hasClass('next-ff-date')) {
				url = '/api/updateNextFFDate';
			} else if($(this).hasClass('action-due-date')) {
				url = '/api/updateActionDueDate';
			} else if($(this).hasClass('action-completed-date')) {
				url = '/api/updateActionCompletedDate';
			}
			
			
			if(url && $(this).attr('data-id')) {
				$.ajax({
					url: url,
					type: "POST",
					data: {id: $(this).attr('data-id'), date: formatted_date},
					dataType:'json',
					success: function(response) {
											
					},
					error:function() {
						alert("An error has occured. Please contact system administrator.");
					}
				});
			}
			
		});
})