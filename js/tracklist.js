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
      this.binder.addEventListener(uEvent.CONFIG, this);
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
    if (event.type === 'change') {
      config.showLatest = false;
    }
    super.handleEvent(event, data);
    if (event.type === uEvent.EXPORT) {
      this.current.export(data);
    } else if (event.type === uEvent.IMPORT) {
      this.import(data).catch((msg) => alert(`${lang.strings['actionfailure']}\n${msg}`));
    } else if (event.type === uEvent.CONFIG && data === 'showLatest') {
      if (config.showLatest) {
        this.fetchLatest().catch((msg) => alert(`${lang.strings['actionfailure']}\n${msg}`));
      } else {
        this.fetchTrack();
      }
    }
  }

  /**
   * @param {HTMLFormElement} form
   * @return {Promise<void>}
   */
  import(form) {
    return uAjax.post('utils/import.php', form,
      {
        // loader: ui.importTitle
      })
      .then((xml) => {
      const root = xml.getElementsByTagName('root');
      const trackCnt = uUtils.getNodeAsInt(root[0], 'trackcnt');
      if (trackCnt > 1) {
        alert(uUtils.sprintf(lang.strings['imultiple'], trackCnt));
      }
      const trackId = uUtils.getNodeAsInt(root[0], 'trackid');
      return this.fetch().then(() => this.select(trackId));
    }).catch((msg) => alert(`${lang.strings['actionfailure']}\n${msg}`));
  }

  /**
   * Fetch tracks for current user
   * @return {Promise<Document, string>}
   */
  fetch() {
    return uAjax.get('utils/gettracks.php',
      {
        userid: uLogger.userList.current.id
      },
      {
        // loader: ui.trackTitle
      }).then((xml) => {
      this.clear();
      return this.fromXml(xml.getElementsByTagName('track'), 'trackid', 'trackname');
    });
  }

  /**
   * Fetch track with latest position for current user
   * @throws
   * @return {Promise<Document, string>}
   */
  fetchLatest() {
    return uAjax.get('utils/getpositions.php', {
      userid: uLogger.userList.current.id,
      last: 1
    }, {
      // loader: ui.trackTitle
    }).then((xml) => {
      const xmlPos = xml.getElementsByTagName('position');
      if (xmlPos.length === 1) {
        const position = uPosition.fromXml(xmlPos[0]);
        if (this.has(position.trackid)) {
          this.select(position.trackid, true);
          this.current.fromXml(xml, false);
          this.current.onlyLatest = true;
          return this.current.render();
        }
        // tracklist needs update
        return this.fetch().fetchLatest();
      }
      return false;
    });
  }

  /**
   * @override
   */
  onChange() {
    this.fetchTrack();
  }

  fetchTrack() {
    if (this.current) {
      this.current.fetch()
        .catch((msg) => alert(`${lang.strings['actionfailure']}\n${msg}`));
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
