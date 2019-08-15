var controller = new ScrollMagic.Controller();

$(".animate-up").each(function() {
    var animateUp = new ScrollMagic.Scene({
        triggerElement: this,
        triggerHook: .75
    }).setTween(TweenMax.to($(this), .75, {
        y: "0%",
        autoAlpha: 1,
        ease: Power3.easeOut
    })).addTo(controller);
});

$(".animate-stagger-up").each(function() {
    var animateStaggerUpTween = TweenMax.staggerTo($(this).children(), .75, {
        y: "0%",
        autoAlpha: 1,
        ease: Power3.easeOut
    }, .3);
    var animateStaggerUp = new ScrollMagic.Scene({
        triggerElement: this,
        triggerHook: .75
    }).setTween(animateStaggerUpTween).addTo(controller);
});

$(".animate-left").each(function() {
    var animateLeft = new ScrollMagic.Scene({
        triggerElement: this,
        triggerHook: .75
    }).setTween(TweenMax.to($(this), .75, {
        x: "0%",
        autoAlpha: 1,
        ease: Power3.easeOut
    })).addTo(controller);
});

$(".animate-right").each(function() {
    var animateRight = new ScrollMagic.Scene({
        triggerElement: this,
        triggerHook: .75
    }).setTween(TweenMax.to($(this), .75, {
        x: "0%",
        autoAlpha: 1,
        ease: Power3.easeOut
    })).addTo(controller);
});

$("[data-target]").click(function(e) {
    e.preventDefault();
    var $target = $(this).data("target"), diff = 0;
    if ($(".js-navbar-fixed").length > 0) {
        diff = $(".js-navbar-fixed").outerHeight(true);
    }
    TweenMax.to(window, 1, {
        scrollTo: {
            y: $target,
            offsetY: diff,
            autoKill: false
        },
        ease: Power2.easeInOut
    });
});

$(".parallax").each(function() {
    var animateParallaxTween = TweenMax.to($(this).children(".parallax__bg"), .75, {
        y: "25%",
        ease: Power0.easeOut
    });
    var animateParallax = new ScrollMagic.Scene({
        triggerElement: this,
        triggerHook: 0,
        duration: "100%"
    }).setTween(animateParallaxTween).addTo(controller);
});

function setItemHeight() {
    if (isMobile) {
        $(".js-normalize-item").height("");
        return false;
    }
    $(".js-normalize-item").height("");
    $(".js-normalize").each(function() {
        var maxHeight = 0;
        var normalizeItemNum = $(this).find(".js-normalize-item").length;
        for (var i = 0; i < normalizeItemNum; i++) {
            var h = $(this).find(".js-normalize-item").eq(i).height();
            if (h > maxHeight) {
                maxHeight = h;
            }
        }
        $(this).find(".js-normalize-item").height(maxHeight);
    });
}

function getMobileData() {
    if ($(".is-mobile").css("display") === "block") {
        isMobile = true;
    } else {
        isMobile = false;
    }
    if ($(".is-medium").css("display") === "block") {
        isMedium = true;
    } else {
        isMedium = false;
    }
    if ($(".is-smalldesk").css("display") === "block") {
        isSmalldesk = true;
    } else {
        isSmalldesk = false;
    }
}

var isTouch = false, isMobile = false, isMedium = false, isSmalldesk = false;

if (/Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/i.test(navigator.userAgent || navigator.vendor || window.opera)) {
    $("body").addClass("is-touch");
    isTouch = true;
}

$('[data-type="page-load"]').on("click", function(e) {
    var href = $(this).attr("href");
    var plainHref = href.split("#")[0];
    var plainLocation = window.location.href.split("#")[0];
    if (plainHref != plainLocation) {
        e.preventDefault();
        $("body").removeClass("loaded");
        setTimeout(function() {
            window.location.href = href;
        }, 500);
    }
});

$("div[href], span[href], tr[href], p[href], li[href]").click(function() {
    window.location.href = $(this).attr("href");
});

$("select.js-reload").change(function() {
    url = $(this).val();
    if (url && url != "") {
        if ($(this).attr("data-type") == "page-load") {
            $("body").removeClass("loaded");
            setTimeout(function() {
                window.location.href = url;
            }, 500);
        } else {
            window.location.href = url;
        }
    }
    return false;
});

window.onload = window.onpageshow = function() {
    if ($(".is-mobile").css("display") === "block") {
        isMobile = true;
    }
    setTimeout(function() {
        getMobileData();
        setItemHeight();
    }, 100);
    setTimeout(function() {
        $("body").addClass("loaded");
    }, 400);
};

var resizeTimer;

$(window).on("resize", function() {
    getMobileData();
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        setItemHeight();
    }, 1e3);
});

