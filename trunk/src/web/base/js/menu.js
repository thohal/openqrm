// PHP Layers Menu 3.2.0-rc (C) 2001-2004 Marco Pratesi - http://www.marcopratesi.it/
DOM = (document.getElementById) ? 1 : 0;
NS4 = (document.layers) ? 1 : 0;
// We need to explicitly detect Konqueror
// because Konqueror 3 sets IE = 1 ... AAAAAAAAAARGHHH!!!
Konqueror = (navigator.userAgent.indexOf('Konqueror') > -1) ? 1 : 0;
// We need to detect Konqueror 2.2 as it does not handle the window.onresize event
Konqueror22 = (navigator.userAgent.indexOf('Konqueror 2.2') > -1 || navigator.userAgent.indexOf('Konqueror/2.2') > -1) ? 1 : 0;
Konqueror30 =
	(
		navigator.userAgent.indexOf('Konqueror 3.0') > -1
		|| navigator.userAgent.indexOf('Konqueror/3.0') > -1
		|| navigator.userAgent.indexOf('Konqueror 3;') > -1
		|| navigator.userAgent.indexOf('Konqueror/3;') > -1
		|| navigator.userAgent.indexOf('Konqueror 3)') > -1
		|| navigator.userAgent.indexOf('Konqueror/3)') > -1
	)
	? 1 : 0;
Konqueror31 = (navigator.userAgent.indexOf('Konqueror 3.1') > -1 || navigator.userAgent.indexOf('Konqueror/3.1') > -1) ? 1 : 0;
// We need to detect Konqueror 3.2 and 3.3 as they are affected by the see-through effect only for 2 form elements
Konqueror32 = (navigator.userAgent.indexOf('Konqueror 3.2') > -1 || navigator.userAgent.indexOf('Konqueror/3.2') > -1) ? 1 : 0;
Konqueror33 = (navigator.userAgent.indexOf('Konqueror 3.3') > -1 || navigator.userAgent.indexOf('Konqueror/3.3') > -1) ? 1 : 0;
Opera = (navigator.userAgent.indexOf('Opera') > -1) ? 1 : 0;
Opera5 = (navigator.userAgent.indexOf('Opera 5') > -1 || navigator.userAgent.indexOf('Opera/5') > -1) ? 1 : 0;
Opera6 = (navigator.userAgent.indexOf('Opera 6') > -1 || navigator.userAgent.indexOf('Opera/6') > -1) ? 1 : 0;
Opera56 = Opera5 || Opera6;
IE = (navigator.userAgent.indexOf('MSIE') > -1) ? 1 : 0;
IE = IE && !Opera;
IE5 = IE && DOM;
IE4 = (document.all) ? 1 : 0;
IE4 = IE4 && IE && !DOM;


function setLMCookie(name, value)
{
	document.cookie = name + '=' + value + ';path=/';
}

function getLMCookie(name)
{
	foobar = document.cookie.split(name + '=');
	if (foobar.length < 2) {
		return null;
	}
	tempString = foobar[1];
	if (tempString.indexOf(';') == -1) {
		return tempString;
	}
	yafoobar = tempString.split(';');
	return yafoobar[0];
}

function parseExpandString()
{
	expandString = getLMCookie('phplm_expand');
	phplm_expand = new Array();
	if (expandString) {
		expanded = expandString.split('|');
		for (i=0; i<expanded.length-1; i++) {
			phplm_expand[expanded[i]] = 1;
		}
	}
}

function parseCollapseString()
{
	collapseString = getLMCookie('phplm_collapse');
	phplm_collapse = new Array();
	if (collapseString) {
		collapsed = collapseString.split('|');
		for (i=0; i<collapsed.length-1; i++) {
			phplm_collapse[collapsed[i]] = 1;
		}
	}
}

parseExpandString();
parseCollapseString();

function saveExpandString()
{
	expandString = '';
	for (var val in phplm_expand) {
		if (phplm_expand[val] == 1) {
			expandString += val + '|';
		}
	}
	setLMCookie('phplm_expand', expandString);
}

function saveCollapseString()
{
	collapseString = '';

	for (var val in phplm_collapse) {
		if (phplm_collapse[val] == 1) {
			expandString += val + '|';
		}
	}

	setLMCookie('phplm_collapse', collapseString);
}
