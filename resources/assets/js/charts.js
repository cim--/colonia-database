var chart_xaxis_callback = function(v, i, vs) {
	return moment("3303-03-01").add(v, 'days').format("D MMM YYYY");
};


var tooltip_label_number = function (t, d) {
	return chart_xaxis_callback(t.xLabel)+" = "+t.yLabel;
};
var tooltip_label_percent = function (t, d) {
	return chart_xaxis_callback(t.xLabel)+" = "+t.yLabel + "%";
};
var tooltip_label_title = function (t, d) {
	return d.datasets[t[0].datasetIndex].label;
};