(function($, window, document, undefined) {
    var pluginName = "helllbox", defaults = {
        ease: "Power3.easeInOut"
    };
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this._settings = {
            $open: null,
            $img: null,
            img: null,
            $counter_max: null,
            $counter_current: null,
            $lightbox: null,
            lightboxIndex: null,
            lightboxSize: null,
            $currentImg: null,
            $nextImg: null
        };
        this.init();
    }
    Plugin.prototype = {
        init: function() {
            this._settings.$open = $(this.element).find(".js-open-lightbox");
            this._settings.$next = $(this.element).find(".js-lightbox-next");
            this._settings.$prev = $(this.element).find(".js-lightbox-prev");
            this._settings.$close = $(this.element).find(".js-close-lightbox");
            this._settings.$img = $(this.element).find(".js-lightbox .lightbox__img");
            this._settings.$counter_max = $(this.element).find(".js-lightbox-counter .max__img");
            this._settings.$counter_current = $(this.element).find(".js-lightbox-counter .current__img");
            this._settings.$lightbox = $("#" + this._settings.$open.data("lightbox"));
            this._settings.lightboxSize = parseInt(this._settings.$lightbox.children(".lightbox__img").length);
            this._settings.$counter_max.text(this._settings.lightboxSize);
            this.initSwipe();
            this.bindEvents();
        },
        bindEvents: function() {
            this._settings.$open.on("click", {
                self: this
            }, this.open);
            this._settings.$prev.on("click", {
                self: this
            }, this.prev);
            this._settings.$next.on("click", {
                self: this
            }, this.next);
            this._settings.$close.on("click", {
                self: this
            }, this.close);
            $(document).on("keydown", {
                self: this
            }, this.closeByKey);
            $(document).on("keydown", {
                self: this
            }, this.prevByKey);
            $(document).on("keydown", {
                self: this
            }, this.nextByKey);
        },
        open: function(e) {
            e.preventDefault();
            var self = e.data.self;
            self._settings.lightboxIndex = parseInt($(this).data("index"));
            self._settings.img = self._settings.$lightbox.children(".lightbox__img").eq(self._settings.lightboxIndex);
            self.updateCounterCurrent();
            TweenMax.set(self._settings.img, {
                autoAlpha: 1
            });
            TweenMax.to(self._settings.$lightbox, .35, {
                autoAlpha: 1,
                className: "+=is-open",
                ease: self.options.ease
            }, 0);
        },
        next: function(e) {
            e.preventDefault();
            var self = e.data.self;
            if (self._settings.lightboxIndex == self._settings.lightboxSize - 1) self._settings.lightboxIndex = -1;
            self._settings.lightboxIndex++;
            self.setCurrentImage("next");
            self.setPrevNextImage("next");
            TweenMax.to(self._settings.$currentImg, .5, {
                autoAlpha: 0,
                ease: self.options.ease
            }, 0);
            TweenMax.to(self._settings.$nextImg, .5, {
                autoAlpha: 1,
                ease: self.options.ease
            }, 0);
            self.updateCounterCurrent();
        },
        prev: function(e) {
            e.preventDefault();
            var self = e.data.self;
            if (self._settings.lightboxIndex === 0) self._settings.lightboxIndex = self._settings.lightboxSize;
            self._settings.lightboxIndex--;
            self.setCurrentImage("prev");
            self.setPrevNextImage("prev");
            TweenMax.to(self._settings.$currentImg, .5, {
                autoAlpha: 0,
                ease: self.options.ease
            }, 0);
            TweenMax.to(self._settings.$prevImg, .5, {
                autoAlpha: 1,
                ease: self.options.ease
            }, 0);
            self.updateCounterCurrent();
        },
        close: function(e) {
            e.preventDefault();
            var self = e.data.self;
            TweenMax.to(self._settings.$lightbox, .35, {
                autoAlpha: 0,
                className: "-=is-open",
                ease: self.options.ease
            }, 0);
            TweenMax.set(self._settings.$lightbox.children(".lightbox__img"), {
                autoAlpha: 0
            });
        },
        closeByKey: function(e) {
            var self = e.data.self;
            if (e.keyCode === 27 && self._settings.$lightbox !== null) {
                e.preventDefault();
                self.close(e);
            }
        },
        prevByKey: function(e) {
            var self = e.data.self;
            if (e.keyCode === 37 && self._settings.$lightbox !== null) {
                e.preventDefault();
                self.prev(e);
            }
        },
        nextByKey: function(e) {
            var self = e.data.self;
            if (e.keyCode === 39 && self._settings.$lightbox !== null) {
                e.preventDefault();
                self.next(e);
            }
        },
        setCurrentImage: function(direction) {
            var index;
            if (direction === "next") {
                index = this._settings.lightboxIndex - 1;
                if (index < 0) {
                    index = this._settings.lightboxSize - 1;
                }
            } else {
                index = this._settings.lightboxIndex + 1;
                if (index >= this._settings.lightboxSize) {
                    index = 0;
                }
            }
            this._settings.$currentImg = this._settings.$lightbox.children(".lightbox__img").eq(index);
        },
        setPrevNextImage: function(direction) {
            if (direction === "next") {
                this._settings.$nextImg = this._settings.$lightbox.children(".lightbox__img").eq(this._settings.lightboxIndex);
            } else {
                this._settings.$prevImg = this._settings.$lightbox.children(".lightbox__img").eq(this._settings.lightboxIndex);
            }
        },
        updateCounterCurrent: function() {
            this._settings.$counter_current.text(this._settings.lightboxIndex + 1);
        },
        initSwipe: function() {
            var self = this;
            $(this.element).swipe({
                swipe: function(event, direction, distance, duration, fingerCount, fingerData) {
                    if (direction === "left") {
                        self._settings.$next.trigger("click");
                    } else if (direction === "right") {
                        self._settings.$prev.trigger("click");
                    }
                },
                threshold: 75,
                allowPageScroll: "vertical",
                excludedElements: "button, input, select, textarea, .noSwipe"
            });
        }
    };
    $.fn[pluginName] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
    };
})(jQuery, window, document);

