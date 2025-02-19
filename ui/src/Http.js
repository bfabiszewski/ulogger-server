/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import HttpError from './HttpError';

export default class Http {

  /**
   * Perform POST HTTP request
   * @alias request
   */
  static post(url, data, options) {
    const params = options || {};
    params.method = 'POST';
    return this.request(url, data, params);
  }

  /**
   * Perform PUT HTTP request
   * @alias request
   */
  static put(url, data, options) {
    const params = options || {};
    params.method = 'PUT';
    return this.request(url, data, params);
  }

  /**
   * Perform GET HTTP request
   * @alias request
   */
  static get(url, data, options) {
    const params = options || {};
    params.method = 'GET';
    return this.request(url, data, params);
  }

  /**
   * Perform DELETE HTTP request
   * @alias request
   */
  static delete(url, data, options) {
    const params = options || {};
    params.method = 'DELETE';
    return this.request(url, data, params);
  }

  /**
   * Perform HTTP request
   * @param {string} url Request URL
   * @param {Object} [data] Optional request parameters: key/value pairs or form element
   * @param {Object} [options] Optional options
   * @param {string} [options.method='GET'] Optional query method, default 'GET'
   * @return {Promise<Object, Error>}
   */
  static request(url, data, options) {
    data = data || {};
    options = options || {};
    const method = options.method || 'GET';

    if (data instanceof HTMLFormElement) {
      data = new FormData(data);
    }
    const init = {};
    init.method = method;
    init.headers = new Headers();
    if (method === 'POST' || method === 'PUT') {
      if (data instanceof FormData) {
        init.body = data;
      } else {
        init.headers.append('Content-Type', 'application/json');
        init.body = JSON.stringify(data);
      }
    } else if (method === 'GET') {
      const query = this.dataToQueryString(data);
      url += query.length ? `?${query}` : '';
    }
    return fetch(url, init).then((response) => {
      const statusClass = Math.trunc(response.status / 100);
      const contentType = response.headers.get('Content-Type');

      return response.text().then((bodyText) => {
        let parsedBody;

        if (contentType && contentType.includes('application/json')) {
          try {
            parsedBody = JSON.parse(bodyText);
          } catch (error) {
            throw new HttpError(error.message, response.status);
          }
        } else {
          parsedBody = bodyText;
        }

        if (statusClass === Http.CLASS_SUCCESS) {
          return Promise.resolve(parsedBody);
        }
        let errorMessage = response.statusText;
        if (parsedBody.error && parsedBody.message) {
          errorMessage = parsedBody.message;
        }
        throw new HttpError(errorMessage, response.status);

      }).catch((error) => {
        if (error instanceof HttpError) {
          return Promise.reject(error);
        }
        return Promise.reject(new HttpError(error.message, response.status));
      });
    });
  }

  /**
   * @param {Object} data
   * @return {string}
   */
  static dataToQueryString(data) {
    if (data instanceof FormData) {
      return new URLSearchParams(data).toString();
    }
    const params = [];
    for (const key in data) {
      if (data.hasOwnProperty(key)) {
        if (Array.isArray(data[key])) {
          for (const value of data[key]) {
            params.push(`${key}[]=${this.encodeValue(value)}`);
          }
        } else {
          params.push(`${key}=${this.encodeValue(data[key])}`);
        }
      }
    }
    const query = params.join('&');
    return query.replace(/%20/g, '+');
  }

  static encodeValue(value) {
    if (typeof value === 'object') {
      value = JSON.stringify(value);
    }
    return encodeURIComponent(value);
  }
}

Http.CLASS_INFORM = 1;
Http.CLASS_SUCCESS = 2;
Http.CLASS_REDIRECT = 3;
Http.CLASS_ERROR_CLIENT = 4;
Http.CLASS_ERROR_SERVER = 5;

Http.ERROR_NOT_AUTHORIZED = 401;
Http.ERROR_FORBIDDEN = 403;
Http.ERROR_NOT_FOUND = 404;
Http.ERROR_CONFLICT = 409;
