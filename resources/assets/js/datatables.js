$(function() {

    $('.datatable').each(function() {
		var params = {};
		if (location.hash) {
			params = {
				search : {
					search : decodeURI(location.hash.substr(1))
				}
			};
		}
        var table = $(this).DataTable(params);
		table.on('search.dt', function() {
			location.hash = table.search();
		});
    });
    
});