(function($, window, document, undefined) {
    var pluginName = "helllformValidator", defaults = {
        key: "value"
    };
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this._settings = {
            $form: null,
            $formFields: null,
            formURL: null,
            formValue: null
        };
        this.init();
    }
    Plugin.prototype = {
        init: function() {
            this._settings.$form = $(this.element);
            this._settings.$formFields = $(this.element).find(":input");
            this._settings.formURL = $(this.element).attr("action");
            this._settings.formValue = {};
            this.bindEvents();
            this.checkFields();
        },
        bindEvents: function() {},
        checkFields: function() {
            var self = this;
            $.each(self._settings.$formFields, function(index, el) {
                $(this).on("blur", function() {
                    self._settings.formValue["field"] = $(this).attr("name");
                    self._settings.formValue["value"] = $(this).val();
                    $.ajax({
                        type: "GET",
                        dataType: "json",
                        url: self._settings.formURL + "?ajax-validate-field=1",
                        data: self._settings.formValue,
                        success: function(response) {
                            var key = $(el).attr("name"), message = response[key], $el_wrapper = $(el).parents(".form__field"), $required_star = $el_wrapper.find(".is-required");
                            $el_error = $el_wrapper.find(".form__error");
                            if (response._valid != true) {
                                $(el).addClass("error");
                                $required_star.addClass("error");
                                if (key != message) {
                                    $el_error.addClass("form__error--show").text(message);
                                }
                            } else {
                                $(el).removeClass("error");
                                $(el).parent().removeClass("error");
                                $required_star.removeClass("error");
                                if (key != message) {
                                    $el_error.removeClass("form__error--show");
                                }
                            }
                        }
                    });
                });
            });
        }
    };
    $.fn[pluginName] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
    };
})(jQuery, window, document);

(function($, window, document, undefined) {
    var pluginName = "helllgrid", defaults = {
        ease: "Power3.easeInOut"
    };
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this._settings = {
            $gridContainer: null,
            $grid: null,
            $filter: null,
            $filterNav: null
        };
        this.init();
    }
    Plugin.prototype = {
        init: function() {
            this._settings.$gridContainer = $(this.element);
            this._settings.$grid = this._settings.$gridContainer.find(".js-grid");
            this._settings.$filter = this._settings.$gridContainer.find("[data-filter]");
            this._settings.$filterNav = this._settings.$gridContainer.find(".js-nav-filter");
            var self = this;
            this._settings.$grid.isotope();
            this._settings.$grid.imagesLoaded().progress(function() {
                self._settings.$grid.isotope();
            });
            this.bindEvents();
        },
        bindEvents: function() {
            this._settings.$filter.on("click", {
                self: this
            }, this.filter);
        },
        filter: function(e) {
            e.preventDefault();
            var self = e.data.self;
            var filterVal = $(this).data("filter");
            self._settings.$grid.isotope({
                filter: filterVal
            });
            self._settings.$filterNav.find("li").removeClass("active");
            $(this).parent().addClass("active");
        }
    };
    $.fn[pluginName] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
    };
})(jQuery, window, document);

(function($, window, document, undefined) {
    var pluginName = "helllmodal", defaults = {
        ease: "Power3.easeInOut",
        fullscreen: false
    };
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this._settings = {
            $open: null,
            $close: null,
            $setVideo: null,
            $stopVideo: null,
            $layer: null,
            $modal: null
        };
        this.init();
    }
    Plugin.prototype = {
        init: function() {
            this._settings.$open = $(this.element);
            this._settings.$modal = $("#" + this._settings.$open.data("mdl"));
            this._settings.$close = this._settings.$modal.find(".js-close-mdl");
            this._settings.$layer = $(".js-mdl-layer");
            this.bindEvents();
        },
        bindEvents: function() {
            this._settings.$open.on("click", {
                self: this
            }, this.open);
            this._settings.$close.on("click", {
                self: this
            }, this.close);
            this._settings.$layer.on("click", {
                self: this
            }, this.close);
            $(document).on("keydown", {
                self: this
            }, this.closeByKey);
        },
        open: function(e) {
            e.preventDefault();
            var self = e.data.self;
            if ($(this).hasClass("js-set-video")) {
                var id = $(this).attr("data-embed");
                var vid = "https://www.youtube-nocookie.com/embed/" + id + "?rel=0&autoplay=1";
                self._settings.$modal.find("iframe").attr("src", vid);
            }
            if ($(this).hasClass("js-set-mdl")) {
                var xPos = $(this).position().left, yPos = $(this).position().top;
                self._settings.$modal.css({
                    left: xPos + "px",
                    top: yPos + "px"
                });
            }
            self.checkFullscreen();
            TweenMax.to(self._settings.$modal, .35, {
                autoAlpha: 1,
                className: "+=is-open",
                ease: self.options.ease
            }, 0);
            TweenMax.to(self._settings.$layer, .35, {
                autoAlpha: 1,
                ease: self.options.ease
            }, 0);
        },
        checkFullscreen: function() {
            this._settings.fullscreen = this._settings.$open.data("fullscreen") ? this._settings.$open.data("fullscreen") : this.options.fullscreen;
            if (this._settings.fullscreen) {
                this._settings.$modal.addClass("is-fullscreen");
            }
        },
        close: function(e) {
            e.preventDefault();
            var self = e.data.self;
            if ($(this).hasClass("js-stop-video")) {
                self._settings.$modal.find("iframe").attr("src", "");
            }
            TweenMax.to(self._settings.$modal, .35, {
                autoAlpha: 0,
                className: "-=is-open",
                ease: self.options.ease,
                onComplete: self.reset,
                onCompleteParams: [ self ]
            });
            TweenMax.to(self._settings.$layer, .35, {
                autoAlpha: 0,
                ease: self.options.ease
            }, 0);
        },
        reset: function(self) {
            if (self._settings.fullscreen) {
                self._settings.$modal.removeClass("is-fullscreen");
                self._settings.fullscreen = false;
            }
        },
        closeByKey: function(e) {
            var self = e.data.self;
            if (e.keyCode === 27 && self._settings.$modal !== null) {
                e.preventDefault();
                self.close(e);
            }
        }
    };
    $.fn[pluginName] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
    };
})(jQuery, window, document);

