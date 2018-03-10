var chart_xaxis_callback = function(v, i, vs) {
	/* TODO: adjust for the leap year shift */
	return moment("3303-03-02").add(v, 'days').format("D MMM YYYY");
};


var tooltip_label_number = function (t, d) {
	return chart_xaxis_callback(t.xLabel)+" = "+t.yLabel;
};
var tooltip_label_percent = function (t, d) {
	return chart_xaxis_callback(t.xLabel)+" = "+t.yLabel + "%";
};
var tooltip_label_title = function (t, d) {
	if (d.datasets[t[0].datasetIndex].data[t[0].index].estimated) {
		return d.datasets[t[0].datasetIndex].label+" (Estimated)";
	} else {
		return d.datasets[t[0].datasetIndex].label;
	}
};
