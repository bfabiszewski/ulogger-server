/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Config from './Config.js';
import Http from './Http.js';
import Locale from './Locale.js';
import Session from './Session.js';

/**
 * @Class Initializer
 * @property {Session} auth
 * @property {Config} config
 * @property {Locale} lang
 */
export class Initializer {

  constructor() {
    this.auth = new Session();
    this.config = new Config();
    this.lang = new Locale();
  }

  /**
   * @return {Promise<void, Error>}
   */
  initialize() {
    const authPromise = Http.get('api/session');
    const configPromise = Http.get('api/config');
    const langPromise = Http.get('api/locales');
    return Promise.allSettled([ authPromise, configPromise, langPromise ])
      .then((result) => {
        if (result[1].status === 'rejected' || result[2].status === 'rejected' ||
          (result[0].status === 'rejected' && result[0].reason.status !== 401)) {
          let reason = '';
          if (result[0].reason) {
            reason += result[0].reason;
          }
          if (result[1].reason) {
            reason += result[1].reason;
          }
          if (result[2].reason) {
            reason += result[2].reason;
          }
          throw new Error(`Corrupted initialization data (${reason})`);
        }
        if (result[0].status === 'fulfilled') {
          this.auth.load(result[0].value);
        }
        this.config.load(result[1].value);
        this.lang.init(this.config, result[2].value);
      });
  }

  static waitForDom() {
    return new Promise((resolve) => {
      if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(resolve, 1);
      } else {
        document.addEventListener('DOMContentLoaded', resolve);
      }
    });
  }
}

export const initializer = new Initializer();
export const config = initializer.config;
export const lang = initializer.lang;
export const auth = initializer.auth;
