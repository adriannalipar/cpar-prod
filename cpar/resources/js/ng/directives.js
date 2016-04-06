(function(){
	var directives = angular.module('cparNg.directives', [])
	
	.directive('runPopover', function() {
		return function(scope, element, attrs) {
			if(scope.$last === true) {
				init_popovers();
				
				$('#task_count').val(scope.$index + 1);
			}
		};
	});
	
})();