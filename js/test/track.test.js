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

import uPosition from '../src/position.js';
import uTrack from '../src/track.js';
import uUser from '../src/user.js';
import uUtils from '../src/utils.js';


describe('Track tests', () => {

  let track;
  let posId;
  let latitude;
  let longitude;
  let altitude;
  let speed;
  let bearing;
  let timestamp;
  let accuracy;
  let provider;
  let comment;
  let image;
  let username;
  let trackid;
  let trackname;
  let meters;
  let seconds;

  let jsonPosition;
  beforeEach(() => {
    const id = 1;
    const name = 'test';
    const user = new uUser(1, 'user');
    track = new uTrack(id, name, user);

    posId = 110286;
    latitude = 11.221871666666999;
    longitude = 22.018848333333001;
    altitude = -39;
    speed = 0;
    bearing = null;
    timestamp = 1564250017;
    accuracy = 9;
    provider = 'gps';
    comment = null;
    image = '134_5d3c8fa92ebac.jpg';
    username = 'test';
    trackid = 134;
    trackname = 'Test name';
    meters = 0;
    seconds = 0;

    jsonPosition = {
      id: posId,
      latitude: latitude,
      longitude: longitude,
      altitude: altitude,
      speed: speed,
      bearing: bearing,
      timestamp: timestamp,
      accuracy: accuracy,
      provider: provider,
      comment: comment,
      image: image,
      username: username,
      trackid: trackid,
      trackname: trackname,
      meters: meters,
      seconds: seconds
    };
  });

  describe('simple tests', () => {

    it('should throw error when creating uTrack instance without user parameter', () => {
      // given
      const id = 1;
      const name = 'test';
      // when
      // then
      expect(() => new uTrack(id, name)).toThrow(new Error('Invalid argument for track constructor'));
    });

    it('should create uTrack instance with user parameter', () => {
      // given
      const id = 1;
      const name = 'test';
      const user = new uUser(1, 'user');
      // when
      track = new uTrack(id, name, user);
      // then
      expect(track.id).toBe(id);
      expect(track.name).toBe(name);
      expect(track.user).toBe(user);
      expect(track.positions).toEqual([]);
      expect(track.plotData).toEqual([]);
      expect(track.maxId).toBe(0);
      expect(track.listValue).toBe(id.toString());
      expect(track.listText).toBe(name);
    });

    it('should set track name', () => {
      // given
      const newName = 'newName';
      // when
      track.setName(newName);
      // then
      expect(track.name).toBe(newName);
      expect(track.listText).toBe(newName);
    });

    it('should clear positions data', () => {
      // given
      track.positions.push(new uPosition());
      track.plotData.push({ x: 1, y: 2 });
      track.maxId = 1;
      // when
      track.clear();
      // then
      expect(track.positions).toEqual([]);
      expect(track.plotData).toEqual([]);
      expect(track.maxId).toBe(0);
    });

    it('should return positions length', () => {
      // given
      track.positions.push(new uPosition());
      // when
      const length = track.length;
      // then
      expect(length).toBe(1);
    });

    it('should return true when has positions', () => {
      // given
      track.positions.push(new uPosition());
      // when
      const result = track.hasPositions;
      // then
      expect(result).toBe(true);
    });

    it('should return false when does not have positions', () => {
      // given
      track.positions.length = 0;
      // when
      const result = track.hasPositions;
      // then
      expect(result).toBe(false);
    });

    it('should return true when has plot data', () => {
      // given
      track.plotData.push({ x: 1, y: 2 });
      // when
      const result = track.hasPlotData;
      // then
      expect(result).toBe(true);
    });

    it('should return false when does not have plot data', () => {
      // given
      track.plotData.length = 0;
      // when
      const result = track.hasPlotData;
      // then
      expect(result).toBe(false);
    });

    it('should be equal to other track with same id', () => {
      // given
      track.id = 1;
      const otherTrack = new uTrack(1, 'other', new uUser(2, 'user2'));
      // when
      const result = track.isEqualTo(otherTrack);
      // then
      expect(result).toBe(true);
    });

    it('should not be equal to other track with other id', () => {
      // given
      track.id = 1;
      const otherTrack = new uTrack(2, 'other', new uUser(2, 'user2'));
      // when
      const result = track.isEqualTo(otherTrack);
      // then
      expect(result).toBe(false);
    });

    it('should not be equal to null track', () => {
      // given
      track.id = 1;
      const otherTrack = null;
      // when
      const result = track.isEqualTo(otherTrack);
      // then
      expect(result).toBe(false);
    });

    it('should parse json object to track positions', () => {
      // when
      track.fromJson([ jsonPosition ]);
      // then
      expect(track.length).toBe(1);
      expect(track.plotData.length).toBe(1);
      expect(track.maxId).toBe(posId);
      const position = track.positions[0];

      expect(position.id).toBe(posId);
      expect(position.latitude).toBe(latitude);
      expect(position.longitude).toBe(longitude);
      expect(position.speed).toBe(speed);
      expect(position.bearing).toBe(bearing);
      expect(position.timestamp).toBe(timestamp);
      expect(position.accuracy).toBe(accuracy);
      expect(position.provider).toBe(provider);
      expect(position.comment).toBe(comment);
      expect(position.image).toBe(image);
      expect(position.username).toBe(username);
      expect(position.trackid).toBe(trackid);
      expect(position.trackname).toBe(trackname);
      expect(position.meters).toBe(meters);
      expect(position.seconds).toBe(seconds);
    });

    it('should replace track positions with new ones', () => {
      const position1 = { ...jsonPosition };
      position1.id = 100;
      track.fromJson([ position1 ]);
      // when
      track.fromJson([ jsonPosition ]);
      // then
      expect(track.length).toBe(1);
      expect(track.plotData.length).toBe(1);
      expect(track.maxId).toBe(posId);
      const position2 = track.positions[0];

      expect(position2.id).toBe(posId);
    });

    it('should append track positions with new ones', () => {
      const position1 = { ...jsonPosition };
      position1.id = 100;
      track.fromJson([ position1 ]);
      // when
      track.fromJson([ jsonPosition ], true);
      // then
      expect(track.length).toBe(2);
      expect(track.plotData.length).toBe(2);
      expect(track.maxId).toBe(Math.max(jsonPosition.id, position1.id));
      expect(track.positions[0].id).toBe(position1.id);
      expect(track.positions[1].id).toBe(jsonPosition.id);
      expect(track.positions[0].totalMeters).toBe(position1.meters);
      expect(track.positions[1].totalMeters).toBe(position1.meters + jsonPosition.meters);
      expect(track.positions[0].totalSeconds).toBe(position1.seconds);
      expect(track.positions[1].totalSeconds).toBe(position1.seconds + jsonPosition.seconds);
    });
  });

  describe('ajax tests', () => {
    const validListResponse = [ { 'id': 145, 'name': 'Track 1' }, { 'id': 144, 'name': 'Track 2' } ];
    const invalidListResponse = [ { 'name': 'Track 1' }, { 'id': 144, 'name': 'Track 2' } ];

    beforeEach(() => {
      spyOn(XMLHttpRequest.prototype, 'open').and.callThrough();
      spyOn(XMLHttpRequest.prototype, 'setRequestHeader').and.callThrough();
      spyOn(XMLHttpRequest.prototype, 'send');
      spyOnProperty(XMLHttpRequest.prototype, 'readyState').and.returnValue(XMLHttpRequest.DONE);
      spyOnProperty(XMLHttpRequest.prototype, 'status').and.returnValue(200);
    });

    it('should make successful request and return track array', (done) => {
      // given
      const user = new uUser(1, 'testLogin');
      spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify(validListResponse));
      // when
      uTrack.fetchList(user)
        .then((result) => {
          expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('GET', 'utils/gettracks.php?userid=1', true);
          expect(result).toEqual(jasmine.arrayContaining([ new uTrack(validListResponse[0].id, validListResponse[0].name, user) ]));
          expect(result.length).toBe(2);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should throw error on invalid JSON', (done) => {
      // given
      const user = new uUser(1, 'testLogin');
      spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify(invalidListResponse));
      // when
      uTrack.fetchList(user)
        .then(() => {
          done.fail('resolve callback called');
        })
        .catch((e) => {
          expect(e).toEqual(jasmine.any(Error));
          done();
        });
    });

    it('should make successful request and return latest track position for given user', (done) => {
      // given
      const user = new uUser(1, 'testLogin');
      spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify([ jsonPosition ]));
      // when
      uTrack.fetchLatest(user)
        .then((result) => {
          expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('GET', 'utils/getpositions.php?last=true&userid=1', true);
          expect(result).toBeInstanceOf(uTrack);
          expect(result.id).toEqual(jsonPosition.trackid);
          expect(result.length).toBe(1);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should make successful request and return null when there are no positions for the user', (done) => {
      // given
      const user = new uUser(1, 'testLogin');
      spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify([]));
      // when
      uTrack.fetchLatest(user)
        .then((result) => {
          expect(result).toBe(null);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should make successful request and fetch track positions', (done) => {
      // given
      spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify([ jsonPosition ]));
      track.clear();
      // when
      track.fetchPositions()
        .then(() => {
          expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('GET', `utils/getpositions.php?userid=${track.user.id}&trackid=${track.id}`, true);
          expect(track.length).toBe(1);
          expect(track.positions[0].id).toEqual(jsonPosition.id);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should make successful request and append track positions to existing data', (done) => {
      // given
      spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify([ jsonPosition ]));
      track.clear();
      // when
      track.fetchPositions()
        .then(() => {
          expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('GET', `utils/getpositions.php?userid=${track.user.id}&trackid=${track.id}`, true);
          expect(track.length).toBe(1);
          expect(track.positions[0].id).toEqual(jsonPosition.id);
          // eslint-disable-next-line jasmine/no-promise-without-done-fail
          track.fetchPositions().then(() => {
            expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('GET', `utils/getpositions.php?userid=${track.user.id}&trackid=${track.id}&afterid=${track.positions[0].id}`, true);
            expect(track.length).toBe(2);
            expect(track.positions[0].id).toEqual(jsonPosition.id);
            done();
          });
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should make successful track import request', (done) => {
      // given
      const authUser = new uUser(1, 'admin');
      spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify(validListResponse));
      const form = document.createElement('form');
      // when
      uTrack.import(form, authUser)
        .then((tracks) => {
          expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('POST', 'utils/import.php', true);
          expect(XMLHttpRequest.prototype.send).toHaveBeenCalledWith(new FormData(form));
          expect(tracks.length).toBe(2);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should not open export url when track has no positions', () => {
      // given
      spyOn(uUtils, 'openUrl');
      const type = 'ext';
      // when
      track.export(type);
      // then
      expect(uUtils.openUrl).not.toHaveBeenCalled();
    });

    it('should open export url', () => {
      // given
      track.positions.push(new uPosition());
      spyOn(uUtils, 'openUrl');
      const type = 'ext';
      // when
      track.export(type);
      // then
      expect(uUtils.openUrl).toHaveBeenCalledWith(`utils/export.php?type=${type}&userid=${track.user.id}&trackid=${track.id}`);
    });

    it('should delete track', (done) => {
      // given
      spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify([]));
      // when
      track.delete()
        .then(() => {
          expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('POST', 'utils/handletrack.php', true);
          expect(XMLHttpRequest.prototype.send).toHaveBeenCalledWith(`action=delete&trackid=${track.id}`);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should save track meta', (done) => {
      // given
      spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify([]));
      // when
      track.saveMeta()
        .then(() => {
          expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('POST', 'utils/handletrack.php', true);
          expect(XMLHttpRequest.prototype.send).toHaveBeenCalledWith(`action=update&trackid=${track.id}&trackname=${track.name}`);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

  });

});
