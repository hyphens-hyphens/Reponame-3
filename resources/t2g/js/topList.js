$(window).on('load', function () {

    const firstServer = '.server-rank:first-child';
    // Active First Sever
    $(firstServer).addClass('active');
    getTopList($(firstServer).data('url'),'phat-son');

    // Active Sever rank
    $('.server-rank').click(function (e) {
        e.preventDefault();
        $('.server-rank').removeClass('active');
        $('#rank-loader').removeClass('active');
        $(this).addClass('active');
        let serverName = $(this).data('servername');
        getTopList($(this).data('url'),serverName);
    });

    //  PAGINATION USING AJAX
    $(document).on('click', 'ul.pagination li a', function (e) {
        e.preventDefault();
        $('#rank-loader').removeClass('active');
        const page = ($(this).attr('href').split('page=')[1]);
        if ($('.server-rank').hasClass('active')) {
            const server = $('.server-rank.active');
            getTopList($(server).data('url'),server.data('servername'),page);
        }
    });
});
    //  AJAX GET TOP LIST USER
    function getTopList(action,serverName,page = 1) {
        $.ajax({
            type: 'GET',
            url: action,
            data: {
                page,
                serverName
            },
            success: function (response) {
                if (response != null) {
                    HideLoader();
                    $('.rank-list-content-ad').html(response);
                }
            },
            error: function () {
                $('#rank-loader').removeClass('active');
            }
        })
    }
    // LOADER HIDDEN
    function  HideLoader () {
        setTimeout(function() {
            $('#rank-loader').addClass('active');
        }, 500);
    }

