const Redis = require('redis');
const sequelize = require('./sequelize');
const SensorData = require('../models/sensor_data');
const HourlyData = require('../models/hourly_data');
const DailyData = require('../models/daily_data');
const MonthlyData = require('../models/monthly_data');

const MOTION_DATA_LENGTH = 30;
const pattern = /^([p|m]):(.+)/;
const INVALID_REQUEST = 'invalid request';
const INVALID_DATA_FORMAT = 'invalid data format';

const { CACHE_PORT_6379_TCP_ADDR, CACHE_PORT_6379_TCP_PORT } = process.env;
const redis = Redis.createClient(CACHE_PORT_6379_TCP_PORT, CACHE_PORT_6379_TCP_ADDR);

redis.on('error', function(error) {
    console.error(error);
});

const handlers = {
    'p': function handlePhysical(str) {
        const parsed = JSON.parse(str);
        if (parsed !== null && parsed.length == 4) {
            const data = {
                sensorId: parsed[0],
                timestamp: new Date(),
                ibi: parsed[1],
                bpm: parsed[2],
                tem: parsed[3],
                createdAt: new Date(),
                updatedAt: new Date()
            };
            return sequelize.transaction(transaction => {
                const promises = [
                    SensorData.create(data, { transaction }),
                    HourlyData.saveOrUpdateOnSensorData(data, transaction),
                    DailyData.saveOrUpdateOnSensorData(data, transaction),
                    MonthlyData.saveOrUpdateOnSensorData(data, transaction),
                ];
                return Promise.all(promises);
            }).then(() => str);
        }
        return Promise.reject(new Error(INVALID_DATA_FORMAT));
    },
    'm': function handleMotion(str) {
        const parsed = JSON.parse(str);
        if (parsed !== null && parsed.length == 7) {
            const sensorId = parsed[0];
            const accx = parseFloat(parsed[1]);
            const accy = parseFloat(parsed[2]);
            const accz = parseFloat(parsed[3]);
            // gyroscope
            const gyrx = parseFloat(parsed[4]);
            const gyry = parseFloat(parsed[5]);
            const gyrz = parseFloat(parsed[6]);
            return new Promise((resolve, reject) => {
                redis.lpush(`motion:${sensorId}`, `${accx},${accy},${accz},${gyrx},${gyry},${gyrz}`, (error, response) => error === null ? resolve() : reject(error));
            }).then(() => {
                return new Promise((resolve, reject) => {
                    redis.llen(`motion:${sensorId}`, (error, response) => error === null ? resolve(response) : reject(error));
                });
            }).then(response => {
                // Tell other programs to start calculate when data size is times of ${MOTION_DATA_LENGTH}
                if (response > 0 && response % MOTION_DATA_LENGTH === 0) {
                    redis.publish('motion', sensorId);
                }
                return str;
            });
        }
        return Promise.reject(new Error(INVALID_DATA_FORMAT));
    }
}

class Processor {
    handle(str) {
        const ret = pattern.exec(str);
        if (ret !== null && ret.length === 3) {
            const type = ret[1];
            const content = ret[2];
            if (typeof handlers[type] !== 'undefined') {
                try {
                    return handlers[type](content);
                } catch (error) {
                    return Promise.reject(error);
                }
            }
        }
        return Promise.reject(new Error(INVALID_REQUEST));
    }
}

module.exports = Processor;
