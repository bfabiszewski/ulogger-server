/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Http from '../src/Http.js';
import Position from '../src/Position.js';
import PositionSet from '../src/PositionSet.js';

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
  let hasImage;
  let userName;
  let trackId;
  let trackName;
  let meters;
  let seconds;

  let jsonPosition;

  beforeEach(() => {

    set = new PositionSet();

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
      'trackId': trackId,
      'trackName': trackName,
      'meters': meters,
      'seconds': seconds
    };
  });

  describe('simple tests', () => {

    it('should create PositionSet instance', () => {
      // when
      set = new PositionSet();
      // then
      expect(set.positions).toEqual([]);
    });

    it('should clear positions data', () => {
      // given
      set.positions.push(new Position());
      // when
      set.clear();
      // then
      expect(set.positions).toEqual([]);
    });

    it('should return positions length', () => {
      // given
      set.positions.push(new Position());
      // when
      const length = set.length;
      // then
      expect(length).toBe(1);
    });

    it('should return true when has positions', () => {
      // given
      set.positions.push(new Position());
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
      expect(position.hasImage).toBe(hasImage);
      expect(position.userName).toBe(userName);
      expect(position.trackId).toBe(trackId);
      expect(position.trackName).toBe(trackName);
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

  it('should make successful request and return latest position for each user', (done) => {
    // given
    spyOn(Http, 'get').and.resolveTo([ jsonPosition ]);
    // when
    PositionSet.fetchLatest()
      .then((result) => {
        expect(Http.get).toHaveBeenCalledWith('api/users/position');
        expect(result).toBeInstanceOf(PositionSet);
        expect(result.length).toBe(1);
        done();
      })
      .catch((e) => done.fail(`reject callback called (${e})`));
  });

});
