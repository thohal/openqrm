function init() {
get_events();
get_appliances();
get_resources();
js_events = window.setInterval("get_events()", 15000);	
js_appliances = window.setInterval("get_appliances()", 15000);
js_resources = window.setInterval("get_resources()", 15000);	
}


function get_events() {
xmlHttp = null;
xmlHttp = new Request();

if (xmlHttp) {
    xmlHttp.open("GET", "server/event/event-xmlhttprequest.php?tmp="+Math.random(), true);
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 4) {
		values = xmlHttp.responseText.split(',');
		document.getElementById('events_critical').innerHTML = values[0];
		if(values[0] > 0) {
			document.getElementById('Event_box').style.display = 'block';
		} else {
			document.getElementById('Event_box').style.display = 'none';
		}
        }
    };
    xmlHttp.send(null);
}

}


function get_appliances() {
apHttp = null;
apHttp = new Request();

if (apHttp) {
    apHttp.open("GET", "server/appliance/appliance-xmlhttprequest.php?tmp="+Math.random(), true);
    apHttp.onreadystatechange = function () {
        if (apHttp.readyState == 4) {
		values = apHttp.responseText.split(',');
		document.getElementById('appliances_total').innerHTML = values[0];
		document.getElementById('appliances_active').innerHTML = values[1];
        }
    };
    apHttp.send(null);
}
}


function get_resources() {
reHttp = null;
reHttp = new Request();

if (reHttp) {
    reHttp.open("GET", "server/resource/resource-xmlhttprequest.php?tmp="+Math.random(), true);
    reHttp.onreadystatechange = function () {
        if (reHttp.readyState == 4) {
		values = reHttp.responseText.split(',');
		document.getElementById('resources_total').innerHTML = values[0];
		document.getElementById('resources_active').innerHTML = values[1];
		document.getElementById('resources_off').innerHTML = values[2];
		document.getElementById('resources_error').innerHTML = values[3];
        }
    };
    reHttp.send(null);
}
} 