(function($, window, document, undefined) {
    var pluginName = "helllnav", defaults = {
        ease: "Power3.easeInOut"
    };
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this._settings = {
            $navbar: null,
            $open: null,
            $close: null,
            $mobileNav: null,
            $layer: null
        };
        this.init();
    }
    Plugin.prototype = {
        init: function() {
            var self = this;
            this._settings.$navbar = $(this.element);
            this._settings.$open = this._settings.$navbar.find(".js-open-nav");
            this._settings.$close = this._settings.$navbar.find(".js-close-nav");
            this._settings.$mobileNav = this._settings.$navbar.find(".js-nav-mobile");
            this._settings.$layer = this._settings.$navbar.find(".js-nav-layer");
            this.bindEvents();
        },
        bindEvents: function() {
            this._settings.$open.on("click", {
                self: this
            }, this.open);
            this._settings.$close.on("click", {
                self: this
            }, this.close);
            this._settings.$layer.on("click", {
                self: this
            }, this.close);
            $(document).on("keydown", {
                self: this
            }, this.closeByKey);
            $(window).on("resize", {
                self: this
            }, this.close);
        },
        open: function(e) {
            e.preventDefault();
            var self = e.data.self;
            var tlNavOpen = new TimelineMax();
            tlNavOpen.to(self._settings.$layer, .25, {
                autoAlpha: 1,
                ease: self.options.ease
            }, 0).to(self._settings.$mobileNav, .5, {
                x: "0%",
                ease: self.options.ease
            }, 0);
        },
        close: function(e) {
            e.preventDefault();
            var self = e.data.self;
            var tlNavClose = new TimelineMax();
            tlNavClose.to(self._settings.$mobileNav, .5, {
                x: "100%",
                ease: self.options.ease
            }, 0).to(self._settings.$layer, .25, {
                autoAlpha: 0,
                ease: self.options.ease
            }, 0);
        },
        closeByKey: function(e) {
            var self = e.data.self;
            if (e.keyCode === 27 && self._settings.$modal !== null) {
                e.preventDefault();
                self.close(e);
            }
        }
    };
    $.fn[pluginName] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
    };
})(jQuery, window, document);

