/* μlogger
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

/**
 * @typedef uLogger.config
 * @memberOf uLogger
 * @type {Object}
 * @property {number} interval
 * @property {string} units
 * @property {string} mapapi
 * @property {?string} gkey
 * @property {Object.<string, string>} ol_layers
 * @property {number} init_latitude
 * @property {number} init_longitude
 * @property {boolean} admin
 * @property {?string} auth
 * @property {RegExp} pass_regex
 * @property {number} strokeWeight
 * @property {string} strokeColor
 * @property {number} strokeOpacity
 */

/**
 * @typedef uLogger.lang
 * @memberOf uLogger
 * @type {Object}
 * @property {Object.<string, string>} strings
 */

/**
 * @typedef {Object} uLogger.mapAPI.api
 * @memberOf uLogger
 * @type {Object}
 * @property {string} name
 * @property {function} init
 * @property {function} cleanup
 * @property {function(HTMLCollection, boolean)} displayTrack
 * @property {function} clearMap
 * @property {function(uLogger.Position, number, number)} setMarker
 * @property {function} addChartEvent
 * @property {function} getBounds
 * @property {function} zoomToExtent
 * @property {function} zoomToBounds
 * @property {function} updateSize
 */
