/**
 * Provide a tiny helper around the browser Fetch API so we do not have to
 * rely on Axios. This helper automatically attaches the `X-Requested-With`
 * header and the CSRF token when it is available.
 */

const getCsrfToken = () => {
    if (typeof document === 'undefined') {
        return null;
    }

    const tokenMeta = document.head.querySelector('meta[name="csrf-token"]');

    if (tokenMeta && tokenMeta.content) {
        return tokenMeta.content;
    }

    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : null;
};

window.httpRequest = async (input, init = {}) => {
    const headers = new Headers(init.headers || {});
    headers.set('X-Requested-With', 'XMLHttpRequest');

    const csrfToken = getCsrfToken();
    if (csrfToken && !headers.has('X-CSRF-TOKEN')) {
        headers.set('X-CSRF-TOKEN', csrfToken);
    }

    const response = await fetch(input, { ...init, headers });

    if (!response.ok) {
        const error = new Error(`Request failed with status ${response.status}`);
        error.response = response;
        throw error;
    }

    return response;
};

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
