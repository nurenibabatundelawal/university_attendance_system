$(document).ready(function() {
    let url = window.location.pathname;
    let page = url.substring(url.lastIndexOf('/')+1);
    let sidebarOverlay = $('<div class="sidebar-overlay"></div>');
    $('body').append(sidebarOverlay);

    $('#menu-btn').click(function() {
        $('#sidebar').toggleClass('active');
        sidebarOverlay.toggleClass('show');
    });

    sidebarOverlay.click(function() {
        $('#sidebar').removeClass('active');
        sidebarOverlay.removeClass('show');
    });

    $('.sidebar a').each(function() {
        let href = $(this).attr('href');
        if (href === page) $(this).addClass('active');
    });
});