(function($, window, document, undefined) {
    var pluginName = "helllslider", defaults = {
        ease: "Power3.easeInOut",
        mode: "slide",
        loop: false,
        autoplay: false,
        autoplaySpeed: 3e3,
        autoheight: false
    };
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this._settings = {
            $sliderItems: null,
            $btnNextSlide: null,
            $btnPrevSlide: null,
            sliderCount: null,
            sliderLength: null,
            $sliderCounter: null,
            $sliderPills: null,
            mode: null,
            loop: null,
            autoplay: null,
            autoplaySpeed: null,
            autoplayInterval: null,
            autoheight: null
        };
        this.init();
    }
    Plugin.prototype = {
        init: function() {
            this._settings.$sliderItems = $(this.element).find(".js-slider-item");
            this._settings.$btnNextSlide = $(this.element).find(".js-slide-next");
            this._settings.$btnPrevSlide = $(this.element).find(".js-slide-prev");
            this._settings.sliderCount = parseInt($(this.element).attr("data-slide"));
            this._settings.sliderLength = parseInt($(this.element).find(".js-slider-item").length);
            this._settings.$sliderCounter = $(this.element).find(".js-slider-counter");
            this._settings.$sliderPills = $(this.element).find(".js-slider-pills");
            this._settings.mode = typeof $(this.element).attr("data-mode") !== typeof undefined && $(this.element).attr("data-mode") !== false ? $(this.element).attr("data-mode") : this.options.mode;
            this._settings.loop = typeof $(this.element).attr("data-loop") !== typeof undefined && $(this.element).attr("data-loop") !== false ? JSON.parse($(this.element).attr("data-loop")) : JSON.parse(this.options.loop);
            this._settings.autoplay = typeof $(this.element).attr("data-autoplay") !== typeof undefined && $(this.element).attr("data-autoplay") !== false ? JSON.parse($(this.element).attr("data-autoplay")) : JSON.parse(this.options.autoplay);
            this._settings.autoplaySpeed = typeof $(this.element).attr("data-speed") !== typeof undefined && $(this.element).attr("data-speed") !== false ? $(this.element).attr("data-speed") : this.options.autoplaySpeed;
            this._settings.autoheight = typeof $(this.element).attr("data-autoheight") !== typeof undefined && $(this.element).attr("data-autoheight") !== false ? JSON.parse($(this.element).attr("data-autoheight")) : JSON.parse(this.options.autoheight);
            this.initSlider();
            this.initSlides();
            this.initButtons();
            this.initPills();
            this.initCounter();
            this.initSwipe();
            this.initAutoplay();
            this.initLoop();
            this.bindEvents();
        },
        initSlider: function() {
            var self = this;
            if (self._settings.autoheight === true) {
                $(self.element).attr("data-autoheight", true);
                $(self.element).css("height", self._settings.$sliderItems.eq(0).height());
            }
        },
        initSlides: function() {
            if (this._settings.mode == "fade") {
                TweenMax.set(this._settings.$sliderItems, {
                    autoAlpha: 0
                });
                TweenMax.set(this._settings.$sliderItems.first(), {
                    autoAlpha: 1,
                    className: "+=active"
                });
            } else {
                TweenMax.set(this._settings.$sliderItems, {
                    x: "100%"
                });
                TweenMax.set(this._settings.$sliderItems.first(), {
                    x: "0%",
                    className: "+=active"
                });
            }
        },
        initButtons: function() {
            if (this._settings.sliderLength == 1) {
                this._settings.$btnPrevSlide.remove();
                this._settings.$btnNextSlide.remove();
            }
        },
        initPills: function() {
            var self = this;
            for (i = 0; i < this._settings.sliderLength; i++) {
                self._settings.$sliderPills.append('<li><a href="" data-slideto="' + i + '">' + (i + 1) + "</a></li>");
            }
            self._settings.$sliderPills.children("li").eq(0).addClass("is-active");
            if (this._settings.sliderLength <= 1) {
                self._settings.$sliderPills.remove();
            }
            if (self._settings.mode != "fade") {
                self._settings.$sliderPills.find("a").css({
                    cursor: "default"
                }).on("click", function(e) {
                    e.preventDefault();
                });
            }
        },
        initCounter: function() {
            var self = this;
            this._settings.$sliderCounter.children("span.current__slide").text(parseInt($(this.element).attr("data-slide") + 1));
            this._settings.$sliderCounter.children("span.max__slides").text(this._settings.$sliderItems.length);
        },
        initSwipe: function() {
            var self = this;
            $(this.element).swipe({
                swipe: function(event, direction, distance, duration, fingerCount, fingerData) {
                    if (direction === "left") {
                        self._settings.$btnNextSlide.trigger("click");
                    } else if (direction === "right") {
                        self._settings.$btnPrevSlide.trigger("click");
                    }
                },
                threshold: 75,
                allowPageScroll: "vertical",
                excludedElements: "button, input, select, textarea, .noSwipe"
            });
        },
        initAutoplay: function() {
            var self = this;
            if (this._settings.sliderLength == 1 || this._settings.autoplay !== true) {
                return;
            }
            self._settings.autoplayInterval = setInterval(function() {
                self._settings.sliderCount++;
                self.slideTo(self._settings.sliderCount);
                if (self._settings.loop === true) {
                    if (self._settings.sliderCount == self._settings.sliderLength) {
                        self.slideTo(0);
                    }
                }
            }, self._settings.autoplaySpeed);
        },
        initLoop: function() {
            var self = this;
            if (self._settings.loop !== true) {
                self._settings.$btnPrevSlide.addClass("is-disabled");
            }
        },
        bindEvents: function() {
            var self = this;
            this._settings.$btnNextSlide.on("click", {
                self: this
            }, this.next);
            this._settings.$btnPrevSlide.on("click", {
                self: this
            }, this.prev);
            if (self._settings.mode == "fade") {
                $(this.element).find(".js-slider-pills a").on("click", {
                    self: this
                }, this.slideTo);
            }
        },
        next: function(e) {
            e.preventDefault();
            var self = e.data.self;
            var $activeSlide = self._settings.$sliderItems.eq(self._settings.sliderCount), $nextSlide = self._settings.$sliderItems.eq(self._settings.sliderCount + 1), tlSlideNext = new TimelineMax({
                onComplete: function() {
                    self._settings.$btnPrevSlide.removeClass("is-disabled");
                }
            });
            if ($(this).hasClass("is-disabled")) return false;
            clearInterval(self._settings.autoplayInterval);
            if (self._settings.loop === true) {
                if (self._settings.sliderCount == self._settings.sliderLength - 1) {
                    self.slideTo(0);
                    return;
                }
            }
            self._settings.sliderCount++;
            if (self._settings.sliderCount === self._settings.sliderLength - 1 && self._settings.loop !== true) {
                $(this).addClass("is-disabled");
            }
            if (self._settings.mode == "fade") {
                tlSlideNext.to($activeSlide, .5, {
                    autoAlpha: 0,
                    ease: self._defaults.ease
                }, 0).to($nextSlide, .5, {
                    autoAlpha: 1,
                    ease: self._defaults.ease
                }, 0);
            } else {
                tlSlideNext.to($activeSlide, .75, {
                    x: "-100%",
                    className: "-=active",
                    ease: self._defaults.ease
                }, 0).to($nextSlide, .75, {
                    x: "0%",
                    className: "+=active",
                    ease: self._defaults.ease
                }, 0);
            }
            self.setCurrentSlide(self._settings.sliderCount);
        },
        prev: function(e) {
            e.preventDefault();
            var self = e.data.self;
            var $activeSlide = self._settings.$sliderItems.eq(self._settings.sliderCount), $nextSlide = self._settings.$sliderItems.eq(self._settings.sliderCount - 1), tlSlidePrev = new TimelineMax({
                onComplete: function() {
                    self._settings.$btnNextSlide.removeClass("is-disabled");
                }
            });
            if ($(this).hasClass("is-disabled")) return false;
            clearInterval(self._settings.autoplayInterval);
            if (self._settings.loop === true) {
                if (self._settings.sliderCount == 0) {
                    self.slideTo(self._settings.sliderLength - 1);
                    return;
                }
            }
            self._settings.sliderCount--;
            if (self._settings.sliderCount === 0 && self._settings.loop !== true) {
                $(this).addClass("is-disabled");
            }
            if (self._settings.mode == "fade") {
                tlSlidePrev.to($activeSlide, .5, {
                    autoAlpha: 0,
                    ease: self._defaults.ease
                }, 0).to($nextSlide, .5, {
                    autoAlpha: 1,
                    ease: self._defaults.ease
                }, 0);
            } else {
                tlSlidePrev.to($activeSlide, .75, {
                    x: "100%",
                    className: "-=active",
                    ease: Power3.easeInOut
                }, 0).to($nextSlide, .75, {
                    x: "0%",
                    className: "+=active",
                    ease: Power3.easeInOut
                }, 0);
            }
            self.setCurrentSlide(self._settings.sliderCount);
        },
        slideTo: function(e) {
            var slideTo = null;
            if (e.target) {
                e.preventDefault();
                var self = e.data.self;
                slideTo = parseInt($(e.currentTarget).attr("data-slideto"));
            } else {
                var self = this;
                slideTo = parseInt(e);
            }
            var $activeSlide = e.target ? self._settings.$sliderItems.eq(self._settings.sliderCount) : self._settings.$sliderItems.eq(slideTo - 1), $targetSlide = self._settings.$sliderItems.eq(slideTo), tlSlideTo = new TimelineMax();
            if (self._settings.mode == "fade") {
                tlSlideTo.to(self._settings.$sliderItems, .5, {
                    autoAlpha: 0,
                    ease: self._defaults.ease
                }, 0).to($targetSlide, .5, {
                    autoAlpha: 1,
                    ease: self._defaults.ease
                }, 0);
            } else {
                if (slideTo == 0) {
                    self._settings.$sliderItems.css({
                        transform: "translate(100%,0) matrix(1, 0, 0, 1, 0, 0)"
                    });
                    tlSlideTo.to(self._settings.$sliderItems, .85, {
                        x: "100%",
                        className: "-=active",
                        ease: Power3.easeInOut
                    }, 0).to($targetSlide, 1, {
                        x: "0%",
                        className: "+=active",
                        ease: Power3.easeInOut
                    }, 0);
                } else if (slideTo == self._settings.sliderLength - 1) {
                    self._settings.$sliderItems.css({
                        transform: "translate(-100%,0) matrix(1, 0, 0, 1, 0, 0)"
                    });
                    tlSlideTo.to(self._settings.$sliderItems, .85, {
                        x: "-100%",
                        className: "-=active",
                        ease: Power3.easeInOut
                    }, 0).to($targetSlide, 1, {
                        x: "0%",
                        className: "+=active",
                        ease: Power3.easeInOut
                    }, 0);
                } else {
                    self._settings.$sliderItems.css({
                        transform: "translate(-100%,0) matrix(1, 0, 0, 1, 0, 0)"
                    });
                    tlSlideTo.to($activeSlide, .85, {
                        x: "-100%",
                        className: "-=active",
                        ease: Power3.easeInOut
                    }, 0).to($targetSlide, 1, {
                        x: "0%",
                        className: "+=active",
                        ease: Power3.easeInOut
                    }, 0);
                }
            }
            self._settings.sliderCount = slideTo;
            self.setCurrentSlide(self._settings.sliderCount);
            if (self._settings.loop !== true) {
                self._settings.$btnPrevSlide.removeClass("is-disabled");
                self._settings.$btnNextSlide.removeClass("is-disabled");
                if (self._settings.sliderCount == 0) self._settings.$btnPrevSlide.addClass("is-disabled");
                if (self._settings.sliderCount == self._settings.sliderLength - 1) {
                    self._settings.$btnNextSlide.addClass("is-disabled");
                    clearInterval(self._settings.autoplayInterval);
                }
            }
        },
        setCurrentSlide: function(num) {
            var self = this;
            if (this._settings.autoheight === true) {
                var sliderHeight = new TimelineMax();
                sliderHeight.to($(this.element), .85, {
                    height: this._settings.$sliderItems.eq(num).height() + "px",
                    ease: Power3.easeInOut
                }, 0);
            }
            self._settings.$sliderCounter.children("span.current__slide").text(parseInt(num) + 1);
            self._settings.$sliderPills.children().removeClass("is-active").eq(num).addClass("is-active");
            $(self.element).attr("data-slide", num);
            return;
        }
    };
    $.fn[pluginName] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
    };
})(jQuery, window, document);

