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

import { auth, config, lang } from './constants.js';
import TrackDialog from './trackdialog.js';
import uAjax from './ajax.js';
import uEvent from './event.js';
import uList from './list.js';
import { uLogger } from './ulogger.js';
import uPosition from './position.js';
import uTrack from './track.js';
import uUtils from './utils.js';

/**
 * @class TrackList
 * @extends {uList<uTrack>}
 */
export default class TrackList extends uList {

  /**
   * @param {string} selector
   * @param {uBinder} binder
   */
  constructor(selector, binder) {
    super(selector, binder, uTrack);
    if (binder) {
      this.binder.addEventListener(uEvent.EXPORT, this);
      this.binder.addEventListener(uEvent.IMPORT, this);
    }
  }

  /**
   * @override
   * @param {uTrack} row
   */
  // eslint-disable-next-line class-methods-use-this
  updateDataRow(row) {
    row.user = uLogger.userList.current;
  }

  /**
   * @override
   * @param {(Event|uEvent)} event
   * @param {*=} data
   */
  handleEvent(event, data) {
    if (event.type === uEvent.CHANGE) {
      config.showLatest = false;
    }
    super.handleEvent(event, data);
    if (event.type === uEvent.EXPORT) {
      this.current.export(data);
    } else if (event.type === uEvent.IMPORT) {
      this.import(data).catch((msg) => alert(`${lang.strings['actionfailure']}\n${msg}`));
    }
  }

  /**
   * @param {HTMLFormElement} form
   * @return {Promise<void>}
   */
  import(form) {
    this.emit(true, 'import');
    return uAjax.post('utils/import.php', form)
      .then((xml) => {
        const root = xml.getElementsByTagName('root');
        const trackCnt = uUtils.getNodeAsInt(root[0], 'trackcnt');
        if (trackCnt > 1) {
          alert(uUtils.sprintf(lang.strings['imultiple'], trackCnt));
        }
        const trackId = uUtils.getNodeAsInt(root[0], 'trackid');
        this.emit(false, 'import');
        return this.fetch().then(() => this.select(trackId));
    }).catch((msg) => {
        this.emit(false, 'import');
        alert(`${lang.strings['actionfailure']}\n${msg}`);
      });
  }

  emit(on, action) {
    this.binder.dispatchEvent(uEvent.LOADER, { on: on, action: action });
  }

  /**
   * Fetch tracks for current user
   * @throws
   * @return {Promise<Document, string>}
   */
  fetch() {
    this.emit(true, 'track');
    return uAjax.get('utils/gettracks.php',
      {
        userid: uLogger.userList.current.id
      })
      .then((xml) => {
        this.clear();
        this.fromXml(xml.getElementsByTagName('track'), 'trackid', 'trackname');
        this.emit(false, 'track');
        return xml;
    }).catch((msg) => {
        this.emit(false, 'track');
        alert(`${lang.strings['actionfailure']}\n${msg}`);
      });
  }

  /**
   * Fetch track with latest position for current user
   * @throws
   * @return {Promise<Document, string>}
   */
  fetchLatest() {
    this.emit(true, 'track');
    const data = {
      last: 1
    };
    const allUsers = uLogger.userList.isSelectedAllOption;
    if (!allUsers) {
      data.userid = uLogger.userList.current.id;
    }
    return uAjax.get('utils/getpositions.php', data).then((xml) => {
      if (!allUsers) {
        const xmlPos = xml.getElementsByTagName('position');
        // single user
        if (xmlPos.length === 1) {
          const position = uPosition.fromXml(xmlPos[0]);
          if (this.has(position.trackid)) {
            this.select(position.trackid, true);
            this.current.fromXml(xml, false);
            this.current.onlyLatest = true;
            this.current.render();
          } else {
            // tracklist needs update
            return this.fetch().then(() => this.fetchLatest());
          }
        }
      } else {
        // all users
        this.clear();
        const track = new uTrack(0, '', null);
        track.binder = this.binder;
        track.continuous = false;
        track.fromXml(xml, false);
        this.add(track);
        this.select(0, true);
        this.current.render();
      }
      this.emit(false, 'track');
      return xml;
    }).catch((msg) => {
      this.emit(false, 'track');
      alert(`${lang.strings['actionfailure']}\n${msg}`);
    });
  }

  /**
   * @override
   */
  onChange() {
    if (!config.showLatest) {
      this.fetchTrack();
    }
  }

  /**
   * Fetch and render track
   */
  fetchTrack() {
    if (this.current) {
      this.emit(true, 'track');
      this.current.fetch()
        .then(() => this.emit(false, 'track'))
        .catch((msg) => {
          this.emit(false, 'track');
          alert(`${lang.strings['actionfailure']}\n${msg}`);
        });
    }
  }

  /**
   * @override
   */
  onEdit() {
    if (this.current) {
      if (this.current.user.login !== auth.user.login && !auth.isAdmin) {
        alert(lang.strings['owntrackswarn']);
        return;
      }
      this.editTrack();
    }
  }

  /**
   * @param {TrackDialog=} modal
   */
  editTrack(modal) {
    const dialog = modal || new TrackDialog(this.current);
    dialog.show()
      .then((result) => {
        switch (result.action) {
          case 'update':
            this.current.name = result.data.name;
            return this.current.update('update').then(() => this.render());
          case 'delete':
            return this.current.update('delete').then(() => this.remove(this.current.id));
          default:
            break;
        }
        throw new Error();
      })
      .then(() => {
        alert(lang.strings['actionsuccess']);
        dialog.hide();
      })
      .catch((msg) => {
        alert(`${lang.strings['actionfailure']}\n${msg}`);
        this.editTrack(dialog);
      });
  }

  /**
   * @override
   */
// eslint-disable-next-line no-empty-function,class-methods-use-this
  onAdd() {
  }
}
