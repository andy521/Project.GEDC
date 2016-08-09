const Redis = require('redis');
const sequelize = require('./sequelize');
const SensorData = require('../models/sensor_data');
const HourlyData = require('../models/hourly_data');
const DailyData = require('../models/daily_data');
const MonthlyData = require('../models/monthly_data');

const CALCULATE_ON = 256;
const pattern = /^([p|a|t]):(.+)/;
const INVALID_REQUEST = 'invalid request';
const INVALID_DATA_FORMAT = 'invalid data format';

const { CACHE_PORT_6379_TCP_ADDR, CACHE_PORT_6379_TCP_PORT } = process.env;
const redis = Redis.createClient(CACHE_PORT_6379_TCP_PORT, CACHE_PORT_6379_TCP_ADDR);

redis.on('error', function(error) {
    console.log("Redis error: " + error);
});

const unixTimeToDate = function(time) {
    switch (new String(time).length) {
        case 10: return new Date(time * 1000);
        case 13: return new Date(time);
        default: return null;
    }
}

const handlers = {
    'p': function handlePhysical(str) {
        const parsed = JSON.parse(str);
        if (parsed !== null && parsed.length == 5) {
            const timestamp = unixTimeToDate(parsed[1]);
            if (timestamp !== null) {
                const data = {
                    sensorId: parsed[0],
                    timestamp,
                    ibi: parsed[2],
                    bpm: parsed[3],
                    tem: parsed[4],
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
        }
        return Promise.reject(new Error(INVALID_DATA_FORMAT));
    },
    'a': function handleAcceleration(str) {
        const parsed = JSON.parse(str);
        if (parsed !== null && parsed.length == 6) {
            const sensorId = parsed[0];
            const timestamp = unixTimeToDate(parsed[1]);
            const value = parseFloat(parsed[2]);
            const cosx = parseFloat(parsed[3]);
            const cosy = parseFloat(parsed[4]);
            const cosz = parseFloat(parsed[5]);
            if (timestamp !== null) {
                return new Promise((resolve, reject) => {
                    redis.zadd([`acceleration:${sensorId}`, timestamp.getTime(), `${value},${cosx},${cosy},${cosz}`], (error, response) => error === null ? resolve() : reject(error));
                }).then(() => {
                    return new Promise((resolve, reject) => {
                        redis.zcard(`acceleration:${sensorId}`, (error, response) => error === null ? resolve(response) : reject(error));
                    });
                }).then(response => {
                    // Tell other programs to start calculate when data size is times of ${CALCULATE_ON}
                    if (response % CALCULATE_ON === 0) {
                        redis.publish(`acceleration:${sensorId}`, 'START');
                    }
                    return str;
                });
            }
        }
        return Promise.reject(new Error(INVALID_DATA_FORMAT));
    },
    't': function handleTime(str, timeReceived) {
        return Promise.resolve(str + ',' + timeReceived + ',' + new Date().getTime());
    }
}

class Processor {
    constructor() {
        this.timestamp = new Date().getTime();
    }

    handle(str) {
        const ret = pattern.exec(str);
        if (ret !== null && ret.length === 3) {
            const type = ret[1];
            const content = ret[2];
            if (typeof handlers[type] !== 'undefined') {
                try {
                    return handlers[type](content, this.timestamp);
                } catch (error) {
                    return Promise.reject(error);
                }
            }
        }
        return Promise.reject(new Error(INVALID_REQUEST));
    }
}

module.exports = Processor;
