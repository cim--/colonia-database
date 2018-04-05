$(function() {

    $('.datatable').each(function() {
		var params = {
			search : {
				regex : true
			}
		};
		if (location.hash) {
			params.search.search = decodeURI(location.hash.substr(1));
		}
        var table = $(this).DataTable(params);
		table.on('search.dt', function() {
			location.hash = encodeURI(table.search());
		});
    });
    
});
