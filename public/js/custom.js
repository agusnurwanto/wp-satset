var appMaster = {
    /* --------------------------------------------

Page Loader

-------------------------------------------- */


    pageLoader: function() {

        jQuery(".loader-item")
            .delay(700)
            .fadeOut();

        jQuery("#pageloader")
            .delay(800)
            .fadeOut("slow");

    },


    /* --------------------------------------------

Carousel-slider

-------------------------------------------- */

    simplecarousel: function() {

        if ( !jQuery(".owl-demo").length || typeof jQuery.fn.owlCarousel !== 'function' ) return;

        jQuery(".owl-demo")
            .owlCarousel({

                items: 1,
                itemsDesktop: [1199, 1],
                itemsDesktopSmall: [991, 1],
                itemsTablet: [768, 1],
                itemsTabletSmall: false,
                itemsMobile: [479, 1],

                lazyLoad: true,

                autoPlay: false,

                navigation: false

            });

    },



    /* --------------------------------------------

	Animated Items

-------------------------------------------- */

    simpleAnimation: function() {


        jQuery('.animated')
            .appear(function() {

                var elem = jQuery(this);

                var animation = elem.data('animation');

                if (!elem.hasClass('visible')) {

                    var animationDelay = elem.data(
                        'animation-delay');

                    if (animationDelay) {



                        setTimeout(function() {

                            elem.addClass(animation +
                                " visible");

                        }, animationDelay);



                    } else {

                        elem.addClass(animation + " visible");

                    }

                }

            });



        /* --------------------------------------------

	Load More 

-------------------------------------------- */

        var loadtext = jQuery('.load-more');

        jQuery(".load-posts")
            .click(function() {

                if (jQuery(this)
                    .hasClass('disable')) return false;



                jQuery(this)
                    .html(
                        '<i class="fa fa-spin fa-spinner"></i> Loading'
                    );



                var $hidden = loadtext.filter(':hidden:first')
                    .delay(600);



                if (!$hidden.next('.load-more')
                    .length) {

                    $hidden.fadeIn(500);

                    jQuery(this)
                        .addClass('disable');

                    jQuery(this)
                        .fadeTo("slow", 0.23) /*.delay(600)*/

                    .queue(function(n) {

                            jQuery(this)
                                .html('All Posts Loaded');

                            n();

                        })
                        .fadeTo("slow", 1);



                } else {

                    $hidden.fadeIn(500);

                    jQuery(this)
                        .fadeTo("slow", 0.23) /*.delay(600)*/

                    .queue(function(g) {

                            jQuery(this)
                                .html(
                                    'Load More Post <i class="flaticon-arrow209">'
                                );

                            g();

                        })
                        .fadeTo("slow", 1);

                }

            });

    },



    /* --------------------------------------------

 Scroll Navigation

-------------------------------------------- */

    smoothScroll: function() {


        jQuery('.scroll')
            .bind('click', function(event) {

                var $anchor = jQuery(this);

                var headerH = jQuery('#navigation')
                    .outerHeight();

                jQuery('html, body')
                    .stop()
                    .animate({

                        scrollTop: jQuery($anchor.attr('href'))
                            .offset()
                            .top + -69 + "px"

                    }, 1200, 'easeInOutExpo');



                event.preventDefault();

            });

        /* --------------------------------------------

 Active Navigation

-------------------------------------------- */


        jQuery('body')
            .scrollspy({

                target: '#topnav',

                offset: 95

            })


        // Scroll Links

        jQuery('.page-scroll a')
            .bind('click', function(event) {
                var $anchor = jQuery(this);
                jQuery('html, body')
                    .stop()
                    .animate({
                        scrollTop: jQuery($anchor.attr('href'))
                            .offset()
                            .top
                    }, 1500, 'easeInOutExpo');
                event.preventDefault();
            });

    },


    /* --------------------------------------------

 Count Factors

-------------------------------------------- */

    funFact: function() {

        if ( !jQuery(".fact-number").length ) return;

        jQuery(".fact-number")
            .appear(function() {

                jQuery('.fact-number')
                    .each(function() {

                        dataperc = jQuery(this)
                            .attr('data-perc'),

                            jQuery(this)
                            .find('.factor')
                            .delay(6000)
                            .countTo({

                                from: 0,

                                decimals: 0,

                                to: dataperc,

                                speed: 3000,

                                refreshInterval: 50,

                            });

                    });

            });


        jQuery.fn.countTo = function(options) {

            // merge the default plugin settings with the custom options

            options = jQuery.extend({}, jQuery.fn.countTo.defaults, options || {});



            // how many times to update the value, and how much to increment the value on each update

            var loops = Math.ceil(options.speed / options.refreshInterval),

                increment = (options.to - options.from) / loops;



            return jQuery(this)
                .each(function() {

                    var _this = this,

                        loopCount = 0,

                        value = options.from,

                        interval = setInterval(updateTimer,
                            options.refreshInterval);



                    function updateTimer() {

                        value += increment;

                        loopCount++;

                        jQuery(_this)
                            .html(value.toFixed(options.decimals));



                        if (typeof(options.onUpdate) ==
                            'function') {

                            options.onUpdate.call(_this,
                                value);

                        }



                        if (loopCount >= loops) {

                            clearInterval(interval);

                            value = options.to;



                            if (typeof(options.onComplete) ==
                                'function') {

                                options.onComplete.call(
                                    _this, value);

                            }

                        }

                    }

                });

        };



        jQuery.fn.countTo.defaults = {

            from: 0, // the number the element should start at

            to: 100, // the number the element should end at

            speed: 1000, // how long it should take to count between the target numbers

            refreshInterval: 100, // how often the element should be updated

            decimals: 0, // the number of decimal places to show

            onUpdate: null, // callback method for every time the element is updated,

            onComplete: null, // callback method for when the element finishes updating

        };


    },

    /* --------------------------------------------

Header Screen Slider

-------------------------------------------- */


    mobileSlider: function() {

        if ( !jQuery("#mobileslider").length || typeof jQuery.fn.owlCarousel !== 'function' ) return;

        jQuery("#mobileslider")
            .owlCarousel({
                items: 1,
                slideSpeed: 1000,
                paginationSpeed: 1000,
                lazyLoad: true,
                autoPlay: true,
                navigation: true,
                itemsCustom: false,
                pagination: false,
                itemsDesktop: [1199, 1],
                itemsDesktopSmall: [991, 1],
                itemsTablet: [768, 1],
                itemsTabletSmall: false,
                itemsMobile: [479, 1]
            });
    },


    /* --------------------------------------------

Screenshot Scripts

-------------------------------------------- */


    screenShot: function() {

        if ( !jQuery("#screenshot").length || typeof jQuery.fn.owlCarousel !== 'function' ) return;
        jQuery("#screenshot")
            .owlCarousel({
                items: 2, /* item per page */
                lazyLoad: true,
                autoPlay: false,
                navigation: false,
                itemsCustom: false,
                itemsDesktop: [1199, 4],
                itemsDesktopSmall: [991, 3],
                itemsTablet: [768, 2],
                itemsTabletSmall: false,
                itemsMobile: [479, 1]
            });
    },


    /* --------------------------------------------

Testimonials Scripts

-------------------------------------------- */


    clientFeedback: function() {

        if ( !jQuery("#feedback").length || typeof jQuery.fn.owlCarousel !== 'function' ) return;
        
        jQuery("#feedback")
            .owlCarousel({
                items: 1,
                lazyLoad: true,
                autoPlay: true,
                navigation: true,
                itemsCustom: false,
                itemsDesktop: [1199, 1],
                itemsDesktopSmall: [991, 1],
                itemsTablet: [768, 1],
                itemsTabletSmall: false,
                itemsMobile: [479, 1]
            });

    },


    /* --------------------------------------------

 Pretty Photo

-------------------------------------------- */



    simplePopupphoto: function() {

        if ( !jQuery("a[data-rel^='prettyPhoto']").length || typeof jQuery.fn.owlCarousel !== 'function' ) return;
        
        jQuery("a[data-rel^='prettyPhoto']")
            .prettyPhoto({

                theme: "dark_square",
				hook: 'data-rel'

            });


    },


    // Menus hide after click --  mobile devices

    simpleNav: function() {

        jQuery('.nav li a.scroll')
            .click(function() {

                jQuery('.navbar-collapse')
                    .removeClass('in');

            });


        /* --------------------------------------------
	
	Fixed Menu on Scroll
	
	-------------------------------------------- */
        if ( !jQuery("#navigation").length || typeof jQuery.fn.owlCarousel !== 'function' ) return;

        jQuery("#navigation")
            .sticky({
                topSpacing: 0
            });


    },
    /* --------------------------------------------

Portfolio Scripts

-------------------------------------------- */

    simplePortfolio: function() {

        if (jQuery('#portfolio-wrapper')
            .length) {

            jQuery('#portfolio-wrapper')
                .mixItUp();

        }



        //expander

        var loader = jQuery('.item-expander');

        if (typeof loader.html() == 'undefined') {

            jQuery(
                    '<div class="item-expander"><div id="item-expander" class="container clearfix relative"><p class="cls-btn"><a class="close">X</a></p><div/></div></div>'
                )
                .css({
                    opacity: 0
                })
                .hide()
                .insertAfter('.portfolio');

            loader = jQuery('.item-expander');

        }

        jQuery('.expander')
            .on('click', function(e) {

                e.preventDefault();

                e.stopPropagation();

                var url = jQuery(this)
                    .attr('href');

                loader.slideUp(function() {

                    $.get(url, function(data) {

                        var portfolioContainer = jQuery(
                            '.portfolio');

                        var topPosition =
                            portfolioContainer.offset();

                        var bottomPosition =
                            topPosition +
                            portfolioContainer.height();

                        jQuery('html,body')
                            .delay(600)
                            .animate({
                                scrollTop: bottomPosition -
                                    -10
                            }, 800);

                        var container = jQuery(
                            '#item-expander>div',
                            loader);



                        container.html(data);

                        jQuery(".fit-vids")
                            .fitVids();

                        jQuery('.project')
                            .flexslider({

                                animation: "fade",

                                selector: ".project-slides .slide",

                                controlNav: true,

                                directionNav: true,

                                slideshowSpeed: 5000,

                            });



                        //container.fitVids();

                        loader.slideDown(function() {

                                if (typeof keepVideoRatio ==
                                    'function') {

                                    keepVideoRatio
                                        (
                                            '.project-video > iframe'
                                        );

                                }

                            })
                            .delay(1000)
                            .animate({
                                opacity: 1
                            }, 200);

                    });

                });

            });

        jQuery('.close', loader)
            .on('click', function() {

                loader.delay(300)
                    .slideUp(function() {

                        var container = jQuery('#item-expander>div',
                            loader);

                        container.html('');

                        jQuery(this)
                            .css({
                                opacity: 0
                            });



                    });

                var portfolioContainer = jQuery('.portfolio');

                var topPosition = portfolioContainer.offset()
                    .top;

                jQuery('html,body')
                    .delay(0)
                    .animate({
                        scrollTop: topPosition - 70
                    }, 500);

            });


    },

    /* --------------------------------------------

 Overlay 

-------------------------------------------- */
    simpleOverlay: function() {

        if ( typeof Modernizr != 'undefined' && Modernizr && Modernizr.touch) {

            // show the close overlay button

            jQuery(".close-overlay")
                .removeClass("hidden");

            // handle the adding of hover class when clicked

            jQuery(".img")
                .click(function(e) {

                    if (!jQuery(this)
                        .hasClass("hover")) {

                        jQuery(this)
                            .addClass("hover");

                    }

                });

            // handle the closing of the overlay

            jQuery(".close-overlay")
                .click(function(e) {

                    e.preventDefault();

                    e.stopPropagation();

                    if (jQuery(this)
                        .closest(".img")
                        .hasClass("hover")) {

                        jQuery(this)
                            .closest(".img")
                            .removeClass("hover");

                    }

                });

        } else {

            // handle the mouseenter functionality

            jQuery(".img")
                .mouseenter(function() {

                    jQuery(this)
                        .addClass("hover");

                })

            // handle the mouseleave functionality

            .mouseleave(function() {

                jQuery(this)
                    .removeClass("hover");

            });

        }

    },



    /* -------------------------------------------- 

 Blog Flex Slider

-------------------------------------------- */

    flexSlider: function() {
        
        if ( !jQuery(".flexslider").length || typeof jQuery.fn.owlCarousel !== 'function' ) return;

        jQuery('.flexslider')
            .flexslider({

                animation: 'fade',

                slideshow: false,

                animationLoop: false,

                controlNav: false

            });
    }
};


jQuery(document).ready(function() {

        appMaster.pageLoader();

        appMaster.simplecarousel();

        appMaster.simpleAnimation();

        appMaster.smoothScroll();

        appMaster.funFact();

        appMaster.mobileSlider();

        appMaster.screenShot();

        appMaster.clientFeedback();

        appMaster.simplePopupphoto();

        appMaster.simpleNav();

        appMaster.simplePortfolio();

        appMaster.flexSlider();

        appMaster.simpleOverlay();
});