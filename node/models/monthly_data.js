const Sequelize = require('sequelize');
const sequelize = require('../libs/sequelize');

const MonthlyData = sequelize.define('monthly_data', {
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

MonthlyData.saveOrUpdateOnSensorData = (sensorData, transaction) => {
    if (typeof sensorData === 'undefined' || sensorData === null) {
        return Promise.reject(new Error('no saved sensor data'));
    }
    const timestamp = new Date(sensorData.timestamp.getTime());
    timestamp.setDate(1);
    timestamp.setHours(0, 0, 0, 0);
    return MonthlyData.findOne({
        where: {
            sensorId: sensorData.sensorId,
            timestamp,
        }
    }).then((monthlyData) => {
        let data = {};
        if (monthlyData !== null) {
            data = monthlyData.get({ plain: true });
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
        return MonthlyData.upsert(data, { transaction });
    });
}

module.exports = MonthlyData;