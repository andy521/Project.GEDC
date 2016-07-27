const Sequelize = require('sequelize');
const sequelize = require('../libs/sequelize');

const Notification = sequelize.define('notifications', {
    id: {
        type: Sequelize.INTEGER.UNSIGNED,
        primaryKey: true
    },
    sensorId: {
        type: Sequelize.INTEGER,
        field: 'sensor_id'
    },
    timestamp: Sequelize.DATE,
    category: Sequelize.INTEGER,
    subcategory: Sequelize.INTEGER,
    createdAt: {
        type: Sequelize.DATE,
        field: 'created_at'
    },
    updatedAt: {
        type: Sequelize.DATE,
        field: 'updated_at'
    },
});

module.exports = Notification;