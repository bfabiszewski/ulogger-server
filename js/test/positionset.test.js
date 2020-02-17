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
import uPositionSet from '../src/positionset.js';

describe('PositionSet tests', () => {

  let set;

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

    set = new uPositionSet();

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
      'id': posId,
      'latitude': latitude,
      'longitude': longitude,
      'altitude': altitude,
      'speed': speed,
      'bearing': bearing,
      'timestamp': timestamp,
      'accuracy': accuracy,
      'provider': provider,
      'comment': comment,
      'image': image,
      'username': username,
      'trackid': trackid,
      'trackname': trackname,
      'meters': meters,
      'seconds': seconds
    };
  });

  describe('simple tests', () => {

    it('should create uPositionSet instance', () => {
      // when
      set = new uPositionSet();
      // then
      expect(set.positions).toEqual([]);
    });

    it('should clear positions data', () => {
      // given
      set.positions.push(new uPosition());
      // when
      set.clear();
      // then
      expect(set.positions).toEqual([]);
    });

    it('should return positions length', () => {
      // given
      set.positions.push(new uPosition());
      // when
      const length = set.length;
      // then
      expect(length).toBe(1);
    });

    it('should return true when has positions', () => {
      // given
      set.positions.push(new uPosition());
      // when
      const result = set.hasPositions;
      // then
      expect(result).toBe(true);
    });

    it('should return false when does not have positions', () => {
      // given
      set.positions.length = 0;
      // when
      const result = set.hasPositions;
      // then
      expect(result).toBe(false);
    });

    it('should parse json object to track positions', () => {
      // when
      set.fromJson([ jsonPosition ]);
      // then
      expect(set.length).toBe(1);
      const position = set.positions[0];

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
      set.fromJson([ position1 ]);
      // when
      set.fromJson([ jsonPosition ]);
      // then
      expect(set.length).toBe(1);
      const position2 = set.positions[0];

      expect(position2.id).toBe(posId);
    });

    it('should append track positions with new ones', () => {
      const position1 = { ...jsonPosition };
      position1.id = 100;
      set.fromJson([ position1 ]);
      // when
      set.fromJson([ jsonPosition ], true);
      // then
      expect(set.length).toBe(2);
      expect(set.positions[0].id).toBe(position1.id);
      expect(set.positions[1].id).toBe(jsonPosition.id);
    });

  });

  describe('ajax tests', () => {

    beforeEach(() => {
      spyOn(XMLHttpRequest.prototype, 'open').and.callThrough();
      spyOn(XMLHttpRequest.prototype, 'setRequestHeader').and.callThrough();
      spyOn(XMLHttpRequest.prototype, 'send');
      spyOnProperty(XMLHttpRequest.prototype, 'readyState').and.returnValue(XMLHttpRequest.DONE);
      spyOnProperty(XMLHttpRequest.prototype, 'status').and.returnValue(200);
    });

    it('should make successful request and return latest position for each user', (done) => {
      // given
      spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify([ jsonPosition ]));
      // when
      uPositionSet.fetchLatest()
        .then((result) => {
          expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('GET', 'utils/getpositions.php?last=true', true);
          expect(result).toBeInstanceOf(uPositionSet);
          expect(result.length).toBe(1);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });


    it('should call getpositions with params', (done) => {
      // given
      const params = { param: 'test' };
      spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify([ jsonPosition ]));
      // when
      uPositionSet.fetch(params)
        .then(() => {
          expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('GET', 'utils/getpositions.php?param=test', true);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

  });

});
