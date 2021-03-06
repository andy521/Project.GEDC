const Sequelize = require('sequelize');
const sequelize = require('../libs/sequelize');

const DailyData = sequelize.define('daily_data', {
    id: {
        type: Sequelize.INTEGER.UNSIGNED,
        primaryKey: true
    },
    sensorId: {
        type: Sequelize.INTEGER,
        field: 'sensor_id'
    },
    timestamp: Sequelize.DATE,
    count: Sequelize.INTEGER,
    ibi: Sequelize.DOUBLE(8, 2),
    bpm: Sequelize.DOUBLE(8, 2),
    tem: Sequelize.DOUBLE(8, 2),
    createdAt: {
        type: Sequelize.DATE,
        field: 'created_at'
    },
    updatedAt: {
        type: Sequelize.DATE,
        field: 'updated_at'
    },
});

DailyData.saveOrUpdateOnSensorData = (sensorData, transaction) => {
    if (typeof sensorData === 'undefined' || sensorData === null) {
        return Promise.reject(new Error('no saved sensor data'));
    }
    const timestamp = new Date(sensorData.timestamp.getTime());
    timestamp.setHours(0, 0, 0, 0);
    return DailyData.findOne({
        where: {
            sensorId: sensorData.sensorId,
            timestamp,
        }
    }).then((dailyData) => {
        let data = {};
        if (dailyData !== null) {
            data = dailyData.get({ plain: true });
        }
        const lastCount = data.count | 0;
        data.timestamp = timestamp;
        data.count = lastCount + 1;
        data.sensorId = sensorData.sensorId;
        data.ibi = ((data.ibi ? data.ibi : 0) * lastCount + sensorData.ibi) / (lastCount + 1);
        data.bpm = ((data.bpm ? data.bpm : 0) * lastCount + sensorData.bpm) / (lastCount + 1);
        data.tem = ((data.tem ? data.tem : 0) * lastCount + sensorData.tem) / (lastCount + 1);
        data.createdAt = typeof data.createdAt === 'undefined' ? new Date() : data.createdAt;
        data.updatedAt = new Date();
        return DailyData.upsert(data, { transaction });
    });
}

module.exports = DailyData;