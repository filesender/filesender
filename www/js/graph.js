$.ajax({
	url: "js/graph/uploadGraph.php"
}).done(function(json) {
	var ctx = $("#speedChart");
	var speedChart = new Chart(ctx,$.parseJSON(json));
	var hoverInTimer;
	var hoverOutTimer;
	ctx.hover(function() {
		if (hoverOutTimer) {
			window.clearTimeout(hoverOutTimer);
			hoverOutTimer=null;
		}
		if (!hoverInTimer) {
			hoverInTimer=window.setTimeout(function() {
				hoverInTimer=null;
				$("#graphDiv").stop().animate({width:'100%',height:'300px'},{
					duration: 400,
					progress: function() { speedChart.resize(); } 
				});
			}, 300);
		}
	},function() {
		if (hoverInTimer) {
			window.clearTimeout(hoverInTimer);
			hoverInTimer=null;
		}
		if (!hoverOutTimer) {
			hoverOutTimer=window.setTimeout(function() {
				hoverOutTimer=null;
				$("#graphDiv").stop().animate({width:'400px',height:'200px'},{
					duration: 400,
					progress: function() { speedChart.resize(); } 
				});
			}, 2000);
		}
	});
});
