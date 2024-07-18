var routes = readData('routes');
var points = readData('points');



function loadData() {

	if(isParamNotSet('downloaded')){
		checkLoginTo('download configurations','download');
		return;
	}

    var html = '';

    for (var rot in routes) {
		var complete=0;
		for(var pnt in points)
			if(points[pnt].route_id==routes[rot].id)
				if(points[pnt].complete)
					complete++;
        html = html + "<a href='#' onclick='javascript: viewDetails(" + routes[rot].id + ", \""+routes[rot].name+"\")' class='ui-btn'><table><tr><td style='text-align:left;font-weight:bold'>" + routes[rot].name + "<td><td><td></tr><tr><td style='text-align:left;font-weight:normal;padding-right:20px'>Points: " + routes[rot].total + "<td><td style='text-align:left;font-weight:normal'>Complete: " + complete + " (" + complete / Number(routes[rot].total) * 100 + "%)<td></tr></table>"+(complete==Number(routes[rot].total)?"<div class='ui-li-count ui-body-inherit' style='background-color: green;color: white;text-shadow: none !important;border: none;'>&#10003;</div>":"")+"</a>";

    }

	if(html == '') {
		html = "No route found";
	}

    $("#details").html(html);

}



function viewDetails(id, obj) {

	writeParameter('route_id',id);

	writeParameter('route_path', obj);

    var url = 'route.html';

    window.location.assign(url);

}

