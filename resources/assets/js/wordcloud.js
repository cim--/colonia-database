$(document).ready(function() {

	$('#wordcloud').each(function() {
		var cloud = d3.layout.cloud();
		
		var draw = function draw(words) {
			d3.select("#wordcloud").append("svg")
				.attr("width", cloud.size()[0])
				.attr("height", cloud.size()[1])
				.append("g")
				.attr("transform", "translate(" + cloud.size()[0] / 2 + "," + cloud.size()[1] / 2 + ")")
				.selectAll("text")
				.data(words)
				.enter().append("text")
				.style("font-size", function(d) { return d.size + "px"; })
				.style("font-family", "sans-serif")
				.style("fill", function(d, i) { return d.colour; })
				.attr("text-anchor", "middle")
				.attr("transform", function(d) {
					return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
				})
				.text(function(d) { return d.text; });
		};
		
		cloud
			.size([700, 700])
			.words(wordmapdata)
			.fontSize(function(d) {
				if (d.size == 1) { return 8; }
				return Math.min((0.5+d.size)*9, 120);
			})
			.font("sans-serif")
			.rotate(function() { return (~~(Math.random() * 4) - 2) * 30; })
			.on('end', draw)
			.start();
	});
});
