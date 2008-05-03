 function Request() {
var arg = null;
// Mozilla, Opera, Safari sowie Internet Explorer 7
if (typeof XMLHttpRequest != "undefined") {
    return new XMLHttpRequest();
	arg = "1";
	//alert('XMLHttpRequest');
}
if (!arg) {
    // Internet Explorer 6 und �lter
    try {
        return new ActiveXObject("Msxml2.XMLHTTP");
    } catch(e) {
        try {
            return new ActiveXObject("Microsoft.XMLHTTP");
        } catch(e) {
            return null;
        }
    }
}
//this.object = xmlHttp;

}


function DeleteToken() {
xmlHttp = null;
xmlHttp = new Request();
		
if (xmlHttp) {
    xmlHttp.open("GET", "token.php?id=100&action=delete&tmp="+Math.random(), false);
    xmlHttp.onreadystatechange = function () {
       if (xmlHttp.readyState == 4) {
			try {
				//alert(xmlHttp.responseText);
			}catch(e) {
				//alert(e);
			}

		}
    };
    xmlHttp.send(null);

}
}

function CreateToken() {
xmlHttp = null;
xmlHttp = new Request();

if (xmlHttp) {
    xmlHttp.open("GET", "token.php?id=100&action=create&tmp="+Math.random(), false);
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 4) {
			if (xmlHttp.responseText == "created") { window.onunload = DeleteToken; }
			else { 
			Check = confirm(xmlHttp.responseText + "\nTo return to Messagelist press \"ok\"");
				if (Check == true) {
							DeleteToken();
							CreateToken();
				}
				if (Check == false) {
					history.back();
				}
			}
        }
    };
    xmlHttp.send(null);
}
}
