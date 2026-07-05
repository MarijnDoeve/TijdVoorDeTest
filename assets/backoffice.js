import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.min.css';
import './styles/backoffice.scss';
import './stimulus.js';
import './bootstrap.js';
import * as Sentry from '@sentry/browser';

const dsn = document.querySelector('meta[name="sentry-dsn"]')?.content ?? '';
const userEmail = document.querySelector('meta[name="user-email"]')?.content ?? '';

Sentry.init({
    dsn: dsn || undefined,
    // When no DSN is set (local dev), forward events to Spotlight instead
    spotlight: !dsn,
    integrations: [
        Sentry.feedbackIntegration({
            colorScheme: 'system',
            showName: true,
            showEmail: true,
            isNameRequired: false,
            isEmailRequired: false,
        }),
    ],
});

if (userEmail) {
    Sentry.setUser({ email: userEmail });
}
