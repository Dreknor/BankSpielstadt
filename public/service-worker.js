// Bank – Service Worker für Web-Push-Benachrichtigungen
// Version: 1

self.addEventListener('push', function (event) {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { title: 'Bank', body: event.data ? event.data.text() : 'Neue Benachrichtigung' };
    }

    const titel    = data.title || 'Bank-Fehler';
    const nachricht = data.body  || '';
    const url      = data.url   || '/';
    const icon     = data.icon  || '/favicon.ico';

    const optionen = {
        body:  nachricht,
        icon:  icon,
        badge: icon,
        data:  { url: url },
        requireInteraction: true,
    };

    event.waitUntil(
        self.registration.showNotification(titel, optionen)
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) ? event.notification.data.url : '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            for (const client of clientList) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

