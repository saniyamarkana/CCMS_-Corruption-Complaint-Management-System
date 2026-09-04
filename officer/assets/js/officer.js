/**
 * CCMS — Investigation Officer Portal Main JS
 */

// Officer Panel JS Utilities
document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  /* ── User Profile Sync & Logout ───────────────── */
  const currentUserStr = localStorage.getItem('ccms_current_user');
  if (currentUserStr) {
    try {
      const user = JSON.parse(currentUserStr);
      const nameEls = document.querySelectorAll('.nav-profile-name, .officer-name-display');
      nameEls.forEach(el => el.innerText = user.name || 'Inspector Chen');
      const badgeEls = document.querySelectorAll('.nav-profile-role, .officer-badge-display');
      badgeEls.forEach(el => el.innerText = user.badge || 'BADGE-8842');
    } catch (e) {}
  }

  // Handle logout links
  document.querySelectorAll('a[href*="login.php"]').forEach(btn => {
    btn.addEventListener('click', () => {
      localStorage.removeItem('ccms_current_user');
    });
  });

  /* ── Theme Handling ───────────────────────────── */
  const savedTheme = localStorage.getItem('ccms_theme') || 'light';
  document.documentElement.setAttribute('data-bs-theme', savedTheme);
  updateThemeIcons(savedTheme);

  function updateThemeIcons(theme) {
    const icon = document.getElementById('themeIcon');
    if (icon) {
      icon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
  }

  const themeToggleBtn = document.getElementById('themeToggle');
  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
      const current = document.documentElement.getAttribute('data-bs-theme');
      const next = current === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-bs-theme', next);
      localStorage.setItem('ccms_theme', next);
      updateThemeIcons(next);
    });
  }

  /* ── Sidebar Toggling ─────────────────────────── */
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('main-content');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const mobileToggle = document.getElementById('mobileSidebarToggle');

  if (sidebarToggle && sidebar && mainContent) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('expanded');
    });
  }

  if (mobileToggle && sidebar) {
    mobileToggle.addEventListener('click', () => {
      sidebar.classList.toggle('mobile-open');
    });
  }

  /* ── Animated Number Counters ─────────────────── */
  const counters = document.querySelectorAll('.counter-val');
  counters.forEach(counter => {
    const target = parseInt(counter.getAttribute('data-target') || counter.innerText.replace(/,/g, ''), 10);
    if (isNaN(target)) return;

    let start = 0;
    const duration = 1200;
    const stepTime = 20;
    const stepCount = duration / stepTime;
    const increment = target / stepCount;

    const timer = setInterval(() => {
      start += increment;
      if (start >= target) {
        counter.innerText = target.toLocaleString();
        clearInterval(timer);
      } else {
        counter.innerText = Math.floor(start).toLocaleString();
      }
    }, stepTime);
  });
});

function showOfficerToast(message, type = 'info') {
  if (typeof Swal !== 'undefined') {
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
    Toast.fire({
      icon: type,
      title: message
    });
  } else {
    alert(message);
  }
}
