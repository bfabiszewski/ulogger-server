/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import { config, lang } from '../src/Initializer.js';
import Dialog from '../src/Dialog.js';
import Observer from '../src/Observer.js';
import PositionDialogModel from '../src/models/PositionDialogModel.js';
import State from '../src/State.js';
import TrackFactory from './helpers/trackfactory.js';

describe('PositionDialogModel tests', () => {

  let dm;
  let positionIndex;
  let track;
  const comment = 'comment';

  beforeEach(() => {
    config.reinitialize();
    lang.init(config);
    spyOn(lang, '_').and.returnValue('{placeholder}');
    positionIndex = 0;
    const state = new State();
    track = TrackFactory.getTrack();
    track.positions[positionIndex].comment = comment;
    state.currentTrack = track;
    dm = new PositionDialogModel(state, positionIndex);
    spyOn(track.positions[positionIndex], 'save').and.resolveTo();
    spyOn(track.positions[positionIndex], 'delete').and.resolveTo();
    spyOn(track.positions[positionIndex], 'imageAdd').and.resolveTo();
    spyOn(track.positions[positionIndex], 'imageDelete').and.resolveTo();
    spyOn(Observer, 'forceUpdate');
  });

  afterEach(() => {
    document.body.innerHTML = '';
    Observer.unobserveAll(lang);
  });

  it('should create instance', () => {
    expect(dm).toBeDefined();
    expect(dm.position).toBe(track.positions[positionIndex]);
  });

  it('should show dialog with position comment in textarea', () => {
    // when
    dm.init();
    // then
    expect(document.querySelector('#modal')).toBeInstanceOf(HTMLDivElement);
    expect(dm.dialog.element.querySelector("[data-bind='onPositionUpdate']")).toBeInstanceOf(HTMLButtonElement);
    expect(dm.dialog.element.querySelector("[data-bind='comment']").value).toEqual(comment);
  });

  it('should show dialog with position image preview', () => {
    // when
    track.positions[positionIndex].hasImage = true;
    dm.init();
    // then
    expect(document.querySelector('#modal')).toBeInstanceOf(HTMLDivElement);
    expect(dm.dialog.element.querySelector("[data-bind='onPositionUpdate']")).toBeInstanceOf(HTMLButtonElement);
    expect(dm.dialog.element.querySelector("[data-bind='imagePreview']").src).not.toBe('');
  });

  it('should show dialog without position image preview', () => {
    // when
    track.positions[positionIndex].hasImage = false;
    dm.init();
    // then
    expect(document.querySelector('#modal')).toBeInstanceOf(HTMLDivElement);
    expect(dm.dialog.element.querySelector("[data-bind='onPositionUpdate']")).toBeInstanceOf(HTMLButtonElement);
    expect(dm.dialog.element.querySelector("[data-bind='imagePreview']").src).toBe('');
  });

  it('should hide edit dialog on negative button clicked', (done) => {
    // given
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onCancel']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should update position comment and hide edit dialog on positive button clicked', (done) => {
    // given
    spyOn(dm, 'validate').and.returnValue(true);
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onPositionUpdate']");
    const commentEl = dm.dialog.element.querySelector("[data-bind='comment']");
    const newComment = 'newcomment';
    // when
    commentEl.value = newComment;
    commentEl.dispatchEvent(new Event('change'));
    button.click();
    // then
    setTimeout(() => {
      expect(track.positions[positionIndex].save).toHaveBeenCalledTimes(1);
      expect(track.positions[positionIndex].comment).toBe(newComment);
      expect(document.querySelector('#modal')).toBe(null);
      expect(Observer.forceUpdate).toHaveBeenCalledWith(dm.state, 'currentTrack');
      done();
    }, 100);
  });

  it('should show confirmation dialog on position delete button click', (done) => {
    // given
    spyOn(Dialog, 'isConfirmed').and.returnValue(false);
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onPositionDelete']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(Dialog.isConfirmed).toHaveBeenCalledTimes(1);
      done();
    }, 100);
  });

  it('should delete user and hide dialog on confirmation dialog accepted', (done) => {
    // given
    spyOn(Dialog, 'isConfirmed').and.returnValue(true);
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onPositionDelete']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(dm.position.delete).toHaveBeenCalledTimes(1);
      expect(dm.state.currentTrack.length).toBe(1);
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should positively validate form, check comment was modified', (done) => {
    // given
    dm.model.comment = track.positions[positionIndex].comment + '1234';
    // when
    const result = dm.validate();
    // then
    setTimeout(() => {
      expect(result).toBeTrue();
      done();
    }, 100);
  });

  it('should negatively validate form, check comment was not modified', (done) => {
    // given
    dm.model.comment = track.positions[positionIndex].comment;
    // when
    const result = dm.validate();
    // then
    setTimeout(() => {
      expect(result).toBeFalse();
      done();
    }, 100);
  });

});

