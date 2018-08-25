var CDBTracker = function() {

	var obj = {};

	obj.active = function active() {
		if (window.localStorage.getItem('systems')) {
			return true;
		}
		return false;
	};

	obj.activate = function activate() {
		window.localStorage.setItem('systems', "X");
		window.localStorage.setItem('stations', "X");
		window.localStorage.setItem('factions', "X");
		window.localStorage.setItem('installations', "X");
		window.localStorage.setItem('megaships', "X");
		window.localStorage.setItem('sites', "X");
		window.localStorage.setItem('engineers', "X");
		$("#enabletracktools").hide();
		$("#disabletracktools").show();
		$('#tracktools').removeClass('inactive');
		obj.refresh();
	};

	obj.reset = function reset() {
		if (window.confirm("This will clear all your saved visits and is not reversible - are you sure?")) {
			window.localStorage.clear();
			$("#disabletracktools").hide();
			$("#enabletracktools").show();
			$('#tracktools').addClass('inactive');
			obj.refresh();
		}
	};
	
	obj.visit = function(domain, id) {
		var list = window.localStorage.getItem(domain);
		if (!list) {
			list = "X";
		}
		var items = list.split(";");
		if (!items.includes(""+id)) {
			list += ";"+id;
			window.localStorage.setItem(domain, list);
		}
	};

	obj.visited = function(domain, id) {
		var list = window.localStorage.getItem(domain);
		if (!list) {
			return false;
		}
		var items = list.split(";");
		return items.indexOf(""+id) != -1;
	};
	
	obj.unvisit = function(domain, id) {
		var list = window.localStorage.getItem(domain);
		var items = list.split(";");
		var idx = items.indexOf(""+id);
		if (idx != -1) {
			items.splice(idx, 1);
			list = items.join(";");
			window.localStorage.setItem(domain, list);
		}
	};

	obj.countVisited = function(domain) {
		var list = window.localStorage.getItem(domain);
		if (list) {
			return list.split(";").length-1;
		} else {
			window.localStorage.setItem(domain, "X");
			return 0;
		}
	};

	obj.progressBar = function (element, domain) {
		var total = parseFloat($(element+" .total").text());
		var count = obj.countVisited(domain);

		var percent = count * 100 / total;
		$(element+" .recorded").css('width', percent+"%");
		$(element+" .tracked").text(count);

		$(element+" .items span").each(function() {
			var id = $(this).data('number');
			if(obj.visited(domain, id)) {
				$(this).addClass('visited');
				$(this).removeClass('unvisited');
			} else {
				$(this).addClass('unvisited');
				$(this).removeClass('visited');
			}
		});
	};

	obj.refresh = function() {
		obj.progressBar('#systemtrack', 'systems');
		obj.progressBar('#stationtrack', 'stations');
		obj.progressBar('#factiontrack', 'factions');
		obj.progressBar('#megashiptrack', 'megaships');
		obj.progressBar('#installationtrack', 'installations');
		obj.progressBar('#sitetrack', 'sites');
		obj.progressBar('#engineertrack', 'engineers');
	};

	
	return obj;
}();

/* Set up controls */
$(document).ready(function() {

	$("#enabletracktools").click(CDBTracker.activate);
	$("#disabletracktools").click(CDBTracker.reset);

	if (CDBTracker.active()) {
		$("#enabletracktools").hide();
	} else {
		$("#disabletracktools").hide();
		$('#tracktools').addClass('inactive');
	}
	
	$('#tracktools').each(CDBTracker.refresh);

	$('#trackbox').each(function() {
		if (!CDBTracker.active()) {
			return;
		}
		$(this).show();
		var domain = $(this).data('domain');
		var id = $(this).data('number');
		if (CDBTracker.visited(domain, id)) {
			$(this).addClass('visited');
		} else {
			$(this).addClass('unvisited');
		}
		$(this).html('<button class="visit">Visited: <span class="marker">&#x2718;</span></button><button class="unvisit">Visited: <span class="marker">&#x2714;</span></button>');
		$('#trackbox .visit').click(function() {
			CDBTracker.visit(domain, id);
			$('#trackbox').addClass('visited');
			$('#trackbox').removeClass('unvisited');
		});
		$('#trackbox .unvisit').click(function() {
			CDBTracker.unvisit(domain, id);
			$('#trackbox').addClass('unvisited');
			$('#trackbox').removeClass('visited');
		});
	});

	$('#tracktools span').click(function() {
		if (!CDBTracker.active()) {
			return;
		}
		var domain = $(this).data('domain');
		var id = $(this).data('number');
		if ($(this).hasClass('unvisited')) {
			CDBTracker.visit(domain, id);
		} else {
			CDBTracker.unvisit(domain, id);
		}
		CDBTracker.refresh();
	});

	
	$('.tracker .progressbar').click(function() {
		$(this).closest('.tracker').find('.itemlist').toggle();
	});
});
