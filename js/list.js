import uData from './data.js';
import uEvent from './event.js';
import uUtils from './utils.js';

/**
 * @class uList
 * @template T
 */
export default class uList {

  /**
   * @param {string} selector
   * @param {uBinder} binder
   * @param {Class<T>} type
   * @template T
   */
  constructor(selector, binder, type) {
    /** @type {T[]} */
    this.data = [];
    /** @type {uBinder} */
    this.binder = binder;
    /** @type {boolean} */
    this.showAllOption = false;
    /** @type {boolean} */
    this.hasHead = false;
    this.headValue = '';
    this.allValue = '';
    /** @type {(T|uData)} */
    this.T = type || uData;
    /** @type {HTMLSelectElement} */
    this.domElement = document.querySelector(selector);
    if (this.domElement) {
      this.domElement.addEventListener('change', this, false);
    }
    if (this.binder) {
      this.binder.addEventListener(uEvent.ADD, this);
      this.binder.addEventListener(uEvent.EDIT, this);
    }

    /** @type {string} */
    this.selectedId = '';
    this.fromDom();
  }

  /**
   * @return {(T|null)}
   * @template T
   */
  get current() {
    const i = parseInt(this.selectedId);
    if (!isNaN(i)) {
      return this.data.find((item) => item.key === i);
    }
    return null;
  }

  /**
   * @return {boolean}
   */
  get isSelectedAllOption() {
    return this.selectedId === 'all';
  }

  /**
   * @param {number} id
   * @param {boolean=} skipUpdate
   */
  select(id, skipUpdate) {
    this.selectedId = id.toString();
    this.render();
    if (!skipUpdate) {
      this.onChange();
    }
  }

  clear() {
    this.domElement.options.length = 0;
    this.data.length = 0;
    this.selectedId = '';
  }

  /**
   * Get list from XML structure
   * @param {Element|Document} xml
   * @param {string} key Name of key node
   * @param {string} value Name of value node
   */
  fromXml(xml, key, value) {
    if (!xml) {
      return;
    }
    for (const item of xml) {
      const row = new this.T(uUtils.getNodeAsInt(item, key), uUtils.getNode(item, value));
      this.updateDataRow(row);
      row.binder = this.binder;
      this.data.push(row);
    }
    if (this.data.length) {
      this.selectedId = this.data[0].key.toString();
    }
    /** @todo set defaults ?? */
    // var defaultTrack = tid || getNodeAsInt(tracks[0], 'trackid');
    // var defaultUser = uid || ns.userId;
    this.render();
    this.onChange();
  }

  /**
   * Initialize list from DOM select element options
   */
  fromDom() {
    if (!this.domElement) {
      return;
    }
    for (const option of this.domElement) {
      if (option.value === 'all') {
        this.showAllOption = true;
      } else if (!option.disabled) {
        const row = new this.T(parseInt(option.value), option.innerText);
        this.updateDataRow(row);
        row.binder = this.binder;
        this.data.push(row);
      }
      if (option.selected) {
        this.selectedId = option.value;
      }
    }
  }

  /**
   * @param {(Event|uEvent)} event
   * @param {*=} eventData
   */
  handleEvent(event, eventData) {
    if (event.type === 'change') {
      this.selectedId = this.domElement.options[this.domElement.selectedIndex].value;
      this.onChange();
    } else if (event.type === uEvent.EDIT && this.domElement === eventData) {
      this.onEdit();
    } else if (event.type === uEvent.ADD && this.domElement === eventData) {
      this.onAdd();
    }
  }

  // /**
  //  * @param {T[]} data
  //  */
  // set list(data) {
  //   this.data = data;
  // }

  /**
   * Add item
   * @param {T} item
   * @template T
   */
  add(item) {
    this.data.push(item);
    this.render();
  }

  /**
   * @param {number} id
   * @return {boolean}
   */
  has(id) {
    return this.data.findIndex((o) => o.key === id) !== -1;
  }

  /**
   * Remove item
   * @param {number} id
   */
  remove(id) {
    const currentId = this.current.key;
    this.data.splice(this.data.findIndex((o) => o.key === id), 1);
    if (id === currentId) {
      if (this.data.length) {
        this.selectedId = this.data[0].key.toString();
      } else {
        this.selectedId = '';
      }
      this.onChange();
    }
    this.render();
  }

  render() {
    this.domElement.options.length = 0;
    if (this.hasHead) {
      const head = new Option(this.headValue, '0', true, this.selectedId === '0');
      head.disabled = true;
      this.domElement.options.add(head);
    }
    if (this.showAllOption) {
      this.domElement.options.add(new Option(this.allValue, 'all'));
    }
    for (const item of this.data) {
      this.domElement.options.add(new Option(item.value, item.key.toString(), false, item.key.toString() === this.selectedId));
    }
  }


  /**
   * @param {T} row
   * @template T
   */
  // eslint-disable-next-line no-unused-vars,no-empty-function,class-methods-use-this
  updateDataRow(row) {
  }

  /**
   * @abstract
   */
  // eslint-disable-next-line no-unused-vars,no-empty-function,class-methods-use-this
  onChange() {
  }

  /**
   * @abstract
   */
  // eslint-disable-next-line no-unused-vars,no-empty-function,class-methods-use-this
  onEdit() {
  }

  /**
   * @abstract
   */
  // eslint-disable-next-line no-unused-vars,no-empty-function,class-methods-use-this
  onAdd() {
  }
}
