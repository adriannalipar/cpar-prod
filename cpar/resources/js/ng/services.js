/* (function(){ */
	var service = angular.module('cparNg.services', [])
	.factory('cparAPIservice', function($http) {
		
		var cparAPI = {};
		
		cparAPI.updateDateFiled = function(cpar_no, date_filed) {
			return $http({
				method: 'POST'
				, data	: {cpar_no: cpar_no, date_filed: date_filed}
				, url	: '/api/updateDateFiled'
			});
		}
		
		return cparAPI;
	
	});
/* })(); */