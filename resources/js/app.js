import './bootstrap';

import Alpine from 'alpinejs';
import facialCapture from './facial-capture';

window.Alpine = Alpine;

Alpine.data('facialCapture', facialCapture);

Alpine.start();
