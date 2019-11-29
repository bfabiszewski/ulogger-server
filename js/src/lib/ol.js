/*
 * μlogger
 *
 * Copyright(C) 2019 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

import { Control, Rotate, ScaleLine, Zoom, ZoomToExtent } from 'ol/control';
import { LineString, Point } from 'ol/geom';
import { fromLonLat, toLonLat } from 'ol/proj';
import Feature from 'ol/Feature';
import Icon from 'ol/style/Icon';
import Map from 'ol/Map';
import OSM from 'ol/source/OSM';
import Overlay from 'ol/Overlay';
import Stroke from 'ol/style/Stroke';
import Style from 'ol/style/Style';
import TileLayer from 'ol/layer/Tile';
import Vector from 'ol/source/Vector';
import VectorLayer from 'ol/layer/Vector';
import View from 'ol/View';
import XYZ from 'ol/source/XYZ';

export { Feature, Map, Overlay, View };
export const control = { Control, Rotate, ScaleLine, Zoom, ZoomToExtent };
export const geom = { LineString, Point };
export const layer = { TileLayer, VectorLayer };
export const proj = { fromLonLat, toLonLat };
export const source = { OSM, Vector, XYZ };
export const style = { Icon, Stroke, Style };
