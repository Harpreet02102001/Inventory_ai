// Import our CSS (Vite processes this)
import "../css/app.css";

// Bootstrap 5 JS — includes Popper.js for dropdowns, tooltips, modals
import * as bootstrap from "bootstrap";

// Make bootstrap available globally so inline onclick attributes can access it
window.bootstrap = bootstrap;

/**
 * Initialize all Bootstrap tooltips on the page.
 *
 * We use tooltips on the locked delete button (when a category has linked products)
 * to show the "Linked products" message seen in the Category Listing design.
 *
 * querySelectorAll('[data-bs-toggle="tooltip"]') finds every element with that
 * attribute and initializes a Bootstrap Tooltip instance on each one.
 */
function initTooltips() {
    const tooltipElements = document.querySelectorAll(
        '[data-bs-toggle="tooltip"]',
    );
    tooltipElements.forEach((el) => new bootstrap.Tooltip(el));
}

/**
 * Auto-dismiss flash messages after 4 seconds.
 *
 * Finds Bootstrap alerts with the class .alert-auto-dismiss and
 * uses Bootstrap's Alert component to fade them out automatically.
 * This matches the UX pattern in the designs where success messages disappear.
 */
function initAutoDismissAlerts() {
    const alerts = document.querySelectorAll(".alert-auto-dismiss");
    alerts.forEach((alert) => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 4000);
    });
}

/**
 * Sidebar toggle handler.
 *
 * Toggles the 'sidebar-collapsed' class on body, which triggers the CSS
 * transition defined in app.css to slide the sidebar in/out.
 * The sidebar button calls this function via onclick.
 */
function toggleSidebar() {
    document.body.classList.toggle("sidebar-collapsed");
}

// Expose globally for use in Blade onclick attributes
window.toggleSidebar = toggleSidebar;

// Run on DOM ready
document.addEventListener("DOMContentLoaded", function () {
    initTooltips();
    initAutoDismissAlerts();
});
