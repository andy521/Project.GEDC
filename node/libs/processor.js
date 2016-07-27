const Pusher = require('pusher');
const sequelize = require('./sequelize');
const SensorData = require('../models/sensor_data');
const HourlyData = require('../models/hourly_data');
const DailyData = require('../models/daily_data');
const MonthlyData = require('../models/monthly_data');
const Notification = require('../models/notification');

const pattern = /^([d|n|t]):(.+)/;
const ERROR_REPONSE = 'error: invalid request';

const pusher = new Pusher({
    appId: '213316',
    key: '24737bc4b88e96c1898c',
    secret: 'd6ab2f39949df87df8fa',
    encrypted: true
});

const MESSAGES = {
    0: {
        event: 'new_alert',
        channel: 'alert_channel',
        subcategories: {
            0: 'fall'
        }
    }
};
const getMessage = (category, subcategory) => {
    if (typeof MESSAGES[category] === 'undefined' || typeof MESSAGES[category]['subcategories'][subcategory] === 'undefined') {
        return null;
    }
    return {
        event: MESSAGES[category]['event'],
        channel: MESSAGES[category]['channel'],
        type: MESSAGES[category]['subcategories'][subcategory]
    };
}

const unixTimeToDate = function(time) {
    switch (new String(time).length) {
        case 10: return new Date(time * 1000);
        case 13: return new Date(time);
        default: return null;
    }
}

const handlers = {
    'd': function handleData(str) {
        const parsed = JSON.parse(str);
        if (parsed !== null && parsed.length == 9) {
            const timestamp = unixTimeToDate(parsed[1]);
            if (timestamp !== null) {
                const data = {
                    sensorId: parsed[0],
                    timestamp,
                    acv: parsed[2],
                    acx: parsed[3],
                    acy: parsed[4],
                    acz: parsed[5],
                    ibi: parsed[6],
                    bpm: parsed[7],
                    tem: parsed[8],
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
                }).then(() => str, error => error.message);
            }
        }
        return Promise.resolve('invalid data format');
    },
    't': function handleTime(str, timeReceived) {
        return Promise.resolve(str + ',' + timeReceived + ',' + new Date().getTime());
    },
    'n': function handleNotification (str) {
        const parsed = JSON.parse(str);
        if (parsed !== null && parsed.length == 4) {
            const sensorId = parsed[0];
            const category = parsed[2];
            const subcategory = parsed[3];
            const timestamp = unixTimeToDate(parsed[1]);
            if (timestamp !== null) {
                const message = getMessage(category, subcategory);
                if (message !== null) {
                    message['sensorId'] = sensorId;
                    message['timestamp'] = timestamp; // TODO: format time

                    return Notification.create({
                        sensorId, timestamp, category, subcategory,
                        createdAt: new Date(),
                        updatedAt: new Date()
                    }).then(() => new Promise((resolve, reject) => {
                        pusher.trigger(message['channel'], message['event'], message, resolve);
                    }), error => error.message);
                }
            }
        }
        return Promise.resolve('invalid data format');
    },
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
                return handlers[type](content, this.timestamp) || ERROR_REPONSE;
            }
        }
        return ERROR_REPONSE;
    }
}

module.exports = Processor;
