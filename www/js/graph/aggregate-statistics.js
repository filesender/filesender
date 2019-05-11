
function aggregateStatisticsSetup( divElement, epochtype, eventtype, querytype )
{
    $.ajax({
	url: "js/graph/aggregate-statistics-data-for-chart.php",
        data: { epochtype: epochtype, eventtype: eventtype, querytype: querytype }
    }).done(function(json) {
	var ctx = $('#' + divElement);
	var speedChart = new Chart(ctx,$.parseJSON(json));
    });
}
