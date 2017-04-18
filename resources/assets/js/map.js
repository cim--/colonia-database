var CDBMap = function() {

	var reposition = false;
	var recolour = false;
	
	var obj = {
		systemdata: [],
		canvas: null,
		systemobjects: {},
		systemlinks: {},
		systemtexts: {},
		projection: 'XZ'
	};

	var phaseColors = [
		'#ff7777',
		'#ffaa77',
		'#77ff77',
		'#77ffff',
		'#7777ff',
		'#aa77ff',
		'#ffff77',
		'#aaff77',
	];

	var scaleFactor = 12;

	var getCircle = function(sdata) {
		if (sdata.population > 0) {
			var radius = 2+Math.ceil(Math.log10(sdata.population));
		} else {
			var radius = 1;
		}
		if (obj.projection == "XZ") {
			return [
				radius,
				600 + (scaleFactor * sdata.x) - radius,
				500 - (scaleFactor * sdata.z) - radius
			];
		} else if (obj.projection == "XY") {
			return [
				radius,
				600 + (scaleFactor * sdata.x) - radius,
				500 - (scaleFactor * sdata.y) - radius
			];
		} else if (obj.projection == "ZY") {
			return [
				radius,
				600 + (scaleFactor * sdata.z) - radius,
				500 - (scaleFactor * sdata.y) - radius
			];
		}
	};

	var getDistance = function(s1, s2) {
		var dist = Math.sqrt(
			(s1.x-s2.x) * (s1.x-s2.x) +
				(s1.y-s2.y) * (s1.y-s2.y) +
				(s1.z-s2.z) * (s1.z-s2.z)
		);
		return dist;
	}
	
	var AddSystems = function() {
		var sysobjs = [];
		for (var i=0;i<obj.systemdata.length;i++) {
			var sdata = obj.systemdata[i];
			var props = {};
			var circle = getCircle(sdata);
			props.radius = circle[0]; props.left = circle[1]; props.top = circle[2];
			props.stroke = phaseColors[sdata.phase];
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
			obj.systemlinks[s1data.name] = {};
			for (var j=i+1;j<obj.systemdata.length;j++) {
				var s2data = obj.systemdata[j];
				if (getDistance(s1data, s2data) <= 15) {
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
					props.strokeWidth = 1;

					var link = new fabric.Line(coords, props);
					obj.systemlinks[s1data.name][s2data.name] = link;
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
	};

	obj.setProjection = function(newp) {
		reposition = true;
		obj.projection = newp;
		obj.Redraw();
	}

	obj.Redraw = function() {
		if (reposition) {
			for (var i=0;i<obj.systemdata.length;i++) {
				var s1data = obj.systemdata[i];
				var c1 = getCircle(s1data);
				console.log(c1);
				// move system
				var spot = obj.systemobjects[s1data.name];
				spot.set({
					'left': c1[1],
					'top': c1[2]
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
					if (obj.systemlinks[s1data.name][s2data.name]) {
						var link = obj.systemlinks[s1data.name][s2data.name];
						link.set({
							'x1': c1[1] + c1[0],
							'y1': c1[2] + c1[0],
							'x2': c2[1] + c2[0],
							'y2': c2[2] + c2[0]
						});
					}
				}
			}
		}
		obj.canvas.renderAll();
	}
	
	return obj;
}();

$(document).ready(function() {
	$('#mapctrlprojection').change(function() {
		CDBMap.setProjection($(this).val());
	});
});
