const path = require('path');

module.exports = (Encore) => {
    Encore.addEntry('nova_ezalgolia', [
        path.resolve(__dirname, '../assets/js/search.js'),
    ]);
};