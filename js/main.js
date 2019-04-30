let failedAttempts = 0;
let activeStars = {};
let planetRelativeCoords = {};
function updateServersInfo() {
    $.ajax({
        url: '/scripts/get-servers-info.php',
        type: 'post',
        dataType: 'json',
        success: function(response) {
            failedAttempts = 0;
            activeStars = [];
            for (var k in response) {
                if (typeof(activeStars[response[k]['host']]) == 'undefined') {
                    activeStars[response[k]['host']] = response[k];
                }
            }
            showStatus(activeStars);
        },
        error: function() {
            failedAttempts++;
            if (failedAttempts > 10) {
                showDisaster(activeStars);
            }
        }
    });
}

function showStatus(stars) {
    $('.star').removeClass('blink').hide();
    for (var k in stars) {
        if ($('.star.star-' + stars[k].host).length > 0) {
            $('.star.star-' + stars[k].host).show();
        } else {
            if (typeof (planetRelativeCoords[stars[k].host]) != 'undefined') {
                $('#map .image').append('<div class="star star-' + stars[k].host + '" style="'
                    + 'left: ' + planetRelativeCoords[stars[k].host].x + '%;'
                    + 'top: ' + planetRelativeCoords[stars[k].host].y + '%;'
                    + '"></div>');
            }
        }
        if ($('.star.star-' + stars[k].host).length > 0) {
            if (stars[k].status == 'OK') {
                $('.star.star-' + stars[k].host).css('background-color', '#00AA18');
                $('.star.star-' + stars[k].host).attr('title', 'OK');
            } else if (stars[k].status == 'UNKNOWN') {
                $('.star.star-' + stars[k].host).css('background-color', '#4B4B4B');
                $('.star.star-' + stars[k].host).attr('title', 'Unknown status');
            } else {
                $('.star').addClass('blink');
                if (stars[k].priority == "1") {
                    $('.star.star-' + stars[k].host).css('background-color', '#0275FF');
                } else if (stars[k].priority == "2") {
                    $('.star.star-' + stars[k].host).css('background-color', '#FFF600');
                } else if (stars[k].priority == "3") {
                    $('.star.star-' + stars[k].host).css('background-color', '#ff5722');
                } else if (stars[k].priority == "4") {
                    $('.star.star-' + stars[k].host).css('background-color', '#FF0000');
                } else if (stars[k].priority == "5") {
                    $('.star.star-' + stars[k].host).css('background-color', '#810004');
                } else {
                    $('.star.star-' + stars[k].host).css('background-color', '#4B4B4B');
                }
                $('.star.star-' + stars[k].host).attr('title', stars[k].message);
            }
        }
    }
}

function showDisaster(stars) {
    $('.star').removeClass('blink').hide();
    for (var k in stars) {
        if ($('.star.star-' + stars[k].host).length > 0) {
            $('.star.star-' + stars[k].host).show();
        } else {
            if (typeof (planetRelativeCoords[stars[k].host]) != 'undefined') {
                $('#map .image').append('<div class="star star-' + stars[k].host + '" style="'
                    + 'left: ' + planetRelativeCoords[stars[k].host].x + '%;'
                    + 'top: ' + planetRelativeCoords[stars[k].host].y + '%;'
                    + '"></div>');
            }
        }
        if ($('.star.star-' + stars[k].host).length > 0) {
            $('.star.star-' + stars[k].host).css('background-color', '#ffffff');
        }
    }
}

function adjustMapSize() {
    let imageWidth = $('#map .image img').get(0).naturalWidth;
    let imageHeight = $('#map .image img').get(0).naturalHeight;
    let windowWidth = document.documentElement.clientWidth;
    let windowHeight = document.documentElement.clientHeight;
    let ratio = 1;
    ratio = Math.min(windowWidth / imageWidth, windowHeight / imageHeight);
    $('#map .image').css('width', Math.floor(imageWidth * ratio) + 'px');
    $('#map .image').css('height', Math.floor(imageHeight * ratio) + 'px');
}
$(document).ready(function(){
    updateServersInfo();
    setInterval(updateServersInfo, 5000);
    adjustMapSize();
});
$(window).resize(function(){
    adjustMapSize();
});