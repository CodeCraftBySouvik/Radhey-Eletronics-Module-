$(window).on('load', function () {
    setTimeout(function(){
        $('#app-preloader').addClass('fade');
    },1000);
});

$(".main__nav ul li a").each(function() {
    if ($(this).next().length > 0) {
        $(this).addClass("parent");
    };
});

$('.responsiveNav_Btn').click(function(){
    $(".main__nav").addClass('active');
    $("body").addClass('overlay_black');
});
// $(".main__nav > ul > li").unbind('mouseenter mouseleave');
// $(".main__nav > ul > li > a.parent").unbind('click').bind('click', function(e) {
//     // must be attached to anchor element to prevent bubbling
//     e.preventDefault();
//     if($(this).parent("li").hasClass("active")) {
//         $(this).parent("li").removeClass("active");
//         return;
//     }
//     $(".main__nav > ul > li").removeClass('active');
//     $(this).parent("li").parent().parent().addClass("active");
//     $(this).parent("li").toggleClass("active");
// });


var url = window.location;
$('.main__nav a[href="'+url+'"]').parent().addClass('active');
$('.main__nav a').filter(function(){
    return this.href==url;
}).parent().addClass('active');

$.fn.extend({
    equalizer: function() {
        var minHeight = 0;
        $(this).each(function() {
            if($(this).outerHeight() > minHeight) {
                minHeight = $(this).outerHeight();
            }
        });
        $(this).css('min-height', minHeight + 'px');
    }
});

$('.equal_height').equalizer();


$('.store_details_row').click(function() {
    $(this).next().find('.store_details').slideToggle();
    $('.store_details').not($(this).next().find('.store_details')).slideUp();
});

$('.delete_btn').click(function(event){
    event.stopPropagation();
});

$('.close_nav').click(function(){
    $(".main__nav").removeClass('active');
    $("body").removeClass('overlay_black');
});

$('.responsiveNav_Btn').click(function(e){
    e.stopPropagation();
});
$('.main__nav').click(function(e){
    e.stopPropagation();
});