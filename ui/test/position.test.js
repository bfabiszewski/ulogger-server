/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Http from '../src/Http.js';
import Position from '../src/Position.js';

describe('Position tests', () => {

  const nullableProperties = [
    'altitude',
    'speed',
    'bearing',
    'accuracy',
    'provider',
    'comment'
  ];
  const nonNullableProperties = [
    'id',
    'latitude',
    'longitude',
    'timestamp',
    'userName',
    'userId',
    'trackId',
    'trackName',
    'meters',
    'seconds',
    'hasImage'
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
  let hasImage;
  let userName;
  let userId;
  let trackId;
  let trackName;
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
    hasImage = true;
    userName = 'test';
    userId = 1;
    trackId = 134;
    trackName = 'Test name';
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
      'hasImage': hasImage,
      'userName': userName,
      'userId': userId,
      'trackId': trackId,
      'trackName': trackName,
      'meters': meters,
      'seconds': seconds
    };
  });

  it('should create Position instance from json object', () => {
    // when
    const position = Position.fromJson(jsonPosition);
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
    expect(position.hasImage).toBe(hasImage);
    expect(position.userName).toBe(userName);
    expect(position.userId).toBe(userId);
    expect(position.trackId).toBe(trackId);
    expect(position.trackName).toBe(trackName);
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
        expect(() => { Position.fromJson(posCopy); }).toThrow(new Error('Invalid value'));
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
        expect(() => { Position.fromJson(posCopy); }).toThrow(new Error('Invalid value'));
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
        expect(() => { pos = Position.fromJson(posCopy); }).not.toThrow(new Error('Invalid value'));
        expect(pos[prop]).toBeNull();
      });
    });
  });

  it('should result false on null comment', () => {
    // when
    jsonPosition.comment = null;
    const position = Position.fromJson(jsonPosition);
    // then
    expect(position.hasComment()).toBe(false);
  });

  it('should result false on empty comment', () => {
    // when
    jsonPosition.comment = '';
    const position = Position.fromJson(jsonPosition);
    // then
    expect(position.hasComment()).toBe(false);
  });

  it('should result true on non-null comment', () => {
    // when
    jsonPosition.comment = 'comment';
    const position = Position.fromJson(jsonPosition);
    // then
    expect(position.hasComment()).toBe(true);
  });


  it('should raise error on null hasImage', () => {
    // when
    jsonPosition.hasImage = null;
    // then
    expect(() => {
      Position.fromJson(jsonPosition);
    }).toThrow(new Error('Invalid value'));
  });

  it('should raise error on empty hasImage', () => {
    // when
    jsonPosition.hasImage = '';
    // then
    expect(() => {
      Position.fromJson(jsonPosition);
    }).toThrow(new Error('Invalid value'));
  });

  it('should result true on true hasImage', () => {
    // when
    jsonPosition.hasImage = true;
    const position = Position.fromJson(jsonPosition);
    // then
    expect(position.hasImage).toBeTrue();
  });

  it('should result false on false hasImage', () => {
    // when
    jsonPosition.hasImage = false;
    const position = Position.fromJson(jsonPosition);
    // then
    expect(position.hasImage).toBeFalse();
  });

  it('should calculate speed', () => {
    // when
    const position = Position.fromJson(jsonPosition);
    position.totalMeters = 1000;
    position.totalSeconds = 10;
    // then
    expect(position.totalSpeed).toBe(position.totalMeters / position.totalSeconds);
  });

  it('should delete position on server', () => {
    // given
    spyOn(Http, 'delete');
    const position = Position.fromJson(jsonPosition);
    // when
    position.delete()
    // then
    expect(Http.delete).toHaveBeenCalledWith(`/api/positions/${posId}`);
  });

  it('should save position on server', () => {
    // given
    spyOn(Http, 'put');
    const position = Position.fromJson(jsonPosition);
    // when
    position.save()
    // then
    expect(Http.put.calls.mostRecent().args[0]).toEqual(`/api/positions/${posId}`);
    expect(Http.put.calls.mostRecent().args[1]).toEqual(position);
  });

  it('should delete image on server', (done) => {
    // given
    spyOn(Http, 'delete').and.resolveTo();
    const position = Position.fromJson(jsonPosition);
    // when
    position.imageDelete()
    // then
    setTimeout(() => {
      expect(Http.delete.calls.mostRecent().args[0]).toEqual(`/api/positions/${posId}/image`);
      expect(position.hasImage).toBeFalse();
      done();
    }, 100);
  });

  it('should add image on server', (done) => {
    // given
    const imageFile = new File([ 'blob' ], '/path/filepath.gpx');
    spyOn(Http, 'post').and.resolveTo({ hasImage: true });
    const position = Position.fromJson(jsonPosition);
    // when
    position.imageAdd(imageFile);
    // then
    setTimeout(() => {
      expect(Http.post).toHaveBeenCalledWith(`/api/positions/${posId}/image`, jasmine.any(FormData));
      /** @var {FormData} */
      const data = Http.post.calls.mostRecent().args[1];

      expect(data.get('imageUpload')).toBe(imageFile);
      expect(position.hasImage).toBeTrue();
      done();
    }, 100);
  });

  it('should calculate distance to another position', () => {
    // given
    const position = Position.fromJson(jsonPosition);
    const position2 = Position.fromJson(jsonPosition);
    position2.latitude += 1;
    position2.longitude += 1;
    // then
    expect(position.distanceTo(position2)).toBeCloseTo(155621.15, 2);
  });

  it('should calculate time difference to another position', () => {
    // given
    const timeDifference = 1234;
    const position = Position.fromJson(jsonPosition);
    const position2 = Position.fromJson(jsonPosition);
    position.timestamp += timeDifference;
    // then
    expect(position.secondsTo(position2)).toBe(timeDifference);
  });

});
