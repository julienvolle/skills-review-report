// jQuery
const $ = require('jquery');
global.$ = global.jQuery = $;
require('jquery-datetimepicker');

// Bootstrap
require('bootstrap');

// Display flash messages
import './scripts/flash_messages';

// Setup dynamic button
import './scripts/button';

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';
