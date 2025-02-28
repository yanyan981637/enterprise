require([
    'jquery',
    'throttle'
], function ($, throttle) {
    let window_height = $(window).height();
    window_height = 200;

    const checkPosition = function () {
        const position_top = $(window).scrollTop()

        if(position_top === 0 ) {
            $("html").addClass("reach-top");
        }else {
            $("html").removeClass("reach-top");
        }

        if(position_top >= window_height) {
            $("html").addClass("not-first-screen")
        }else {
            $("html").removeClass("not-first-screen");
        }

    }

    const throttleCheckPosition = throttle(checkPosition, 100);

    $(document).ready(function () {
        throttleCheckPosition();

        $(window).scroll(throttleCheckPosition)
    })
})
