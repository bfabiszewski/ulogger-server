/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

const baseUrl = '/base/test/fixtures/';

export default class Fixture {

  static load(url) {
    return fetch(baseUrl + url)
      .then((response) => response.text())
      .then((fixture) => {
        document.body.insertAdjacentHTML('afterbegin', fixture);
      });
  }

  static clear() {
    document.body.innerHTML = '';
  }
}
