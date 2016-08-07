const Sequelize = require('sequelize');

const {
    DB_PORT_3306_TCP_ADDR,
    DB_PORT_3306_TCP_PORT,
    DB_ENV_MYSQL_DATABASE,
    DB_ENV_MYSQL_USER,
    DB_ENV_MYSQL_PASSWORD
} = process.env;

const sequelize = new Sequelize(DB_ENV_MYSQL_DATABASE, DB_ENV_MYSQL_USER, DB_ENV_MYSQL_PASSWORD, {
    host: DB_PORT_3306_TCP_ADDR,
    port: DB_PORT_3306_TCP_PORT,
    dialect: 'mysql',
    define: {
        timestamps: false
    },
    timezone: '+08:00',
    logging: false
});

module.exports = sequelize;