$( document ).ready(function() {
   
    $(function () {
        $(".header-nav .admin_notibox").on('click', function (e) {
            $(this).parent().toggleClass('open');
            // $('.user_box .dropdown').removeClass('dropdown_active');
            e.stopPropagation()
        });
        $(document).on("click", function (e) {
            if ($(e.target).is(".notify_dropdon") === false) {
                $(".admin_notibox").removeClass("open");
            }
        });
    });
});