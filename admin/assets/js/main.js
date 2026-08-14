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

  /* ── TABLE SEARCH & MULTI-FILTER ───────────────── */
  function filterTableData() {
    const searchVal = $('#tableSearch').val()?.toLowerCase().trim() || '';
    const deptVal   = $('#deptFilter').val()?.toLowerCase().trim() || '';
    const statusVal = $('#statusFilter').val()?.toLowerCase().trim() || '';
    const prioVal   = $('#priorityFilter').val()?.toLowerCase().trim() || '';
    const typeVal   = $('#typeFilter').val()?.toLowerCase().trim() || '';

    $('.tbl tbody tr:not(.no-res)').each(function () {
      const rowText = $(this).text().toLowerCase();
      const matchesSearch = !searchVal || rowText.includes(searchVal);
      const matchesDept   = !deptVal   || deptVal === 'all'   || rowText.includes(deptVal);
      const matchesStatus = !statusVal || statusVal === 'all' || rowText.includes(statusVal);
      const matchesPrio   = !prioVal   || prioVal === 'all'   || rowText.includes(prioVal);
      const matchesType   = !typeVal   || typeVal === 'all'   || rowText.includes(typeVal);

      $(this).toggle(matchesSearch && matchesDept && matchesStatus && matchesPrio && matchesType);
    });

    $('.tbl tbody .no-res').remove();
    const visibleCount = $('.tbl tbody tr:not(.no-res):visible').length;
    if (!visibleCount) {
      const cols = $('.tbl thead th').length || 8;
      $('.tbl tbody').append(
        `<tr class="no-res"><td colspan="${cols}" class="text-center py-5" style="color:var(--text-3);">
          <i class="fa-solid fa-folder-open d-block mb-2" style="font-size:2rem;color:var(--indigo-400);"></i>
          No matching records found for active filters.
        </td></tr>`
      );
    }
  }

  $('#tableSearch').on('keyup', filterTableData);
  $('#deptFilter, #statusFilter, #priorityFilter, #typeFilter, #periodFilter, #roleFilter').on('change', filterTableData);

  $('#btnResetFilters, .btn-reset-filters').on('click', function (e) {
    e.preventDefault();
    $('#tableSearch').val('');
    $('#deptFilter, #statusFilter, #priorityFilter, #typeFilter, #periodFilter, #roleFilter').val('');
    filterTableData();
    showToast('Filters reset to default');
  });

  /* ── BULK SELECTION & ACTION BAR ────────────────── */
  $(document).on('change', '#selectAllRows, .select-all-checkbox', function () {
    const isChecked = $(this).is(':checked');
    $('.row-checkbox').prop('checked', isChecked);
    updateBulkActionBar();
  });

  $(document).on('change', '.row-checkbox', function () {
    const total = $('.row-checkbox').length;
    const checked = $('.row-checkbox:checked').length;
    $('#selectAllRows, .select-all-checkbox').prop('checked', total === checked && total > 0);
    updateBulkActionBar();
  });

  function updateBulkActionBar() {
    const count = $('.row-checkbox:checked').length;
    let bar = $('#bulkActionBar');
    if (!bar.length && count > 0) {
      $('body').append(`
        <div class="bulk-actions-bar show" id="bulkActionBar">
          <span class="bulk-count-badge" id="bulkCountBadge">${count} Selected</span>
          <button class="btn btn-sm btn-ghost" id="btnBulkExport"><i class="fa-solid fa-download"></i> Export Selected</button>
          <button class="btn btn-sm btn-ghost text-primary" id="btnBulkStatus"><i class="fa-solid fa-pen-to-square"></i> Change Status</button>
          <button class="btn btn-sm btn-danger-soft" id="btnBulkDelete"><i class="fa-solid fa-trash"></i> Delete Selected</button>
          <button class="btn btn-sm btn-icon btn-ghost" id="btnCancelBulk" title="Cancel selection"><i class="fa-solid fa-xmark"></i></button>
        </div>
      `);
    } else if (bar.length) {
      if (count > 0) {
        bar.addClass('show');
        $('#bulkCountBadge').text(`${count} Selected`);
      } else {
        bar.removeClass('show');
      }
    }
  }

  $(document).on('click', '#btnCancelBulk', function () {
    $('.row-checkbox, #selectAllRows, .select-all-checkbox').prop('checked', false);
    updateBulkActionBar();
  });

  $(document).on('click', '#btnBulkDelete', function () {
    const count = $('.row-checkbox:checked').length;
    Swal.fire({
      icon: 'warning',
      title: `Delete ${count} records?`,
      text: 'This action is permanent and cannot be undone.',
      showCancelButton: true,
      confirmButtonColor: '#f43f5e',
      confirmButtonText: 'Yes, Delete All'
    }).then(res => {
      if (res.isConfirmed) {
        $('.row-checkbox:checked').closest('tr').fadeOut(350, function () {
          $(this).remove();
          updateBulkActionBar();
        });
        Swal.fire({ icon: 'success', title: 'Deleted', text: `${count} records removed.`, timer: 1500, showConfirmButton: false });
      }
    });
  });

  $(document).on('click', '#btnBulkExport', function () {
    const count = $('.row-checkbox:checked').length;
    Swal.fire({
      icon: 'success',
      title: 'Bulk Export Ready',
      text: `Exported ${count} selected records to CSV dossier.`,
      confirmButtonColor: '#4f46e5'
    });
  });

  /* ── DRAG & DROP FILE ZONE ─────────────────────── */
  $(document).on('dragover', '.file-dropzone', function (e) {
    e.preventDefault();
    $(this).addClass('dragover');
  });

  $(document).on('dragleave drop', '.file-dropzone', function (e) {
    e.preventDefault();
    $(this).removeClass('dragover');
  });

  $(document).on('change', '.file-dropzone input[type="file"]', function () {
    const files = this.files;
    const parent = $(this).closest('.file-dropzone');
    if (files.length > 0) {
      const names = Array.from(files).map(f => f.name).join(', ');
      parent.find('.dropzone-text').html(`<strong>${files.length} file(s) selected:</strong> <br><span class="extra-small text-muted">${names}</span>`);
      parent.find('.file-dropzone-icon').html('<i class="fa-solid fa-file-circle-check text-success"></i>');
    }
  });

  /* ── TABLE SORT (click any TH) ──────────────────── */
  $(document).on('click', '.tbl thead th:not(.no-sort)', function () {
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
