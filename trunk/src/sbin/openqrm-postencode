#!/usr/bin/env python
#
# This file is part of openQRM.
#
# openQRM is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License version 2
# as published by the Free Software Foundation.
#
# openQRM is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with openQRM.  If not, see <http://www.gnu.org/licenses/>.
#
# Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
#
import sys, urllib, base64
input_filename = sys.argv[1]
postwad_filename = input_filename + ".post"
datawad = base64.encodestring(file(input_filename, "rb").read())
postwad = urllib.urlencode({"filedata":datawad, "filename":input_filename})
file(postwad_filename, "wb").write(postwad)
print postwad_filename
