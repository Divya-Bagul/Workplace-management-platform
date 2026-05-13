const STORAGE_KEY = 'workplaceSeenNotificationIds';

function readSeenNotificationIds() {
    try {
        return new Set(JSON.parse(window.localStorage.getItem(STORAGE_KEY) || '[]'));
    } catch {
        return new Set();
    }
}

function writeSeenNotificationIds(ids) {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify([...ids].slice(-100)));
}

function showWorkplaceToast(title, body, actionUrl) {
    const toast = document.getElementById('workplace-live-toast');
    if (!toast) {
        return;
    }

    const link = actionUrl
        ? `<a href="${actionUrl}" class="mt-2 inline-flex text-indigo-700 font-medium hover:text-indigo-900">Open</a>`
        : '';

    toast.innerHTML = `<p class="font-semibold text-indigo-800">${title}</p><p class="mt-1 text-gray-600">${body}</p>${link}`;
    toast.classList.remove('hidden');
    window.clearTimeout(window.__workplaceToastTimer);
    window.__workplaceToastTimer = window.setTimeout(() => toast.classList.add('hidden'), 10000);
}

function showBrowserNotification(title, body, actionUrl) {
    if (!('Notification' in window) || Notification.permission !== 'granted') {
        return;
    }

    const notification = new Notification(title, {
        body,
        icon: '/favicon.ico',
    });

    if (actionUrl) {
        notification.onclick = () => {
            window.focus();
            window.location.href = actionUrl;
        };
    }
}

async function markNotificationRead(id) {
    await window.axios.post(`/notifications/${id}/read`);
}

async function pollUnreadNotifications() {
    const response = await window.axios.get('/notifications/unread');
    const notifications = response.data.notifications || [];
    const seen = readSeenNotificationIds();
    const list = document.getElementById('workplace-notification-list');
    const count = document.getElementById('workplace-notification-count');

    if (list) {
        list.innerHTML = notifications.length
            ? notifications.map((notification) => {
                const href = notification.action_url || '#';
                return `<a href="${href}" data-notification-id="${notification.id}" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0"><p class="font-medium text-gray-900">${notification.title}</p><p class="text-sm text-gray-600 mt-1">${notification.body}</p></a>`;
            }).join('')
            : `<p class="px-4 py-3 text-sm text-gray-500">No new notifications.</p>`;
    }

    if (count) {
        if (notifications.length > 0) {
            count.textContent = String(notifications.length);
            count.classList.remove('hidden');
        } else {
            count.classList.add('hidden');
        }
    }

    notifications.forEach((notification) => {
        if (seen.has(notification.id)) {
            return;
        }

        seen.add(notification.id);
        showWorkplaceToast(notification.title, notification.body, notification.action_url);
        showBrowserNotification(notification.title, notification.body, notification.action_url);
    });

    writeSeenNotificationIds(seen);
}

function bindNotificationUi() {
    const enableButton = document.getElementById('workplace-enable-browser-notifications');
    if (enableButton) {
        enableButton.addEventListener('click', async () => {
            if (!('Notification' in window)) {
                return;
            }

            await Notification.requestPermission();
            enableButton.classList.add('hidden');
        });

        if ('Notification' in window && Notification.permission !== 'default') {
            enableButton.classList.add('hidden');
        }
    }

    document.addEventListener('click', async (event) => {
        const target = event.target.closest('[data-notification-id]');
        if (!target) {
            return;
        }

        const id = target.getAttribute('data-notification-id');
        if (!id) {
            return;
        }

        try {
            await markNotificationRead(id);
        } catch {
            // Ignore read failures; the destination page still opens.
        }
    });

    window.addEventListener('workplace:onboarding', (event) => {
        const detail = event.detail || {};
        const employee = detail.employee && detail.employee.name ? `${detail.employee.name} — ` : '';
        showWorkplaceToast('Onboarding updated', `${employee}${detail.status || ''}`);
    });

    window.addEventListener('workplace:offboarding', (event) => {
        const detail = event.detail || {};
        const employee = detail.employee && detail.employee.name ? `${detail.employee.name} — ` : '';
        showWorkplaceToast('Offboarding updated', `${employee}${detail.status || ''}`);
    });

    pollUnreadNotifications();
    window.setInterval(pollUnreadNotifications, 15000);
}

document.addEventListener('DOMContentLoaded', bindNotificationUi);
