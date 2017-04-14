var CDBMap = function() {
	var obj = {
		systemdata: [],
		canvas: null,
		systemobjects: {},
		systemlinks: {},
		systemtexts: {}
	};

	var phaseColors = [
		'#ff7777',
		'#ffaa77',
		'#77ff77',
		'#77ffff',
		'#7777ff',
		'#aa77ff',
		'#ffff77'
	];

	var scaleFactor = 12.5;

	var getCircle = function(sdata) {
		if (sdata.population > 0) {
			var radius = 2+Math.ceil(Math.log10(sdata.population));
		} else {
			var radius = 1;
		}

		return [
			radius,
			600 + (scaleFactor * sdata.x) - radius,
			500 - (scaleFactor * sdata.z) - radius
		];
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
			obj.systemlinks[sdata.name] = {};
			sysobjs.push(system);
		}
		obj.canvas.add(new fabric.Group(sysobjs));
	};

	var AddLinks = function() {
		var linkobjs = [];
		for (var i=0;i<obj.systemdata.length;i++) {
			var s1data = obj.systemdata[i];
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
					obj.systemlinks[s2data.name][s1data.name] = link;
					linkobjs.push(link);
				}
			}
		}
		var linkgroup = new fabric.Group(linkobjs)
		obj.canvas.add(linkgroup);
		linkgroup.sendBackwards();

	};

	var AddNames = function() {
		var nameobjs = [];
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
			nameobjs.push(systemname);
		}
		obj.canvas.add(new fabric.Group(nameobjs));	
	}

	obj.Init = function(systems) {
		obj.systemdata = systems;
		obj.canvas = new fabric.Canvas('cdbmap');

		obj.canvas.selection = false;
		
		AddSystems();
		AddLinks();
		AddNames();
	};

	return obj;
}();
