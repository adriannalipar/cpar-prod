(function(){
var filters = angular.module('cparNg.filters', [])
	.filter('cpar_task', function() {
		return function(str) {
			return $('<div/>').text(str).html();
		};
	});
})();