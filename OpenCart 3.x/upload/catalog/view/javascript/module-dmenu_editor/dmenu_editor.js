/**
 * Script Module D.Menu Editor
 *
 * @version 1.0
 * 
 * @author D.art <d.art.reply@gmail.com>
 */

jQuery(function($) {
	'use strict';

    // Window Width.
    var widthWindowDMenu = $(window).width();

    // Remove Default Top Menu.
    if (typeof dmenuSettings.menu.top !== 'undefined') {
        if (!dmenuSettings.menu.top.display) {
            $('#top').remove();
        }
    } else {
        if (dMenuIsEmptyHTML($('#dmenu_editor-top'))) {
            $('#top').remove();
        }
    }

    // Fire on document.
    $(document).ready(function() {
        // When Display device < 768px.
        if (widthWindowDMenu < 768) {
            // Swipe Menu Setting (General).
            var dmenuCloseBySwipeGeneral = false;
            for (const [key, value] of Object.entries(dmenuSettings.menu)) {
                if (value.status && value.mobile.status && value.mobile.close) {
                    dmenuCloseBySwipeGeneral = true;
                    break;
                }
            }

            // Top Menu manipulations.
            if (typeof dmenuSettings.menu.top !== 'undefined') {
                if (dmenuSettings.menu.top.mobile.status) {
                    // Additional Items.
                    var additional = $('#dmenu_editor-menu_top').find('.dmenu_editor-item_additional');

                    // Move Additional Items to the end of the current menu level.
                    additional.each(function(index, element) {
                        var parentEl = $(element).parent();

                        if (typeof parentEl.data('additional_moved') === 'undefined') {
                            $(element).css({
                                'border-top': '1px solid #fff',
                                'margin-top': '5px'
                            });

                            parentEl.data('additional_moved', '1').attr('data-additional_moved', '1');
                        }

                        parentEl.append(element);
                    });

                    // Move Top Menu.
                    //$('#dmenu_editor-main').before($('#dmenu_editor-top'));
                }
            }

            // Show Mobile Menu.
            $('.dmenu_editor-toggle').on('click', function(e){
                e.preventDefault();

                var menu_type = $(this).closest('.dmenu_editor-nav_menu').data('menu');

                $('body').css('overflow', 'hidden');
                $(this).toggleClass('active').closest('#dmenu_editor-menu_' + menu_type).addClass('open').find('.dmenu_editor-collapse > .dmenu_editor-dropdown_inner').css('overflow', 'hidden auto');
            });

            // Hide Mobile Menu. With Button.
            $('.dmenu_editor-mobile_toggle').on('click', function(e){
                e.preventDefault();

                var menu_type = $(this).closest('.dmenu_editor-nav_menu').data('menu');

                if (dmenuSettings['menu'][menu_type]['mobile']['close'] !== 1) {
                    dMenuMobileClose(menu_type);
                }
            });

            // Hide Mobile Menu. With Swipe.
            if (dmenuCloseBySwipeGeneral) {
                $('.dmenu_editor-mobile_bg').swipe({
                    swipeLeft: function(event, direction, distance, duration, fingerCount, fingerData, currentDirection){
                        var menu_type = $(this).closest('.dmenu_editor-nav_menu').data('menu');

                        if (dmenuSettings['menu'][menu_type]['mobile']['close'] !== 0) {
                            dMenuMobileClose(menu_type);
                        }
                    },
                    threshold: 10 // Distance for triggers swipe by 'px'
                });
            }

            // Click Menu Item.
            $('.dmenu_editor-collapse .dmenu_editor-item > a').on('click', function(e){
                e.preventDefault();

                let _this = $(this);
                let href = _this.attr('href');
                let target = _this.attr('target') ? _this.attr('target') : '_self';
                let hrefCancelArray = ['', '#'];

                // Open Mobile Menu Item.
                if (_this.parent().hasClass('dmenu_editor-dropdown')) {
                    _this.closest('li').toggleClass('active');
                    _this.closest('.dmenu_editor-dropdown_inner').css({ 'overflow' : 'inherit' });
                    _this.next().css({ 'overflow' : 'hidden auto' }).toggle(200, 'linear');

                // Follow the link.
                } else if (href && (jQuery.inArray(href, hrefCancelArray) === -1)) {
					window.open(href, target);
                }
            });

            // Close Mobile Menu Item.
            $('.dmenu_editor-collapse .button_back').on('click', function(e){
                e.preventDefault();

                $(this).closest('.dmenu_editor-dropdown_inner').css({ 'overflow' : '' }).hide(200, 'linear').closest('li').removeClass('active').closest('.dmenu_editor-dropdown_inner').css({ 'overflow' : 'hidden auto' });
            });

        // When Display device >= 768px.
        } else {
            // Click Menu Item.
            $('.dmenu_editor-collapse .dmenu_editor-item > a').on('click', function(e){
                e.preventDefault();

                let _this = $(this);
                let href = _this.attr('href');
                let target = _this.attr('target') ? _this.attr('target') : '_self';
                let hrefCancelArray = ['', '#'];

                if (href && (jQuery.inArray(href, hrefCancelArray) === -1)) {
					window.open(href, target);
                }
            });

            // Open Menu Item.
            // Offscreen Dropdown Item.
            $('.dmenu_editor-dropdown').on('mouseenter', function(e){
                let _this = $(this);

                if (_this.children('.dmenu_editor-dropdown_inner').is(':dmenu-off-right')) {
                    _this.children('.dmenu_editor-dropdown_inner').addClass('dmenu-off-right');
                }

                /* if (_this.children('.dmenu_editor-dropdown_inner').is(':dmenu-off-bottom')) {
                    _this.children('.dmenu_editor-dropdown_inner').addClass('dmenu-off-bottom');
                } */
            });

            // Close Menu Item.
            // Offscreen Dropdown Item.
            $('.dmenu_editor-dropdown').on('mouseleave', function(e){
                let _this = $(this);

                if (_this.children('.dmenu_editor-dropdown_inner').hasClass('dmenu-off-right')) {
                    _this.children('.dmenu_editor-dropdown_inner').removeClass('dmenu-off-right');
                }

                /* if (_this.children('.dmenu_editor-dropdown_inner').hasClass('dmenu-off-bottom')) {
                    _this.children('.dmenu_editor-dropdown_inner').removeClass('dmenu-off-bottom');
                } */
            });
        }

        // Select Currency.
        $('#dmenu-form-currency .currency-select').on('click', function(e) {
            e.preventDefault();

            $('#dmenu-form-currency input[name=\'code\']').val($(this).attr('name'));
            $('#dmenu-form-currency').submit();
        });

        // Select Language.
        $('#dmenu-form-language .language-select').on('click', function(e) {
            e.preventDefault();

            $('#dmenu-form-language input[name=\'code\']').val($(this).attr('name'));
            $('#dmenu-form-language').submit();
        });
    });

    /**
     * Close Mobile Menu.
     */
    function dMenuMobileClose(menu_type) {
        $('body').css('overflow', '');
        $('#dmenu_editor-menu_' + menu_type + ' .dmenu_editor-dropdown').removeClass('active');
        $('#dmenu_editor-menu_' + menu_type + ' .dmenu_editor-dropdown_inner').css({ 'overflow' : '' });
        $('#dmenu_editor-menu_' + menu_type + ' > div > div .dmenu_editor-dropdown_inner').hide(200, 'linear');
        $('.dmenu_editor-mobile_toggle').toggleClass('active').closest('#dmenu_editor-menu_' + menu_type).removeClass('open');
    }

    /**
     * Check empty HTML.
     */
    function dMenuIsEmptyHTML(el) {
        return $(el).length ? !$.trim(el.html()) : undefined;
    }

    /**
     * jQuery offscreen plugin.
     * Edited classes for DMenu Editor.
     *
     * Copyright Cory LaViska for A Beautiful Site, LLC
     * @license: http://opensource.org/licenses/MIT
     */
    $.extend($.expr[':'], {
        'dmenu-off-top': function(el) {
            return $(el).offset().top < $(window).scrollTop();
        },
        'dmenu-off-bottom': function(el) {
            return $(el).offset().top + $(el).outerHeight() - $(window).scrollTop() > $(window).height();
        },
        'dmenu-off-right': function(el) {
            return $(el).offset().left + $(el).outerWidth() - $(window).scrollLeft() > $(window).width();
        },
        'dmenu-off-left': function(el) {
            return $(el).offset().left < $(window).scrollLeft();
        },
        'dmenu-off-screen': function(el) {
            return $(el).is(':dmenu-off-top, :dmenu-off-right, :dmenu-off-bottom, :dmenu-off-left');
        }
    });
});