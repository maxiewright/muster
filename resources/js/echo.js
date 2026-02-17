import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const websocketHost = import.meta.env.VITE_REVERB_HOST;
const configuredScheme = import.meta.env.VITE_REVERB_SCHEME;
const isLocalSocketHost = ['localhost', '127.0.0.1'].includes(websocketHost ?? '');
const resolvedScheme = configuredScheme ?? (isLocalSocketHost ? 'http' : 'https');

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: websocketHost,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: resolvedScheme === 'https',
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});
