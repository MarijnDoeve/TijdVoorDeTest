import 'bootstrap/dist/css/bootstrap.min.css'
import * as bootstrap from 'bootstrap'

import './styles/quiz.scss'

document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the elimination candidate screen
    const eliminationScreen = document.querySelector('.elimination-screen');
    if (eliminationScreen) {
        // Add event listener for any keypress
        document.addEventListener('keydown', function(event) {
            // Get the current URL
            const currentUrl = window.location.href;
            // Extract the elimination ID from the URL
            const urlParts = currentUrl.split('/');
            // Remove the candidate hash (last part of the URL)
            urlParts.pop();
            // Construct the URL to the main elimination page
            const redirectUrl = urlParts.join('/');
            // Redirect to the main elimination page
            window.location.href = redirectUrl;
        });
    }
});
