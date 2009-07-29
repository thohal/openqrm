<?php
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
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