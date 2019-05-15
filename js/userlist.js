import { auth, config, lang } from './constants.js';
import UserDialog from './userdialog.js';
import uList from './list.js';
import { uLogger } from './ulogger.js';
import uUser from './user.js';

/**
 * @class UserList
 * @extends {uList<uUser>}
 */
export default class UserList extends uList {

  /**
   * @param {string} selector
   * @param {uBinder} binder
   */
  constructor(selector, binder) {
    super(selector, binder, uUser);
    super.hasHead = true;
    super.allValue = `- ${lang.strings['allusers']} -`;
    super.headValue = lang.strings['suser'];
  }

  /**
   * @override
   */
  onChange() {
    if (this.isSelectedAllOption) {
      // clearOptions(ui.trackSelect);
      // loadLastPositionAllUsers();
    } else if (config.showLatest) {
      uLogger.trackList.fetchLatest()
        .catch((msg) => alert(`${lang.strings['actionfailure']}\n${msg}`));
    } else {
      uLogger.trackList.fetch()
        .catch((msg) => alert(`${lang.strings['actionfailure']}\n${msg}`));
    }
  }

  /**
   * @override
   */
  onEdit() {
    if (this.isSelectedAllOption) {
      return;
    }
    if (this.current) {
      if (this.current.login === auth.user.login) {
        alert(lang.strings['selfeditwarn']);
        return;
      }
      this.editUser();
    }
  }

  /**
   * @param {UserDialog=} modal
   */
  editUser(modal) {
    const dialog = modal || new UserDialog('edit', this.current);
    dialog.show()
      .then((result) => {
        switch (result.action) {
          case 'update':
            // currently only password
            this.current.password = result.data.password;
            return this.current.update('update');
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
          this.editUser(dialog);
      });
  }

  /**
   * @override
   */
  onAdd() {
    this.addUser();
  }

  /**
   * @param {UserDialog=} modal
   */
  addUser(modal) {
    const dialog = modal || new UserDialog('add');
    dialog.show()
      .then((result) => {
        const newUser = new uUser(0, result.data.login);
        newUser.password = result.data.password;
        return newUser.update('add')
      })
      .then((user) => {
        alert(lang.strings['actionsuccess']);
        this.add(user);
        dialog.hide();
      })
      .catch((msg) => {
        alert(`${lang.strings['actionfailure']}\n${msg}`);
        this.addUser(dialog);
      });
  }

}
