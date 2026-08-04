// ============================================================
// error-handler.js - Global JavaScript Error Handling
// Provides centralized error catching, logging, and user feedback
// Satisfies Milestone 4: Error Handling and Debugging (2 marks)
// ============================================================

(function () {
    'use strict';

    // --- Configuration ---
    const DEBUG_MODE = true; // Set to false in production

    // --- Toast Notification System for Error Feedback ---
    function showErrorToast(message, type = 'error') {
        try {
            // Remove existing toast if any
            const existingToast = document.getElementById('globalErrorToast');
            if (existingToast) existingToast.remove();

            const toast = document.createElement('div');
            toast.id = 'globalErrorToast';
            toast.style.cssText = `
                position: fixed; bottom: 30px; right: 30px; z-index: 99999;
                padding: 16px 24px; border-radius: 12px; max-width: 400px;
                color: #fff; font-family: 'Poppins', sans-serif; font-size: 0.9rem;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                transform: translateY(100px); opacity: 0;
                transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                display: flex; align-items: center; gap: 10px;
                background: ${type === 'error' ? '#e74c3c' : type === 'warning' ? '#f39c12' : '#27ae60'};
            `;

            const icon = type === 'error' ? '❌' : type === 'warning' ? '⚠️' : '✅';
            toast.innerHTML = `
                <span style="font-size:1.2rem;">${icon}</span>
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;color:#fff;cursor:pointer;margin-left:10px;font-size:1.1rem;">✕</button>
            `;

            document.body.appendChild(toast);

            // Animate in
            requestAnimationFrame(() => {
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
            });

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.style.transform = 'translateY(100px)';
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 400);
                }
            }, 5000);
        } catch (toastError) {
            // Fallback: if toast fails, use basic alert
            console.error('Toast notification failed:', toastError);
        }
    }

    // --- Global Error Handler ---
    // Catches uncaught errors across all pages
    window.onerror = function (message, source, lineno, colno, error) {
        console.error('[Global Error]', {
            message: message,
            source: source,
            line: lineno,
            column: colno,
            error: error
        });

        if (DEBUG_MODE) {
            showErrorToast('An unexpected error occurred. Please try again.', 'error');
        }

        // Return true to prevent default browser error handling
        return true;
    };

    // --- Unhandled Promise Rejection Handler ---
    // Catches failed promises (e.g., failed fetch calls)
    window.addEventListener('unhandledrejection', function (event) {
        console.error('[Unhandled Promise Rejection]', {
            reason: event.reason,
            promise: event.promise
        });

        showErrorToast('A network error occurred. Please check your connection.', 'warning');

        // Prevent default handling
        event.preventDefault();
    });

    // --- Safe Fetch Wrapper ---
    // Wraps native fetch with error handling and logging
    window.safeFetch = async function (url, options = {}) {
        const startTime = performance.now();
        console.log(`[Fetch] Starting request to: ${url}`);

        try {
            const response = await fetch(url, options);
            const elapsed = (performance.now() - startTime).toFixed(0);
            console.log(`[Fetch] Response from ${url}: ${response.status} (${elapsed}ms)`);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            return response;
        } catch (error) {
            const elapsed = (performance.now() - startTime).toFixed(0);
            console.error(`[Fetch Error] ${url} failed after ${elapsed}ms:`, error.message);

            // Show user-friendly error
            if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                showErrorToast('Unable to connect to the server. Please check your internet connection.', 'error');
            } else if (error.message.includes('HTTP 404')) {
                showErrorToast('The requested resource was not found.', 'warning');
            } else if (error.message.includes('HTTP 500')) {
                showErrorToast('A server error occurred. Please try again later.', 'error');
            } else {
                showErrorToast('Something went wrong. Please try again.', 'error');
            }

            throw error; // Re-throw for caller to handle
        }
    };

    // --- Form Validation Helper ---
    // Provides reusable client-side validation with visual feedback
    window.validateField = function (inputId, validationFn, errorMsg) {
        try {
            const input = document.getElementById(inputId);
            if (!input) {
                console.warn(`[Validation] Input #${inputId} not found.`);
                return true; // Skip validation if field doesn't exist
            }

            const value = input.value.trim();
            const isValid = validationFn(value);

            if (!isValid) {
                input.style.borderColor = '#e74c3c';
                input.style.boxShadow = '0 0 0 3px rgba(231, 76, 60, 0.15)';

                // Add or update error message below field
                let errEl = input.nextElementSibling;
                if (!errEl || !errEl.classList.contains('field-error')) {
                    errEl = document.createElement('p');
                    errEl.classList.add('field-error');
                    errEl.style.cssText = 'color:#e74c3c; font-size:0.78rem; margin:4px 0 8px; padding:0;';
                    input.parentNode.insertBefore(errEl, input.nextSibling);
                }
                errEl.textContent = errorMsg;

                console.log(`[Validation] Field #${inputId} failed: ${errorMsg}`);
                return false;
            } else {
                input.style.borderColor = '';
                input.style.boxShadow = '';

                // Remove existing error message
                const errEl = input.nextElementSibling;
                if (errEl && errEl.classList.contains('field-error')) {
                    errEl.remove();
                }
                return true;
            }
        } catch (validationError) {
            console.error('[Validation Error]', validationError);
            return true; // Don't block submission on validation errors
        }
    };

    // --- Console Debugging Utilities ---
    console.log('%c🔮 Aura Artifacts', 'font-size:20px; font-weight:bold; color:#D4AF37;');
    console.log('%c✅ Error handler loaded successfully', 'color:#27ae60;');
    console.log(`%c🔧 Debug mode: ${DEBUG_MODE ? 'ON' : 'OFF'}`, 'color:#3498db;');

    // Expose showErrorToast globally for use in inline scripts
    window.showErrorToast = showErrorToast;

})();