(function($, window, document, undefined) {
    var pluginName = "helllspy", defaults = {
        sectionSelector: "",
        navItemSelector: "",
        offsetElements: []
    };
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this._settings = {
            $sections: null,
            navItems: null,
            offsetHeight: 0,
            scrollPos: 0,
            currentOffset: 0,
            currentIndex: 0
        };
        this.init();
    }
    Plugin.prototype = {
        init: function() {
            this._settings.$sections = $(this.options.sectionSelector);
            this.getOffsetHeight();
            this.buildNav();
            this._settings.$navItems = $(this.element).find(this.options.navItemSelector);
            this.bindEvents();
        },
        bindEvents: function() {
            var self = this;
            $(window).on("load scroll", function() {
                self._settings.scrollPos = $(window).scrollTop();
                self.checkPosition();
            });
        },
        getOffsetHeight: function() {
            var self = this;
            if (self.options.offsetElements.length > 0) {
                $.each(self.options.offsetElements, function() {
                    self._settings.offsetHeight = $(this.toString()).outerHeight();
                });
            }
        },
        buildNav: function() {
            var self = this, i = 0;
            self._settings.$sections.each(function(i) {
                var sectionName = $(this).attr("data-pagenav"), sectionSlug = $(this).attr("id");
                $(self.element).children("ul").append('<li id="pagenav-' + sectionSlug + '"><a href="#' + sectionSlug + '" data-target="#' + sectionSlug + '">' + sectionName + "</a></li>");
                i++;
            });
        },
        checkPosition: function(e) {
            var self = this;
            self._settings.$sections.each(function() {
                self._settings.currentOffset = $(this).offset().top - self._settings.offsetHeight;
                if (self._settings.scrollPos >= self._settings.currentOffset) {
                    self._settings.currentIndex = parseInt($(this).attr("data-index"));
                    self._settings.$navItems.removeClass("active");
                    self._settings.$navItems.eq(self._settings.currentIndex).addClass("active");
                }
            });
        }
    };
    $.fn[pluginName] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
    };
})(jQuery, window, document);

