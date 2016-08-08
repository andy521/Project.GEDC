const Sequelize = require('sequelize');
const sequelize = require('../libs/sequelize');

const SensorData = sequelize.define('sensor_data', {
    id: {
        type: Sequelize.INTEGER.UNSIGNED,
        primaryKey: true
    },
    sensorId: {
        type: Sequelize.INTEGER,
        field: 'sensor_id'
    },
    timestamp: Sequelize.DATE,
    ibi: Sequelize.INTEGER,
    bpm: Sequelize.INTEGER,
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

module.exports = SensorData;