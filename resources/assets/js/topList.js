$(window).on('load', function () {

    // Active First Tab User Rank List
    const firstServer = $('.server-rank:first-child');
    firstServer.addClass('active');
    getTopLevelUserList(firstServer.data('url'),firstServer.data('servername'));

    // Active Tab User Rank List
    $('.server-rank').click(function (e) {
        e.preventDefault();
        $('.server-rank').removeClass('active');
        $('#rank-loader').removeClass('active');
        $(this).addClass('active');
        let serverName = $(this).data('servername');
        getTopLevelUserList($(this).data('url'),serverName);
    });

    //  PAGINATION USING AJAX
    $(document).on('click', 'ul.pagination li a', function (e) {
        e.preventDefault();
        $('#rank-loader').removeClass('active');
        const page = ($(this).attr('href').split('page=')[1]);
        if ($('.server-rank').hasClass('active')) {
            const server = $('.server-rank.active');
            getTopLevelUserList($(server).data('url'),server.data('servername'),page);
        }
    });
});
//  Ajax Get Top User Rank List
function getTopLevelUserList(action,serverName,page = 1) {
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
// Loader
function  HideLoader () {
    setTimeout(function() {
        $('#rank-loader').addClass('active');
    }, 500);
}