(function($, window, document, undefined) {
    var pluginName = "hellltabs", defaults = {
        ease: "Power3.easeInOut",
        tabIndex: 0
    };
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this._settings = {
            $tabNav: null,
            $tabLinks: null,
            $tabSelect: null,
            $tabs: null,
            $tab: null,
            $currentTab: null,
            $currentTabLink: null,
            navType: null,
            $tabByHash: false
        };
        this.init();
    }
    Plugin.prototype = {
        init: function() {
            var hash = window.location.hash;
            this._settings.$tabNav = $(this.element).find(".js-tab-nav");
            this._settings.$tabLinks = $(this.element).find(".js-tab-link");
            this._settings.$tabSelect = $(this.element).find(".js-tab-select");
            this._settings.$tabs = $(this.element).find(".js-tabs");
            this._settings.$tab = $(this.element).find(".js-tab-item");
            this._settings.navType = this.getNavType();
            if (hash.length > 0) {
                this._settings.$tabByHash = this._settings.$tabNav.find('[href="' + hash + '"]');
            }
            if (this._settings.$tabByHash && this._settings.$tabByHash.length > 0) {
                this.options.tabIndex = this._settings.$tabByHash.data("index");
            }
            this.initializeTabs();
            this.bindEvents();
        },
        bindEvents: function() {
            var self = this;
            if (this._settings.navType === "links") {
                this._settings.$tabLinks.on("click", {
                    self: this
                }, this.select);
            } else {
                this._settings.$tabSelect.on("change", {
                    self: this
                }, this.select);
            }
            var resizeTimer;
            $(window).on("resize", function(e) {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    self.setTabHeight();
                }, 100);
            });
        },
        getNavType: function() {
            return this._settings.$tabLinks.length > 0 ? "links" : "select";
        },
        initializeTabs: function() {
            var self = this;
            this._settings.$currentTab = this._settings.$tab.eq(this.options.tabIndex);
            if (this._settings.navType === "links") {
                this._settings.$currentTabLink = this._settings.$tabNav.find("li").eq(this.options.tabIndex);
                this.setCurrentTabLink(this.options.tabIndex);
            } else {
                this._settings.$tabSelect[0].selectedIndex = this.options.tabIndex;
            }
            TweenMax.set(this._settings.$currentTab, {
                autoAlpha: 1,
                className: "+=active"
            });
            this.setTabHeight();
        },
        setTabHeight: function() {
            var self = this;
            setTimeout(function() {
                var tabHeight = $(self.element).find(".tab__item.active").height();
                TweenMax.set(self._settings.$tabs, {
                    height: tabHeight
                });
            }, 100);
        },
        select: function(e) {
            e.preventDefault();
            var self = e.data.self;
            var tabIndex;
            if (self._settings.navType === "select") {
                tabIndex = $(this).prop("selectedIndex");
            } else {
                tabIndex = $(this).data("index");
                self.setCurrentTabLink(tabIndex);
            }
            self._settings.$currentTab = $(self.element).find(".js-tab-item").eq(tabIndex);
            self.fadeOut(self);
            self.fadeIn(self);
            self.setTabContainerHeight(self);
        },
        fadeOut: function(self) {
            TweenMax.to(self._settings.$tab, .5, {
                autoAlpha: 0,
                className: "-=active",
                ease: self.options.ease
            }, 0);
        },
        fadeIn: function(self) {
            TweenMax.to(self._settings.$currentTab, .5, {
                autoAlpha: 1,
                className: "+=active",
                ease: self.options.ease
            }, 0);
        },
        setTabContainerHeight: function(self) {
            TweenMax.to(self._settings.$tabs, .5, {
                height: self._settings.$currentTab.height(),
                ease: self.options.ease
            }, 0);
        },
        setCurrentTabLink: function(tabIndex) {
            this._settings.$currentTabLink.removeClass("active");
            this._settings.$currentTabLink = this._settings.$tabNav.find("li").eq(tabIndex);
            this._settings.$currentTabLink.addClass("active");
            this.updateHash();
        },
        updateHash: function() {
            var href = this._settings.$currentTabLink.find("a").attr("href");
            if (href.length > 0 && window.location.hash.length > 0) {
                window.location.hash = href;
            }
        }
    };
    $.fn[pluginName] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
    };
})(jQuery, window, document);

