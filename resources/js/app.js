import './bootstrap';
import './workplace-notifications';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

if (window.Echo) {
    window.Echo.private('workplace')
        .listen('.onboarding.status', (payload) => {
            window.dispatchEvent(new CustomEvent('workplace:onboarding', { detail: payload }));
        })
        .listen('.offboarding.status', (payload) => {
            window.dispatchEvent(new CustomEvent('workplace:offboarding', { detail: payload }));
        });
}
