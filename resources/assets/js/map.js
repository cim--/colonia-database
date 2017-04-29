var CDBMap = function() {

	var reposition = false;
	var recolour = false;
	
	var obj = {
		systemdata: [],
		canvas: null,
		systemobjects: {},
		systemlinks1: {}, // 15 LY
		systemlinks2: {}, // 30 LY
		systemtexts: {}
	}

	var config = {
		projection: 'XZ',
		highlight: 'C:phase',
		links: 'C:mission',
		radius: 'P',
		filter: '0'
	};

	var ReadConfig = function() {
		opts = decodeURI(location.hash).substr(1).split("~");
		config.projection = opts[0];
		config.highlight = opts[1];
		config.links = opts[2];
		config.radius = opts[3];
		config.filter = opts[4];
	};

	var WriteConfig = function() {
		console.log(config);
		location.hash = [config.projection, config.highlight, config.links, config.radius, config.filter].join("~");
	};


	obj.SetSelectors = function() {
		$('#mapctrlprojection').val(config.projection);
		$('#mapctrlcolour').val(config.highlight);
		$('#mapctrllinks').val(config.links);
		$('#mapctrlsize').val(config.radius);
		$('#mapctrlfilter').val(config.filter);
	};
	
	if (location.hash) {
		ReadConfig();
		console.log(config);
	}
	
	var phaseColors = [
		'#ff7777', 
		'#ffaa77',
		'#77ff77', // CEI 1
		'#77ffdd',
		'#77ffff', // CEI 3
		'#ffdd77', // CCS 2
		'#77ddff', // CEI 4
		'#7777ff',
		'#aa77ff', // CEI 6
	];

	var factionColors = [
		'#444444',
		'#55ff55',
		'#77ff55',
		'#99ff55',
		'#ccff33', // 4 = retreatable
		'#eeff33',
		'#ffee33',
		'#ffaa33', // 7 = full
		'#ff3333', // 8 = overfull
	];

	var scaleFactor = 12;
	var mapXSize = 600; // half-size
	var mapYSize = 500; // radiuses
	
	var getCircle = function(sdata) {
		var radius = 1;
		if (sdata.population > 0) {
			if (config.radius == "P") {
				var radius = 2+Math.ceil(Math.sqrt(sdata.population/1000));
			} else if (config.radius == "T") {
				var radius = 1+Math.ceil(Math.sqrt(sdata.traffic));
			} else if (config.radius == "C") {
				var radius = 1+Math.ceil(Math.log10(sdata.crime+1));
			} else if (config.radius == "B") {
				var radius = 1+Math.ceil(Math.log10(sdata.bounties+1));
			} 
		}
		var projection = [0,0,0];
		if (config.projection.substr(0,2) == "XZ") {
			projection = [
				radius,
				(scaleFactor * sdata.x),
				-(scaleFactor * sdata.z)
			];
		} else if (config.projection.substr(0,2) == "XY") {
			projection = [
				radius,
				(scaleFactor * sdata.x),
				-(scaleFactor * sdata.y)
			];
		} else if (config.projection.substr(0,2) == "ZY") {
			projection = [
				radius,
				(scaleFactor * sdata.z),
				-(scaleFactor * sdata.y)
			];
		}

		projection[1] += mapXSize - radius;
		projection[2] += mapYSize - radius;
		return projection;
	};

	var getDistance = function(s1, s2) {
		var dist = Math.sqrt(
			(s1.x-s2.x) * (s1.x-s2.x) +
				(s1.y-s2.y) * (s1.y-s2.y) +
				(s1.z-s2.z) * (s1.z-s2.z)
		);
		return dist;
	};

	var SystemColour = function(sdata) {
		if (config.highlight == "C:phase") {
			return phaseColors[sdata.phase];
		} else if (config.highlight == "C:factions") {
			return factionColors[sdata.factions.length];
		} else if (config.highlight.substr(0,2) == "F:") {
			var faction = config.highlight.substr(2);
			if (sdata.controlling == faction) {
				return "#FF0000";
			} else if (sdata.factions.indexOf(faction) > -1) {
				return "#AAAA00";
			} else if (sdata.population > 0) {
				if (sdata.factions.length >= 7) {
					return "#8888bb";
				} else {
					return "#88bb88";
				}
			} else {
				return "#444444";
			}
		}
		return "#777777"; // unrecognised highlight
	};

	var IsFiltered = function(s1data, s2data) {
		if (config.filter == "1") {
			if (s1data.population == 0) {
				return 0;
			}
			if (s2data && s2data.population == 0) {
				return 0;
			}
		}
		return 1;
	};
	
	var AddSystems = function() {
		var sysobjs = [];
		for (var i=0;i<obj.systemdata.length;i++) {
			var sdata = obj.systemdata[i];
			var props = {};
			var circle = getCircle(sdata);
			props.radius = circle[0]; props.left = circle[1]; props.top = circle[2];
			props.stroke = SystemColour(sdata);
			props.strokeWidth = 1;
			var system = new fabric.Circle(props);
			obj.systemobjects[sdata.name] = system;
			obj.canvas.add(system);
		}
	};

	var AddLinks = function() {
		var linkobjs = [];
		for (var i=0;i<obj.systemdata.length;i++) {
			var s1data = obj.systemdata[i];
			obj.systemlinks1[s1data.name] = {};
			obj.systemlinks2[s1data.name] = {};
			for (var j=i+1;j<obj.systemdata.length;j++) {
				var s2data = obj.systemdata[j];
				var dist = getDistance(s1data, s2data);
				if (dist <= 30) {
					var props = {};
					var coords = [];
					var cen1 = getCircle(s1data);
					var cen2 = getCircle(s2data);
					coords = [
						cen1[1] + cen1[0],
						cen1[2] + cen1[0],
						cen2[1] + cen2[0],
						cen2[2] + cen2[0]
					];
					if (s1data.population > 0 && s2data.population > 0) {
						props.stroke = '#339933';
					} else {
						props.stroke = '#002200';
					}
					if (dist <= 15) {
						props.strokeWidth = 1;
					} else {
						props.strokeWidth = 0;
					}

					var link = new fabric.Line(coords, props);
					obj.systemlinks2[s1data.name][s2data.name] = link;
					if (dist <= 15) {
						obj.systemlinks1[s1data.name][s2data.name] = link;
					}
					obj.canvas.add(link);
				}
			}
		}


	};

	var AddNames = function() {
		for (var i=0;i<obj.systemdata.length;i++) {
			var sdata = obj.systemdata[i];
			var props = {};
			var circle = getCircle(sdata);
			props.left = circle[1]+circle[0]*2;
			props.top = circle[2]+circle[0];
			props.fill = '#cccccc';
			props.fontSize = 10;
			props.fontFamily = "Verdana";
			var name = sdata.name.replace(/^Eol Prou [A-Z][A-Z]-[A-Z] /, "");
			var systemname = new fabric.Text(name, props);
			obj.systemtexts[sdata.name] = systemname;
			obj.canvas.add(systemname);
		}
	}

	obj.Init = function(systems) {
		obj.systemdata = systems;
		obj.canvas = new fabric.StaticCanvas('cdbmap');

		obj.canvas.selection = false;
		
		AddLinks(); // have to do this first
		AddSystems();
		AddNames();

		reposition = true;
		recolour = true;
		obj.Redraw();
	};

	obj.setProjection = function(newp) {
		reposition = true;
		config.projection = newp;
		obj.Redraw();
	}

	obj.setHighlight = function(newc) {
		recolour = true;
		config.highlight = newc;
		obj.Redraw();
	}

	obj.setRadius = function(newr) {
		reposition = true;
		config.radius = newr;
		obj.Redraw();
	}

	obj.setLinks = function(newl) {
		recolour = true;
		config.links = newl;
		obj.Redraw();
	}

	obj.setFilter = function(newf) {
		recolour = true;
		config.filter = newf;
		obj.Redraw();
	}

	
	var RedrawReposition = function() {
		for (var i=0;i<obj.systemdata.length;i++) {
			var s1data = obj.systemdata[i];
			var c1 = getCircle(s1data);
			// move system
			var spot = obj.systemobjects[s1data.name];
			spot.set({
				'left': c1[1],
				'top': c1[2],
				'radius': c1[0]
			});

			var text = obj.systemtexts[s1data.name];
			text.set({
				'left': c1[1]+c1[0]*2,
				'top': c1[2]+c1[0]
			});
			
			for (var j=i+1;j<obj.systemdata.length;j++) {
				var s2data = obj.systemdata[j];
				var c2 = getCircle(s2data);
				// move line if it exists
				if (obj.systemlinks1[s1data.name][s2data.name]) {
					var link = obj.systemlinks1[s1data.name][s2data.name];
					link.set({
						'x1': c1[1] + c1[0],
						'y1': c1[2] + c1[0],
						'x2': c2[1] + c2[0],
						'y2': c2[2] + c2[0]
					});
				}
				if (obj.systemlinks2[s1data.name][s2data.name]) {
					var link = obj.systemlinks2[s1data.name][s2data.name];
					link.set({
						'x1': c1[1] + c1[0],
						'y1': c1[2] + c1[0],
						'x2': c2[1] + c2[0],
						'y2': c2[2] + c2[0]
					});
				}
			}
		}
	};
	
	var RedrawRecolour = function() {
		for (var i=0;i<obj.systemdata.length;i++) {
			var s1data = obj.systemdata[i];
			var spot = obj.systemobjects[s1data.name];
			spot.set({
				'stroke': SystemColour(s1data),
				'strokeWidth': IsFiltered(s1data)
			});
			var label = obj.systemtexts[s1data.name];
			if (IsFiltered(s1data)) {
				label.set({
					'fontSize': 10
				});
			} else {
				label.set({
					'fontSize': 0
				});
			}

			
			for (var j=0;j<obj.systemdata.length;j++) {
				var s2data = obj.systemdata[j];
				if (obj.systemlinks2[s1data.name][s2data.name]) {
					var link = obj.systemlinks2[s1data.name][s2data.name];
					var width = 0;
					if (IsFiltered(s1data, s2data)) {
						if (config.links == 'C:mission') {
							if (obj.systemlinks1[s1data.name][s2data.name]) {
								width = 1;
							}
						} else if (config.links.substr(0,2) == "S:") {
							var sn = config.links.substr(2);
							if (s1data.name == sn || s2data.name == sn) {
								width = 1;
							}
						}
					}
					link.set({
						'strokeWidth': width
					});
				}
			}
		}
		
	};
	
	obj.Redraw = function() {
		if (reposition) {
			RedrawReposition();
			reposition = false;
		}
		if (recolour) {
			RedrawRecolour();
			recolour = false;
		}
		obj.canvas.renderAll();
		WriteConfig();
	};
	
	return obj;
}();

/* Set up map ctrls */
$(document).ready(function() {
	$('#mapctrlprojection').change(function() {
		CDBMap.setProjection($(this).val());
	});

	$('#mapctrlcolour').change(function() {
		CDBMap.setHighlight($(this).val());
	});

	$('#mapctrlsize').change(function() {
		CDBMap.setRadius($(this).val());
	});

	$('#mapctrllinks').change(function() {
		CDBMap.setLinks($(this).val());
	});

	$('#mapctrlfilter').change(function() {
		CDBMap.setFilter($(this).val());
	});

	if (location.hash) {
		CDBMap.SetSelectors();
	}
});
