import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const createNoopChannel = () => ({
    error: () => createNoopChannel(),
    here: () => createNoopChannel(),
    joining: () => createNoopChannel(),
    leaving: () => createNoopChannel(),
    listen: () => createNoopChannel(),
    listenToAll: () => createNoopChannel(),
    notification: () => createNoopChannel(),
    stopListening: () => createNoopChannel(),
    stopListeningForNotification: () => createNoopChannel(),
    subscribed: () => createNoopChannel(),
    whisper: () => createNoopChannel(),
});

const createFallbackEcho = () => ({
    channel: () => createNoopChannel(),
    disconnect: () => {},
    encryptedPrivate: () => createNoopChannel(),
    join: () => createNoopChannel(),
    leave: () => {},
    leaveChannel: () => {},
    private: () => createNoopChannel(),
    socketId: () => null,
});

const currentHostname = window.location.hostname;
const currentScheme = window.location.protocol === 'https:' ? 'https' : 'http';
const configuredHost = import.meta.env.VITE_REVERB_HOST;
const configuredScheme = import.meta.env.VITE_REVERB_SCHEME;
const isLoopbackHost = ['localhost', '127.0.0.1'].includes(configuredHost ?? '');
const websocketHost = configuredHost && !isLoopbackHost ? configuredHost : currentHostname;
const resolvedScheme = websocketHost === currentHostname ? currentScheme : (configuredScheme ?? currentScheme);
const enabledTransports = resolvedScheme === 'https' ? ['wss'] : ['ws'];

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: websocketHost,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        useTLS: resolvedScheme === 'https',
        forceTLS: resolvedScheme === 'https',
        disableStats: true,
        enabledTransports,
    });
} catch (error) {
    console.warn('Failed to bootstrap Laravel Echo.', error);
    window.Echo = createFallbackEcho();
}
