angular.module('CIBonfire', ['ngRoute']);
angular.module('CIBonfire').factory('BFModule', BFModule);

function BFModule($http){
    var fac = {
    	
    	 create: function(module){
	    	return {
	    		get: function(id, callback){
		    		fac.get(module+"/api_get/"+id, callback);
		    	},
		    	save: function(data, callback){
		    		fac.post(module+"/api_save", data, callback);
		    	},
		    	list: function(query, callback){
		    		fac.post(module+"/api_list", query, callback);
		    	},
		    	delete: function(id, callback){
		    		fac.get(module+"/api_delete/"+id, callback);
		    	}
	    	};
	    },

	    get: function(path, callback){
	    	$http.get(path).success(callback);
	    },

	    post: function(path, data, callback){
	    	data.ci_csrf_token = ci_csrf_token();
	    	$http.post(path, data).success(callback);
	    }
    };

    return fac;
}