$(function() {
    var Message = {
        $open: $(".js-open-msg"),
        $close: $(".js-close-msg"),
        $closeSmall: $(".js-close-msg-small"),
        $layer: $(".js-msg-layer"),
        $msg: null,
        init: function() {
            this.bindEvents();
        },
        bindEvents: function() {
            Message.$open.on("click", Message.open);
            Message.$close.on("click", Message.close);
            Message.$closeSmall.on("click", Message.closeSmall);
            Message.$layer.on("click", Message.close);
        },
        open: function(e) {
            e.preventDefault();
            var msgType = $(this).data("msg"), $msg = $(".js-msg-" + msgType);
            TweenMax.to(Message.$layer, .35, {
                autoAlpha: 1,
                ease: Power3.easeInOut
            }, 0);
            TweenMax.to($msg, .35, {
                top: "50%",
                autoAlpha: 1,
                ease: Power3.easeInOut
            }, 0);
        },
        close: function(e) {
            e.preventDefault();
            $msg = $(".js-msg");
            TweenMax.to(Message.$layer, .35, {
                autoAlpha: 0,
                ease: Power3.easeInOut
            }, 0);
            TweenMax.to($msg, .35, {
                top: "51%",
                autoAlpha: 0,
                ease: Power3.easeInOut
            }, 0);
        },
        closeSmall: function(e) {
            e.preventDefault();
            $msg = $(this).parent();
            $msg.hide();
        }
    };
    Message.init();
});

$(".is-animated input").focus(function() {
    $(this).siblings(".input-placeholder").addClass("is-selected");
});

$(".is-animated input").focusout(function() {
    if ($(this).val().length === 0) {
        $(this).siblings(".input-placeholder").removeClass("is-selected");
    }
});

$(".btn.is-animated-submit").click(function(e) {
    e.preventDefault();
    var $loadIcon = $(this).find(".icon-loader"), $doneIcon = $(this).find(".icon-done"), $errorIcon = $(this).find(".icon-error"), tlSubmit = new TimelineMax();
    tlSubmit.set($loadIcon, {
        autoAlpha: 0,
        right: "10px"
    }).set($doneIcon, {
        autoAlpha: 0,
        right: "32px"
    }).to($(this), .25, {
        paddingRight: "3em",
        ease: Power3.ease
    }, 0).to($loadIcon, .1, {
        autoAlpha: 1,
        ease: Power3.ease
    }, .25).to($loadIcon, .1, {
        autoAlpha: 0,
        right: "0px",
        ease: Power3.ease,
        onComplete: sendForm()
    }, 1.5);
    if (send === false) {
        TweenMax.to($errorIcon, .1, {
            autoAlpha: 1,
            right: "10px",
            delay: 1.75,
            ease: Power3.ease
        });
    } else {
        TweenMax.to($doneIcon, .1, {
            autoAlpha: 1,
            right: "10px",
            delay: 1.75,
            ease: Power3.ease
        });
    }
});

function sendForm() {
    send = true;
}

$(function() {
    var Caroussel = {
        $caroussel: $(".js-caroussel"),
        $carousselSlides: $(".js-caroussel-slides"),
        $carousselItems: $(".js-caroussel-item"),
        itemWidth: 0,
        itemSize: 0,
        itemCount: 0,
        carousselInterval: 0,
        init: function() {
            Caroussel.carousselInterval = parseInt(Caroussel.$caroussel.attr("data-interval"));
            this.setItems();
        },
        next: function() {
            Caroussel.$carousselItems.each(function(i, data) {
                var currentPos = parseInt($(this).css("left"));
                TweenMax.to($(this), .5, {
                    left: +(currentPos - Caroussel.itemWidth) + "px",
                    ease: Power2.eaeInOut,
                    onComplete: Caroussel.setFirstItem
                });
            });
            Caroussel.itemCount++;
        },
        setItems: function() {
            Caroussel.itemWidth = Caroussel.$carousselItems.first().outerWidth(true);
            Caroussel.itemSize = Caroussel.$carousselItems.length;
            Caroussel.$carousselItems.first().addClass("is-first");
            Caroussel.$carousselSlides.css("width", Caroussel.itemWidth * Caroussel.itemSize + "px");
            Caroussel.$carousselItems.each(function(i, data) {
                TweenMax.set($(this), {
                    left: +i * Caroussel.itemWidth + "px"
                });
            });
        },
        setFirstItem: function() {
            var $firstItem = Caroussel.$carousselItems.eq(Caroussel.itemCount - 1), $nextItem = Caroussel.$carousselItems.eq(Caroussel.itemCount);
            TweenMax.set($firstItem, {
                left: (Caroussel.itemSize - 1) * Caroussel.itemWidth + "px",
                className: "-=is-first"
            });
            TweenMax.set($nextItem, {
                className: "+=is-first"
            });
            if (Caroussel.itemCount === Caroussel.itemSize) {
                Caroussel.itemCount = 0;
            }
        }
    };
    Caroussel.init();
    var timer = null;
    timer = setInterval(function() {
        Caroussel.next();
    }, Caroussel.carousselInterval);
});

$(".js-play-video").on("click", function() {
    var id = $(this).data("embed");
    var vid = '<iframe width="853" height="480" src="https://www.youtube-nocookie.com/embed/' + id + '?rel=0&autoplay=1" frameborder="0" allowfullscreen></iframe>';
    $(this).html(vid);
});

$(".js-toggle-btn").on("click", function() {
    var $text = $(this).parent().find(".js-toggle-text");
    $text.slideToggle();
    $(this).toggleClass("is-open");
});

$(".js-form-tab-link").on("click", function(e) {
    e.preventDefault();
    var index = parseInt($(this).attr("data-index"));
    $(".js-form-tab-link").parent().removeClass("active");
    $(this).parent().addClass("active");
    $(".js-form-tab").hide();
    $(".js-form-tab").eq(index).show();
});