// ============================================================
// js/main.js — Golden Girl Frontend JavaScript
// Handles: mobile nav toggle, password strength, form UX
// ============================================================

// Wait for the entire DOM to load before running any JS
document.addEventListener('DOMContentLoaded', function () {

    // ── Mobile Navigation Toggle ──────────────────────────────
    const navToggle = document.querySelector('.nav-toggle');
    const mainNav   = document.querySelector('#main-nav');

    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function () {
            // Toggle the 'open' class to show/hide the mobile menu
            const isOpen = mainNav.classList.toggle('open');

            // Update aria-expanded for accessibility screen readers
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Close the nav if user clicks anywhere outside of it
        document.addEventListener('click', function (e) {
            if (!navToggle.contains(e.target) && !mainNav.contains(e.target)) {
                mainNav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ── Password Strength Meter ───────────────────────────────
    // Checks complexity and provides real-time visual feedback
    const passwordInput = document.getElementById('password');
    const strengthFill  = document.querySelector('.strength-fill');
    const strengthText  = document.querySelector('.strength-text');

    if (passwordInput && strengthFill) {
        passwordInput.addEventListener('input', function () {
            const val = this.value;
            let score = 0; // Start with zero strength points

            // Award points for each complexity criterion met
            if (val.length >= 8)            score++; // Minimum length
            if (/[A-Z]/.test(val))          score++; // Uppercase letter
            if (/[0-9]/.test(val))          score++; // Number
            if (/[^A-Za-z0-9]/.test(val))  score++; // Special character

            // Map score to a label and color
            const levels = [
                { label: '',        color: '',          width: '0%'   },
                { label: 'Weak',    color: '#ef4444',   width: '25%'  },
                { label: 'Fair',    color: '#f97316',   width: '50%'  },
                { label: 'Good',    color: '#eab308',   width: '75%'  },
                { label: 'Strong',  color: '#22c55e',   width: '100%' },
            ];

            const level = levels[score] || levels[0];

            // Update the visual bar
            strengthFill.style.width      = val.length === 0 ? '0%' : level.width;
            strengthFill.style.background = level.color;

            // Update the text label
            if (strengthText) {
                strengthText.textContent = val.length === 0 ? '' : 'Strength: ' + level.label;
                strengthText.style.color  = level.color;
            }
        });
    }

    // ── Confirm Password Validation ───────────────────────────
    // Checks that both password fields match before form submit
    const confirmInput = document.getElementById('confirm_password');
    const registerForm = document.getElementById('register-form');

    if (registerForm && confirmInput && passwordInput) {
        // Live mismatch indicator
        confirmInput.addEventListener('input', function () {
            if (this.value && this.value !== passwordInput.value) {
                this.style.borderColor = '#ef4444'; // Red border on mismatch
            } else {
                this.style.borderColor = ''; // Reset to default
            }
        });

        // Prevent form submission if passwords don't match
        registerForm.addEventListener('submit', function (e) {
            if (passwordInput.value !== confirmInput.value) {
                e.preventDefault(); // Stop the form
                showAlert('Passwords do not match. Please try again.', 'error');
                confirmInput.focus(); // Bring focus to the problem field
            }
        });
    }

    // ── Alert Helper ──────────────────────────────────────────
    // Dynamically inserts an alert message at the top of a form
    function showAlert(message, type) {
        // Remove any existing alerts first
        const existing = document.querySelector('.alert-dynamic');
        if (existing) existing.remove();

        const alert = document.createElement('div');
        alert.className = 'alert alert-' + (type || 'info') + ' alert-dynamic';
        alert.setAttribute('role', 'alert'); // Accessibility: announces to screen readers
        alert.textContent = message;

        // Insert before the first form element
        const form = document.querySelector('form');
        if (form) form.insertBefore(alert, form.firstChild);
    }

    // ── Auto-dismiss Flash Messages ───────────────────────────
    // PHP flash messages fade away after a few seconds
    const flashMessages = document.querySelectorAll('.alert[data-auto-dismiss]');
    flashMessages.forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.5s ease'; // Smooth fade
            el.style.opacity    = '0';
            setTimeout(function () { el.remove(); }, 500); // Remove from DOM after fade
        }, 4000); // Show for 4 seconds
    });

    // ── Smooth scroll for anchor links ───────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ── Service selection on schedule page ───────────────────
    // Pre-check a service checkbox if passed via URL query string
    const urlParams = new URLSearchParams(window.location.search);
    const preService = urlParams.get('service'); // e.g. ?service=real-estate

    if (preService) {
        const checkbox = document.querySelector('input[value="' + preService + '"]');
        if (checkbox) {
            checkbox.checked = true; // Pre-select the matching checkbox
            // Scroll to the form smoothly
            const form = document.getElementById('service-form');
            if (form) form.scrollIntoView({ behavior: 'smooth' });
        }
    }

});
