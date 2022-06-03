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

import uAjax from '../src/ajax.js';
import uPosition from '../src/position.js';

describe('Position tests', () => {

  const nullableProperties = [
    'altitude',
    'speed',
    'bearing',
    'accuracy',
    'provider',
    'comment',
    'image'
  ];
  const nonNullableProperties = [
    'id',
    'latitude',
    'longitude',
    'timestamp',
    'username',
    'trackid',
    'trackname',
    'meters',
    'seconds'
  ];
  const properties = nullableProperties.concat(nonNullableProperties);

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

  it('should create uPosition instance from json object', () => {
    // when
    const position = uPosition.fromJson(jsonPosition);
    // then
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

  describe('should raise error on undefined property', () => {
    properties.forEach((prop) => {
      it(`testing property: ${prop}`, () => {
        // given
        const posCopy = { ...jsonPosition };
        // when
        delete posCopy[prop];
        // then
        expect(() => { uPosition.fromJson(posCopy); }).toThrow(new Error('Invalid value'));
      });
    });
  });

  describe('should raise error on null non-nullable property', () => {
    nonNullableProperties.forEach((prop) => {
      it(`testing property: ${prop}`, () => {
        // given
        const posCopy = { ...jsonPosition };
        // when
        posCopy[prop] = null;
        // then
        expect(() => { uPosition.fromJson(posCopy); }).toThrow(new Error('Invalid value'));
      });
    });
  });

  describe('should not raise error on null nullable property', () => {
    nullableProperties.forEach((prop) => {
      it(`testing property: ${prop}`, () => {
        // given
        const posCopy = { ...jsonPosition };
        // when
        posCopy[prop] = null;
        let pos = {};
        // then
        expect(() => { pos = uPosition.fromJson(posCopy); }).not.toThrow(new Error('Invalid value'));
        expect(pos[prop]).toBeNull();
      });
    });
  });

  it('should result false on null comment', () => {
    // when
    jsonPosition.comment = null;
    const position = uPosition.fromJson(jsonPosition);
    // then
    expect(position.hasComment()).toBe(false);
  });

  it('should result false on empty comment', () => {
    // when
    jsonPosition.comment = '';
    const position = uPosition.fromJson(jsonPosition);
    // then
    expect(position.hasComment()).toBe(false);
  });

  it('should result true on non-null comment', () => {
    // when
    jsonPosition.comment = 'comment';
    const position = uPosition.fromJson(jsonPosition);
    // then
    expect(position.hasComment()).toBe(true);
  });


  it('should result false on null image', () => {
    // when
    jsonPosition.image = null;
    const position = uPosition.fromJson(jsonPosition);
    // then
    expect(position.hasImage()).toBe(false);
  });

  it('should result false on empty image', () => {
    // when
    jsonPosition.image = '';
    const position = uPosition.fromJson(jsonPosition);
    // then
    expect(position.hasImage()).toBe(false);
  });

  it('should result true on non-null image', () => {
    // when
    jsonPosition.image = 'image';
    const position = uPosition.fromJson(jsonPosition);
    // then
    expect(position.hasImage()).toBe(true);
  });

  it('should calculate speed', () => {
    // when
    const position = uPosition.fromJson(jsonPosition);
    position.totalMeters = 1000;
    position.totalSeconds = 10;
    // then
    expect(position.totalSpeed).toBe(position.totalMeters / position.totalSeconds);
  });

  it('should delete position on server', () => {
    // given
    spyOn(uPosition, 'update');
    const position = uPosition.fromJson(jsonPosition);
    // when
    position.delete()
    // then
    expect(uPosition.update).toHaveBeenCalledWith({ action: 'delete', posid: posId });
  });

  it('should save position on server', () => {
    // given
    spyOn(uPosition, 'update');
    const position = uPosition.fromJson(jsonPosition);
    // when
    position.save()
    // then
    expect(uPosition.update).toHaveBeenCalledWith({ action: 'update', posid: posId, comment: comment });
  });

  it('should delete image on server', (done) => {
    // given
    spyOn(uPosition, 'update').and.resolveTo();
    const position = uPosition.fromJson(jsonPosition);
    // when
    position.imageDelete()
    // then
    setTimeout(() => {
      expect(uPosition.update).toHaveBeenCalledWith({ action: 'imagedel', posid: posId });
      expect(position.image).toBeNull();
      done();
    }, 100);
  });

  it('should add image on server', (done) => {
    // given
    const newImage = 'new_image.jpg';
    const imageFile = 'imageFile';
    spyOn(uPosition, 'update').and.resolveTo({ image: newImage });
    const position = uPosition.fromJson(jsonPosition);
    // when
    position.imageAdd(imageFile);
    // then
    setTimeout(() => {
      expect(uPosition.update).toHaveBeenCalledWith(jasmine.any(FormData));

      /** @var {FormData} */
      const data = uPosition.update.calls.mostRecent().args[0];

      expect(data.get('image')).toBe(imageFile);
      expect(data.get('action')).toBe('imageadd');
      expect(data.get('posid')).toBe(posId.toString());
      expect(position.image).toBe(newImage);
      done();
    }, 100);
  });

  it('should call ajax post with url and params', () => {
    // given
    const url = 'utils/handleposition.php';
    spyOn(uAjax, 'post');
    const data = 'test data';
    // when
    uPosition.update(data);
    // then
    expect(uAjax.post).toHaveBeenCalledWith(url, data);
  });

  it('should calculate distance to another position', () => {
    // given
    const position = uPosition.fromJson(jsonPosition);
    const position2 = uPosition.fromJson(jsonPosition);
    position2.latitude += 1;
    position2.longitude += 1;
    // then
    expect(position.distanceTo(position2)).toBeCloseTo(155621.15, 2);
  });

  it('should calculate time difference to another position', () => {
    // given
    const timeDifference = 1234;
    const position = uPosition.fromJson(jsonPosition);
    const position2 = uPosition.fromJson(jsonPosition);
    position.timestamp += timeDifference;
    // then
    expect(position.secondsTo(position2)).toBe(timeDifference);
  });

});
