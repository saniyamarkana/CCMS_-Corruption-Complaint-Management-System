/**
 * CCMS — Shared Main JS (Enhanced)
 * Works across all pages: sidebar, theme, search, forms, delete, PDF, email
 */
$(document).ready(function () {
  'use strict';

  /* ── THEME: restore on page load ───────────────── */
  const savedTheme = localStorage.getItem('ccms_theme') || 'light';
  document.documentElement.setAttribute('data-bs-theme', savedTheme);
  updateThemeIcon(savedTheme);

  function updateThemeIcon(t) {
    $('#themeIcon').toggleClass('fa-sun', t === 'dark').toggleClass('fa-moon', t !== 'dark');
  }

  $('#themeToggle').on('click', function () {
    const cur  = document.documentElement.getAttribute('data-bs-theme');
    const next = cur === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', next);
    localStorage.setItem('ccms_theme', next);
    updateThemeIcon(next);
    showToast(`${next.charAt(0).toUpperCase() + next.slice(1)} mode activated`);
  });

  /* ── SIDEBAR TOGGLE ─────────────────────────────── */
  $('#sidebarToggle').on('click', function () {
    $('#sidebar').toggleClass('collapsed');
    $('#main-content').toggleClass('expanded');
    localStorage.setItem('ccms_sb_collapsed', $('#sidebar').hasClass('collapsed'));
  });

  // Restore sidebar state
  if (localStorage.getItem('ccms_sb_collapsed') === 'true') {
    $('#sidebar').addClass('collapsed');
    $('#main-content').addClass('expanded');
  }

  // Mobile sidebar
  $('#mobileSidebarToggle').on('click', function () {
    $('#sidebar').toggleClass('mobile-open');
  });

  /* ── ANIMATED COUNTERS ──────────────────────────── */
  $('.counter-value, .kpi-value[data-count]').each(function () {
    const el  = $(this);
    const end = parseInt(el.text().replace(/,/g, ''), 10);
    if (isNaN(end)) return;
    $({ n: 0 }).animate({ n: end }, {
      duration: 1500,
      easing: 'swing',
      step(v) { el.text(Math.floor(v).toLocaleString()); },
      complete() { el.text(end.toLocaleString()); }
    });
  });

  /* ── TABLE SEARCH ───────────────────────────────── */
  $('#tableSearch').on('keyup', function () {
    const v = $(this).val().toLowerCase().trim();

    $('.tbl tbody tr:not(.no-res)').each(function () {
      $(this).toggle($(this).text().toLowerCase().includes(v));
    });

    $('.tbl tbody .no-res').remove();

    if (v) {
      const visible = $('.tbl tbody tr:not(.no-res):visible').length;
      if (!visible) {
        const cols = $('.tbl thead th').length || 8;
        $('.tbl tbody').append(
          `<tr class="no-res"><td colspan="${cols}" class="text-center py-5" style="color:var(--text-3);">
            <i class="fa-solid fa-folder-open d-block mb-2" style="font-size:2rem;color:#818cf8;"></i>
            No results for <strong>"${v}"</strong>
          </td></tr>`
        );
      }
    }
  });

  /* ── TABLE SORT (click any TH) ──────────────────── */
  $(document).on('click', '.tbl thead th', function () {
    const idx = $(this).index();
    const asc = !$(this).data('asc');
    $(this).data('asc', asc);
    // Visual caret
    $('.tbl thead th').html(function (_, h) { return h.replace(/ ▲| ▼/g, ''); });
    $(this).append(asc ? ' ▲' : ' ▼');

    const rows = $('.tbl tbody tr:not(.no-res)').toArray().sort((a, b) => {
      const va = $(a).find('td').eq(idx).text().trim();
      const vb = $(b).find('td').eq(idx).text().trim();
      return ($.isNumeric(va) && $.isNumeric(vb))
        ? (asc ? va - vb : vb - va)
        : (asc ? va.localeCompare(vb) : vb.localeCompare(va));
    });

    $('.tbl tbody').append(rows);
  });

  /* ── AJAX FORM SUBMISSION SIMULATION ───────────── */
  $(document).on('submit', 'form.ajax-form', function (e) {
    e.preventDefault();
    const form    = $(this);
    const modalEl = form.closest('.modal');

    // Validate required fields
    let valid = true;
    form.find('[required]').each(function () {
      if (!$(this).val().trim()) {
        $(this).addClass('is-invalid').removeClass('is-valid');
        valid = false;
      } else {
        $(this).addClass('is-valid').removeClass('is-invalid');
      }
    });
    if (!valid) return;

    Swal.fire({
      title: 'Saving Record…',
      html: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" style="width:2.5rem;height:2.5rem;"></div></div>',
      showConfirmButton: false,
      allowOutsideClick: false
    });

    setTimeout(() => {
      if (modalEl.length) modalEl.modal('hide');
      form[0].reset();
      form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');

      Swal.fire({
        icon: 'success',
        title: 'Saved Successfully',
        text: 'The record has been processed.',
        timer: 2000,
        showConfirmButton: false,
        confirmButtonColor: '#4f46e5'
      });
    }, 900);
  });

  /* ── DELETE CONFIRMATION ─────────────────────── */
  $(document).on('click', '.btn-delete', function (e) {
    e.preventDefault();
    const item = $(this).data('item') || 'record';
    const row  = $(this).closest('tr');

    Swal.fire({
      icon: 'warning',
      title: 'Delete this ' + item + '?',
      text: 'This action is permanent and cannot be undone.',
      showCancelButton: true,
      confirmButtonColor: '#f43f5e',
      cancelButtonColor: '#64748b',
      confirmButtonText: '<i class="fa-solid fa-trash me-1"></i> Yes, Delete'
    }).then(result => {
      if (result.isConfirmed) {
        row.fadeOut(350, function () { $(this).remove(); });
        Swal.fire({ icon: 'success', title: 'Deleted', timer: 1500, showConfirmButton: false });
      }
    });
  });

  /* ── PDF EXPORT SIMULATION ──────────────────── */
  $(document).on('click', '.btn-export-pdf', function (e) {
    e.preventDefault();
    const doc = $(this).data('doc-name') || 'CCMS_Official_Report';

    Swal.fire({
      title: 'Generating PDF…',
      html: `Rendering <b>${doc}</b> via <em>Dompdf / TCPDF Engine</em>`,
      timer: 1700,
      timerProgressBar: true,
      didOpen: () => Swal.showLoading()
    }).then(() => {
      Swal.fire({
        icon: 'success',
        title: 'PDF Ready!',
        text: `${doc}.pdf has been generated.`,
        confirmButtonColor: '#4f46e5',
        timer: 2500
      });
    });
  });

  /* ── PHPMAILER EMAIL SIMULATION ─────────────── */
  $(document).on('click', '.btn-send-email', function (e) {
    e.preventDefault();
    const to = $(this).data('email') || 'officer@acc.gov.bd';

    Swal.fire({
      title: 'Dispatching Email…',
      html: `Sending notification via <b>PHPMailer</b> to <code>${to}</code>`,
      timer: 1500,
      timerProgressBar: true,
      didOpen: () => Swal.showLoading()
    }).then(() => {
      showToast('Email notification delivered!', 'success');
    });
  });

  /* ── STATUS BADGE UPDATE SIMULATION ─────────── */
  $(document).on('click', '.btn-update-status', function () {
    const $row   = $(this).closest('tr');
    const select = $(this).closest('.modal').find('#newStatus');
    if (!select.val()) return;
    Swal.fire({
      icon: 'success', title: 'Status Updated',
      text: `Case status changed to: ${select.val()}`,
      timer: 1800, showConfirmButton: false
    });
  });

  /* ── TOOLTIPS ────────────────────────────────── */
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
  });

  /* ── TOAST HELPER ────────────────────────────── */
  function showToast(msg, icon = 'info') {
    Swal.mixin({
      toast: true, position: 'top-end',
      showConfirmButton: false,
      timer: 2000, timerProgressBar: true
    }).fire({ icon, title: msg });
  }

});
