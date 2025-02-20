/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Http from '../src/Http.js';
import HttpError from '../src/HttpError';
import Position from '../src/Position.js';
import Track from '../src/Track.js';
import User from '../src/User.js';
import Utils from '../src/Utils.js';


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
  let hasImage;
  let userName;
  let userId;
  let trackId;
  let trackName;
  let meters;
  let seconds;

  let jsonPosition;
  beforeEach(() => {
    const id = 1;
    const name = 'test';
    const user = new User(1, 'user');
    track = new Track(id, name, user);

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
      hasImage: hasImage,
      userName: userName,
      userId: userId,
      trackId: trackId,
      trackName: trackName,
      meters: meters,
      seconds: seconds
    };
  });

  describe('simple tests', () => {

    it('should throw error when creating Track instance without user parameter', () => {
      // given
      const id = 1;
      const name = 'test';
      // when
      // then
      expect(() => new Track(id, name)).toThrow(new Error('Invalid argument for track constructor'));
    });

    it('should create Track instance with user parameter', () => {
      // given
      const id = 1;
      const name = 'test';
      const user = new User(1, 'user');
      // when
      track = new Track(id, name, user);
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
      track.positions.push(new Position());
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
      track.positions.push(new Position());
      // when
      const length = track.length;
      // then
      expect(length).toBe(1);
    });

    it('should return true when has positions', () => {
      // given
      track.positions.push(new Position());
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
      const otherTrack = new Track(1, 'other', new User(2, 'user2'));
      // when
      const result = track.isEqualTo(otherTrack);
      // then
      expect(result).toBe(true);
    });

    it('should not be equal to other track with other id', () => {
      // given
      track.id = 1;
      const otherTrack = new Track(2, 'other', new User(2, 'user2'));
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
      expect(position.hasImage).toBe(hasImage);
      expect(position.userName).toBe(userName);
      expect(position.userId).toBe(userId);
      expect(position.trackId).toBe(trackId);
      expect(position.trackName).toBe(trackName);
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

  describe('request tests', () => {
    const validListResponse = [ { 'id': 145, 'name': 'Track 1' }, { 'id': 144, 'name': 'Track 2' } ];
    const invalidListResponse = [ { 'name': 'Track 1' }, { 'id': 144, 'name': 'Track 2' } ];

    beforeEach(() => {
      spyOn(Http, 'get').and.resolveTo();
      spyOn(Http, 'post').and.resolveTo();
    });

    it('should make successful request and return track array', (done) => {
      // given
      const user = new User(1, 'testLogin');
      Http.get.and.resolveTo(validListResponse);
      // when
      Track.fetchList(user)
        .then((result) => {
          expect(Http.get).toHaveBeenCalledWith(`api/users/${user.id}/tracks`);
          expect(result).toEqual(jasmine.arrayContaining([ new Track(validListResponse[0].id, validListResponse[0].name, user) ]));
          expect(result.length).toBe(2);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should throw error on invalid JSON', (done) => {
      // given
      const user = new User(1, 'testLogin');
      Http.get.and.resolveTo(invalidListResponse);
      // when
      Track.fetchList(user)
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
      const user = new User(1, 'testLogin');
      Http.get.and.resolveTo(jsonPosition);
      // when
      Track.fetchLatest(user)
        .then((result) => {
          expect(Http.get).toHaveBeenCalledWith(`api/users/${user.id}/position`);
          expect(result).toBeInstanceOf(Track);
          expect(result.id).toEqual(jsonPosition.trackId);
          expect(result.length).toBe(1);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should make successful request and return null when there are no positions for the user', (done) => {
      // given
      const user = new User(1, 'testLogin');
      Http.get.and.rejectWith(new HttpError('Not found', 404));
      // when
      Track.fetchLatest(user)
        .then((result) => {
          expect(result).toBe(null);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should make successful request and fetch track positions', (done) => {
      // given
      Http.get.and.resolveTo([ jsonPosition ]);
      track.clear();
      // when
      track.fetchPositions()
        .then(() => {
          expect(Http.get).toHaveBeenCalledWith(`api/tracks/${track.id}/positions`);
          expect(track.length).toBe(1);
          expect(track.positions[0].id).toEqual(jsonPosition.id);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should make successful request and append track positions to existing data', (done) => {
      // given
      Http.get.and.resolveTo([ jsonPosition ]);
      track.clear();
      // when
      track.fetchPositions()
        .then(() => {
          expect(Http.get).toHaveBeenCalledTimes(1);
          expect(Http.get).toHaveBeenCalledWith(`api/tracks/${track.id}/positions`);
          expect(track.length).toBe(1);
          expect(track.positions[0].id).toEqual(jsonPosition.id);
          // eslint-disable-next-line jasmine/no-promise-without-done-fail
          track.fetchPositions().then(() => {
            expect(Http.get).toHaveBeenCalledTimes(2);
            expect(Http.get).toHaveBeenCalledWith(`api/tracks/${track.id}/positions?afterId=${track.positions[0].id}`);
            expect(track.length).toBe(2);
            expect(track.positions[0].id).toEqual(jsonPosition.id);
            done();
          });
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should make successful track import request', (done) => {
      // given
      const authUser = new User(1, 'admin');
      Http.post.and.resolveTo(validListResponse);
      const form = document.createElement('form');
      // when
      Track.import(form, authUser)
        .then((tracks) => {
          expect(Http.post).toHaveBeenCalledTimes(1);
          expect(Http.post).toHaveBeenCalledWith('api/tracks/import', form);
          expect(tracks.length).toBe(2);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should not open export url when track has no positions', () => {
      // given
      spyOn(Utils, 'openUrl');
      const type = 'ext';
      // when
      track.export(type);
      // then
      expect(Utils.openUrl).not.toHaveBeenCalled();
    });

    it('should open export url', () => {
      // given
      track.positions.push(new Position());
      spyOn(Utils, 'openUrl');
      const type = 'ext';
      // when
      track.export(type);
      // then
      expect(Utils.openUrl).toHaveBeenCalledWith(`api/tracks/${track.id}/export?format=${type}`);
    });

    it('should delete track', (done) => {
      // given
      spyOn(Http, 'delete').and.resolveTo();
      // when
      track.delete()
        .then(() => {
          expect(Http.delete).toHaveBeenCalledWith(`api/tracks/${track.id}`);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should save track meta', (done) => {
      // given
      spyOn(Http, 'put').and.resolveTo();
      // when
      track.saveMeta()
        .then(() => {
          expect(Http.put).toHaveBeenCalledWith(`api/tracks/${track.id}`,
            { id: track.id, name: track.name, userId: track.user.id });
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should get track meta', (done) => {
      // given
      // when
      Track.getMeta(track.id)
        .then(() => {
          expect(Http.get).toHaveBeenCalledWith(`api/tracks/${track.id}`);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

  });

});
