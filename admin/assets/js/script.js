// ========================================
// University Attendance System - Main JS
// ========================================

$(function() {

  // ---- Sidebar Toggle ----
  const $menuBtn = $('#menu-btn');
  const $sidebar = $('#sidebar');
  const $overlay = $('<div class="sidebar-overlay" id="sidebarOverlay"></div>').appendTo('body');

  $menuBtn.on('click', function() {
    $sidebar.toggleClass('active');
    $overlay.toggleClass('show');
  });

  $overlay.on('click', function() {
    $sidebar.removeClass('active');
    $overlay.removeClass('show');
  });

  $(window).on('resize', function() {
    if ($(window).width() > 900) {
      $sidebar.removeClass('active');
      $overlay.removeClass('show');
    }
  });

  // ---- Active Menu Highlighting ----
  const currentPath = window.location.pathname.split('/').pop().split('?')[0];
  $('.sidebar ul li a').each(function() {
    const href = $(this).attr('href').split('?')[0];
    if (href === currentPath) $(this).addClass('active');
  });

  // ---- Fade In Animation ----
  $('.main-content, .form-box').addClass('fade-in');

  // ---- Auto-hide Alerts ----
  setTimeout(function() {
    $('.msg').fadeOut(500, function() { $(this).remove(); });
  }, 5000);

  // ---- DataTables Init ----
  if ($.fn.DataTable && $('.datatable').length) {
    $('.datatable').DataTable({
      pageLength: 25,
      lengthMenu: [10, 25, 50, 100],
      language: { search: '', searchPlaceholder: 'Search...' },
      dom: '<"table-top"f>rt<"table-bottom"lip>'
    });
  }

  // ---- SweetAlert2 Delete Confirmations ----
  $(document).on('click', '.btn-delete', function(e) {
    e.preventDefault();
    const $form = $(this).closest('form');
    const item = $(this).data('item') || 'this item';
    Swal.fire({
      title: 'Are you sure?',
      text: 'Delete ' + item + '? This cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e53935',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete!',
      cancelButtonText: 'Cancel'
    }).then(function(result) {
      if (result.isConfirmed) $form.trigger('submit');
    });
  });

  // ---- Password Visibility Toggle ----
  $(document).on('click', '.toggle-password', function() {
    const $input = $(this).closest('.password-wrap').find('input');
    const $icon = $(this).find('i');
    if ($input.attr('type') === 'password') {
      $input.attr('type', 'text');
      $icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
      $input.attr('type', 'password');
      $icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
  });

  // ---- Form Validation Enhancement ----
  $(document).on('submit', 'form', function() {
    let valid = true;
    $(this).find('[required]').each(function() {
      if (!$(this).val()) {
        $(this).css('border-color', '#e53935');
        valid = false;
      } else {
        $(this).css('border-color', '');
      }
    });
    return valid;
  });

  $(document).on('input', '[required]', function() {
    $(this).css('border-color', $(this).val() ? '' : '#e53935');
  });

});
