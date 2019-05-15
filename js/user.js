import uAjax from './ajax.js';
import uData from './data.js';
import uUtils from './utils.js';

/**
 * @class uUser
 * @extends {uData}
 * @property {number} id
 * @property {string} login
 * @property {string} [password]
 */
export default class uUser extends uData {
  /**
   * @param {number} id
   * @param {string} login
   */
  constructor(id, login) {
    super(id, login, 'id', 'login');
  }

  /**
   *
   * @param {string} action
   * @return {Promise<uUser>}
   */
  update(action) {
    const pass = this.password;
    // don't store password in class property
    delete this.password;
    return uAjax.post('utils/handleuser.php',
      {
        action: action,
        login: this.login,
        pass: pass
      }).then((xml) => {
        if (action === 'add') {
          this.id = uUtils.getNodeAsInt(xml, 'userid');
        }
        return this;
    });
  }

  /**
   * @param {string} password
   * @param {string} oldPassword
   * @return {Promise<void>}
   */
  changePass(password, oldPassword) {
    return uAjax.post('utils/changepass.php',
      {
        login: this.login,
        pass: password,
        oldpass: oldPassword
      });
  }
}
