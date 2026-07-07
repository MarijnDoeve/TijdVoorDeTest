import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.min.css';
import './styles/backoffice.scss';
import {session as turboSession} from '@hotwired/turbo';
turboSession.drive = false;
import './stimulus.js';
import './bootstrap.js';
import * as Sentry from '@sentry/browser';

const dsn = document.querySelector('meta[name="sentry-dsn"]')?.content ?? '';
const userEmail = document.querySelector('meta[name="user-email"]')?.content ?? '';

// When no real DSN is configured, route to the local Spotlight sidecar so
// nothing reaches Sentry. A syntactically valid DSN is still required for the
// SDK to initialise; the tunnel option redirects all transport to Spotlight.
const useSpotlight = !dsn;
const effectiveDsn = dsn || 'https://0@o0.ingest.sentry.io/0';

const feedbackIntegration = Sentry.feedbackIntegration({
    colorScheme: 'system',
    showName: false,
    showEmail: true,
    isEmailRequired: false,
    autoInject: false,
    triggerLabel: 'Report feedback',
    formTitle: 'Report Feedback',
    submitButtonLabel: 'Send Feedback',
});

Sentry.init({
    dsn: effectiveDsn,
    tunnel: useSpotlight ? 'http://localhost:8969/stream' : undefined,
    integrations: [feedbackIntegration],
});

// autoInject is unreliable in Sentry v10 due to the setupOnce guard; mount manually.
feedbackIntegration.createWidget();

if (userEmail) {
    Sentry.setUser({ email: userEmail });
}
