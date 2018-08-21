var ChartCallbacks = {};

ChartCallbacks.data = {};

ChartCallbacks.leapYearConspiracy = function(t) {
	var dm = t.format("D MMM ");
	var y = parseInt(t.format("YYYY"));
	y += 1286;
	return dm+y;
};

ChartCallbacks.chart_xaxis_callback = function(v, i, vs) {
	var t = moment("2017-03-02").add(v, 'days');
	return ChartCallbacks.leapYearConspiracy(t);
};

ChartCallbacks.chart_commodity_callback = function(v, i, vs) {
	if (v >= 0) {
		return ChartCallbacks.data.commodities[v];
	}
	return "";
};

ChartCallbacks.chart_station_callback = function(v, i, vs) {
	if (v >= 0) {
		return ChartCallbacks.data.stations[v];
	}
	return "";
};


ChartCallbacks.chart_xaxis_callback_datetime = function(v, i, vs) {
	var t = moment("2017-12-22").add(v, 'seconds');
	return ChartCallbacks.leapYearConspiracy(t);
};


ChartCallbacks.tooltip_label_number = function (t, d) {
	return ChartCallbacks.chart_xaxis_callback(t.xLabel)+" = "+t.yLabel;
};
ChartCallbacks.tooltip_label_datetime = function (t, d) {
	var ts = moment("2017-12-22").add(t.xLabel, 'seconds');
	return ChartCallbacks.leapYearConspiracy(ts) + ts.format(" HH:mm")+" = "+t.yLabel;
};
ChartCallbacks.tooltip_label_percent = function (t, d) {
	return ChartCallbacks.chart_xaxis_callback(t.xLabel)+" = "+t.yLabel + "%";
};
ChartCallbacks.tooltip_label_title = function (t, d) {
	if (d.datasets[t[0].datasetIndex].data[t[0].index].estimated) {
		return d.datasets[t[0].datasetIndex].label+" (Estimated)";
	} else {
		return d.datasets[t[0].datasetIndex].label;
	}
};

ChartCallbacks.tooltip_label_desc = function (t, d) {
	return d.datasets[t[0].datasetIndex].data[t[0].index].desc;
};
ChartCallbacks.tooltip_label_intensity = function (t, d) {
	return d.datasets[t.datasetIndex].data[t.index].intensity;
};

ChartCallbacks.tooltip_label_datetime_title = function (t, d) {
	if (d.datasets[t[0].datasetIndex].data[t[0].index].state) {
		return d.datasets[t[0].datasetIndex].label+" ("+d.datasets[t[0].datasetIndex].data[t[0].index].state+")";
	} else {
		return d.datasets[t[0].datasetIndex].label;
	}
};
