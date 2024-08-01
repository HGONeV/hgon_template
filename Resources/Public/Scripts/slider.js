/**
 * HGON JS Slider
 *
 * @version 0.0.1
 */
class HGONSlider
{
    /**
     * Default settings.
     *
     * @type {object}
     */
    settings = {
        loop: 0,
        slideSpeed: '.75s',
        slideDirection: 'left'
    };

    /**
     * Navigation properties
     *
     * @type {object}
     */
    nav = {
        container: null,
        list: null,
        type: 'default',
        slidePos: 0,
        itemWidth: 0,
        numItems: 0,
        visible: false,
        enabled: false
    };

    sliderContainer = null;
    itemWidth = 0;
    numItems = 0;
    slidePosition = 0;
    visibleItems = 0;
    numSlides = 0;
    hasArrows = false;
    lastTouch = null;
    touchPosX = 0;
    touchPosY = 0;
    sliding = false;
    stepsInfo = false;
    stylesElement = null;
    interval = null;


    /**
     * The class constructor.
     *
     * @param {string}      selector    The CSS selector for the slider container
     * @param {object}      options     Default settings overrides
     */
    constructor(selector, options = {})
    {
        let me = this;
        // override default settings, if given
        for (var key in options) {
            me.settings[key] = options[key];
        }
        me.sliderContainer = document.querySelector(selector);

        if (me.sliderContainer.offsetParent === null) {
            let observer = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.intersectionRatio > 0) {
                        me.init(me);
                        window.addEventListener('resize', me);
                    }
                });
            }, {root: document});
            observer.observe(me.sliderContainer);
        } else {
            window.setTimeout(me.init, 100, me);
            window.addEventListener('resize', me);
        }
    };


    /**
     * Initialize slider components like nav etc.
     *
     * @return {void}
     */
    initComponents()
    {
        let me = this;
        // init navigation component and properties
        let theNav = me.sliderContainer.querySelector('.slider-navigation');
        if (theNav === null) {
            me.nav.visible = false;
            me.nav.enabled = false;
        } else {
            me.nav.enabled = true;
            me.nav.container = theNav.closest('.nav-container');
            me.nav.list = theNav;
            if (theNav.classList.contains('teaser-navigation')) {
                me.nav.type = 'teaser';
            }
        }
        me.numItems = me.sliderContainer.querySelectorAll('.slider-item').length;
    };


    /**
     * Calculate itemWidth and stage width
     *
     * @return {void}
     */
    calcWidths()
    {
        let me = this;
        me.itemWidth = me.sliderContainer.querySelector('.slider-item').offsetWidth;
        // items being visible at the same time
        me.visibleItems = Math.round(me.sliderContainer.querySelector('.slider-stage').clientWidth / me.itemWidth);
        // calculate navigation items needed
        me.numSlides = me.numItems - me.visibleItems;
    };


    /**
     * Initialize the slider.
     *
     * @return {void}
     */
    init(me)
    {
        me.initComponents();
        me.calcWidths();
        // handle touch events
        me.sliderContainer.querySelector('.slider-stage').addEventListener('touchstart', me, {passive: false});
        me.sliderContainer.querySelector('.slider-stage').addEventListener('touchend', me, {passive: false});

        if (me.nav.enabled) {
            me.initNav(!me.sliderContainer.classList.contains('nav-inline'));
        }
        me.calcWidths();

        if (me.settings.loop > 0) {
            me.startAutoSliding();
            me.sliderContainer.querySelector('.slider-stage').addEventListener('mouseenter', me, {passive: false});
            me.sliderContainer.querySelector('.slider-stage').addEventListener('mouseleave', me, {passive: false});
        }

        if (me.stylesElement === null) {
            me.stylesElement = document.createElement('style');
            me.sliderContainer.appendChild(me.stylesElement);
        }
    };

    /**
     * Initialize the navigation
     *
     * @param {bool}        initialize       Set true, if nav shall be built completely
     * @return {void}
     */
    initNav(initialize = true)
    {
        let me = this;

        // clear the nav container before refill
        if (initialize) {
            me.nav.list.innerHTML = '';
            me.nav.numItems = 0;
        } else {
            me.nav.numItems = me.nav.list.querySelectorAll('li').length;
        }

        if (me.numSlides > 0) {
            // refill nav items
            if (initialize) {
                for (let i = 0; i <= me.numSlides; i++) {
                    let newNode = document.createElement('li');
                    if (me.nav.type == 'teaser') {
                        let teaserImg = me.sliderContainer.querySelector('.slider-item:nth-child(' + (i + 1) +') img');
                        if (teaserImg !== null) {
                            let teaser = document.createElement('img');
                            teaser.setAttribute('src', teaserImg.getAttribute('src'));
                            teaser.setAttribute('data-index', i);
                            if (teaserImg.hasAttribute('alt')) {
                                teaser.setAttribute('alt', teaserImg.getAttribute('alt'));
                            }
                            newNode.appendChild(teaser);
                        } else {
                            newNode.textContent = '';
                        }
                    } else {
                        newNode.textContent = '';
                    }
                    newNode.setAttribute('data-index', i);
                    newNode.addEventListener('click', me);
                    if (i === 0) {
                        newNode.classList.add('active');
                    }
                    me.nav.list.appendChild(newNode);
                    ++me.nav.numItems;
                }
            } else {
                me.nav.list.querySelectorAll('li').forEach(function(navItem, i) {
                    if (i === 0) {
                        navItem.classList.add('active');
                    }
                    navItem.setAttribute('data-index', i);
                    navItem.addEventListener('click', me);
                });
            }
            if (me.nav.numItems) {
                me.nav.itemWidth = me.nav.list.querySelector('li').offsetWidth + 4;
                me.nav.container.classList.remove('display-none');
                me.nav.visible = true;
            } else {
                me.nav.container.classList.add('display-none');
                me.nav.visible = false;
            }

                me.nav.container.style.maxWidth =
                    me.sliderContainer.querySelector('.slider-stage').offsetWidth + 'px';
                let navMarginLeft = parseInt(window.getComputedStyle(me.nav.container.querySelector('li')).marginLeft);
                let navMarginRight = parseInt(window.getComputedStyle(me.nav.container.querySelector('li')).marginRight);
                let navWidth = (me.nav.container.querySelector('li').offsetWidth + navMarginLeft + navMarginRight) * me.numSlides;
                let navContainerWidth = me.nav.container.clientWidth - parseInt(window.getComputedStyle(me.nav.container).paddingRight) - parseInt(window.getComputedStyle(me.nav.container).paddingLeft);

                // if nav is higher than the slider content, make nav scrollable vertically
                if (navWidth > navContainerWidth) {
                    let arrowContainer = document.createElement('div');
                    arrowContainer.classList.add('arrow-container');
                    if (!me.nav.container.querySelector('.scroll-up')) {
                        let up = document.createElement('span');
                        up.classList.add('scroll-up', 'arrow-up');
                        up.addEventListener('click', me);
                        arrowContainer.appendChild(up);
                    }

                    if (!me.nav.container.querySelector('.scroll-down')) {
                        let down = document.createElement('span');
                        down.classList.add('scroll-down', 'arrow-down');
                        down.addEventListener('click', me);
                        arrowContainer.appendChild(down);
                    }
                    me.nav.container.before(arrowContainer);
                }

                let navStyle = window.getComputedStyle(me.nav.container);
                // if the nav beside is not visible, scale stage to 100% width
                if (navStyle.display === 'none') {
                    me.nav.visible = false;
                }
        } else{
            me.nav.container.classList.add('display-none', 'display-sm-none', 'display-md-none', 'display-lg-none', 'display-xl-none', 'display-xxl-none');
        }

        if (!me.nav.visible) {
            me.sliderContainer.querySelector('.slider-stage').classList.add('width-full');
        } else {
            me.sliderContainer.querySelector('.slider-stage').classList.remove('width-full');
        }
    };

    /**
     * Slider Events listener.
     *
     * @param {event}       event       The DOM event being fired
     * @return {boolean}
     */
    handleEvent(event)
    {
        switch (event.type) {
            case 'resize':
                // calculate new dimensions
                this.calcWidths();
                if (this.numItems > this.visibleItems) {
                    if (this.nav.enabled) {
                        this.initNav();
                    }
                    // reposition items
                    this.slide(0);
                    this.slideNav(0)
                } else {
                    this.reset();
                }
            break;

            case 'touchstart':
                this.lastTouch = event.changedTouches.item(0);
                this.touchPosX = this.lastTouch.clientX;
                this.touchPosY = this.lastTouch.clientY;
                this.stopAutoSliding()
            break;

            case 'touchmove':
            break;

            case 'touchend':
                this.lastTouch = event.changedTouches.item(0);
                if ((this.lastTouch.clientX > (this.touchPosX + 30))) {
                    this.slide(1);
                    this.slideNav(1);
                } else if ((this.lastTouch.clientX < (this.touchPosX - 30))) {
                    this.slide(-1);
                    this.slideNav(-1);
                }
                this.touchPosX = 0;
                this.touchPosY = 0;
                this.startAutoSliding();
            break;

            case 'mouseenter':
                this.stopAutoSliding();
            break;

            case 'mouseleave':
                this.startAutoSliding();
            break;

            // default is "click" event
            default:
                // determine, which component has been clicked
                if (event.target.classList.contains('scroll-up')) {
                    this.slideNav(1);
                    this.slide(1)
                    return false;
                }

                if (event.target.classList.contains('scroll-down')) {
                    this.slideNav(-1);
                    this.slide(-1)
                    return false;
                }
                if (event.target.closest('.slider-navigation') !== null) {
                    this.slidePosition = parseInt(event.target.getAttribute('data-index')) * -1;
                    this.slide(0);
                    this.slideNav(0);
                }
            break;
        }

        return false;
    }; 


    /**
     * Reset the slider to the first item.
     *
     * @return void
     */
    reset()
    {
        this.slidePosition = 0;
        let sliderItems = this.sliderContainer.querySelector('.slider-items');
        sliderItems.style.transition = this.settings.slideDirection + ' ' + this.settings.slideSpeed;
        sliderItems.style.left = '0px';
        this.updateNav();
    };


    /**
     * Update arrows display settings
     *
     * @return void
     */
    updateNav()
    {
        if (this.nav.container !== null) {
            let me = this;
            me.nav.list.querySelectorAll('li').forEach(function (item, index) {
                if (index === (me.slidePosition) * -1) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        }
    };

    /**
     * Performs a slide event.
     *
     * @param {int}     steps   The amount of items to slide
     * @return {void}
     */
    slide(steps)
    {
        if (!this.sliding) {
            this.sliding = true;
            this.slidePosition += steps;
            if (this.slidePosition > 0) this.slidePosition = 0;
            if (this.slidePosition < (this.numSlides * -1)) this.slidePosition = (this.numSlides * -1);

            let sliderItems = this.sliderContainer.querySelector('.slider-items');
            sliderItems.style.transition = this.settings.slideDirection + ' ' + this.settings.slideSpeed;
            sliderItems.style.left = (this.itemWidth * this.slidePosition) + 'px';

            // update steps info, if activated
            if (this.stepsInfo) {
                this.sliderContainer.querySelector('.slide-index .current').textContent = Math.floor((this.slidePosition * -1) + 1);
            }

            // update navigation active element, if navigation
            this.updateNav();

            this.sliding = false;
        }
    };

    /**
     * Slides the navigation, if navigation is vertical.
     *
     * @param {int}     steps   The amount of items to slide
     * @return {void}
     */
    slideNav(steps)
    {
        if (this.nav.container !== null) {
            let visibleWidth = this.nav.container.clientWidth;
            let maxSlidePos = this.nav.numItems - Math.floor(visibleWidth / this.nav.itemWidth);
            this.nav.slidePos += steps;
            if (this.nav.slidePos > 0) this.nav.slidePos = 0;
            if (this.nav.slidePos < (maxSlidePos * -1)) this.nav.slidePos = (maxSlidePos * -1);
            this.nav.list.style.transform =
                'translate3d(' + (this.nav.slidePos * this.nav.itemWidth) + 'px, 0, 0)';
        }
    };

    /**
     * Start auto sliding
     */
    startAutoSliding()
    {
        if (this.settings.loop > 0) {
            let me = this;
            clearInterval(me.interval);
            me.interval = setInterval(function() {
                if ((-1 * me.slidePosition) != me.numSlides) {
                    me.slide(-1);
                    me.slideNav(-1)
                } else {
                    me.slide(-1 * me.slidePosition);
                    me.slideNav(-1 * me.slidePosition);
                }
            }, me.settings.loop);
        }
    };

    /**
     * Stop auto sliding
     */
    stopAutoSliding()
    {
        if (this.settings.loop > 0) {
            clearInterval(this.interval);
            this.interval = null;
        }
    }
};