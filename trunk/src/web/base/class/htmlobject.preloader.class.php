<?php
/**
 * @package Htmlobjects
 */


/**
 * @package Htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @version 1.0
 */
class htmlobject_preloader extends htmlobject
{
	function start($string = 'Loading ...') {
		echo '
		<div id="Loadbar" style="margin:40px 0 0 40px;display:none">
		<strong>'.$string.'</strong>
		</div>
		<script>
		document.getElementById("Loadbar").style.display = "block";
		</script>
		';
		flush();
	}

	function stop() {
		echo '
		<script>
			document.getElementById("Loadbar").style.display = "none";
		</script>
		';
	}
}
