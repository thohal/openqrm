<?php
/**
 * @package htmlobjects
 *
 */

/**
 * Preloader
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */

class htmlobject_preloader
{
	function start($string = 'Loading ...') {
		echo '
		<div id="Loadbar" style="margin:40px 0 0 40px;display:none">
		<strong>'.$string.'</strong>
		</div>
		<script type="text/javascript">
		document.getElementById("Loadbar").style.display = "block";
		</script>
		';
		flush();
	}

	function stop() {
		echo '
		<script type="text/javascript">
			document.getElementById("Loadbar").style.display = "none";
		</script>
		';
	}
}
