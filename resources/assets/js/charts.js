var chart_xaxis_callback = function(v, i, vs) {
	return moment("3303-03-01").add(v, 'days').format("D MMM YYYY");
};


Chart.defaults.global.tooltips.callbacks.label = function (t, d) {
	return chart_xaxis_callback(t.xLabel)+" = "+t.yLabel + "%";
};
Chart.defaults.global.tooltips.callbacks.title = function (t, d) {
	return d.datasets[t[0].datasetIndex].label;
};
