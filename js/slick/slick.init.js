jQuery(document).ready(function($){
    $('.list-elements').slick({
        infinite: false,
        slidesToShow: 6,
        slidesToScroll: 1,
        arrows: true,
        draggable: true,
        prevArrow:'<button type="button" class="slick-prev"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#141211" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left"><polyline points="15 18 9 12 15 6"/></svg></button>',
        nextArrow:'<button type="button" class="slick-next"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#141211" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"/></svg></button>',
        responsive: [
            {
                breakpoint: 1504,
                settings: {
                    slidesToShow: 5,
                    slidesToScroll: 1,
                    infinite: true
                }
            },
            {
                breakpoint: 1400,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 1150,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            },
            {
                breakpoint: 530,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
    });
});