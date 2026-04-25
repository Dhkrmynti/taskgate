import './bootstrap';

import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

import DataTable from 'datatables.net-dt';
// DataTables 1.13 automatic initialization with jQuery
window.DataTable = DataTable;

import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';
window.Choices = Choices;

import Swal from 'sweetalert2';
window.Swal = Swal;

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Only instantiate Echo if a Pusher Key is provided to avoid console errors
if (import.meta.env.VITE_PUSHER_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
        wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
        wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
        wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
} else {
    // Provide a mock Echo to prevent ReferenceError in views
    window.Echo = {
        channel: () => ({ listen: () => ({}) }),
        private: () => ({ listen: () => ({}) }),
    };
}
