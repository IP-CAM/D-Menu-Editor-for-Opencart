/**
 * Script Module D.Menu Editor
 *
 * @version 1.0
 * 
 * @author D.art <d.art.reply@gmail.com>
 */

jQuery(function($) {
    // Set width of window to global variable.
    window.widthWindowDME = $(window).width();

    // Fire on document
    $(document).ready(function() {
        // Sortable Menu Items.
        dmenuSortableMenuItems();

        // Sortable Sticky Items.
        dmenuSortableStickyItems();

        // When Display device > 1200px.
        if (widthWindowDME <= 1200) {
            // Open/Hide Menu Items.
            $('.sticky-menu_items_button button').on('click', function(){
                $(this).closest('.module-menu_items_add').toggleClass('active');
            });
        }

        // Show/Hide Sticky Item.
        $('.sticky-menu_items_add .sticky-menu_item_title').on('click', function(){
            var _this = $(this);

            if (!_this.closest('.sticky-menu_item_wrap').hasClass('open')) {
                _this.closest('.sticky-menu_items_add').find('.sticky-menu_item_wrap').removeClass('open').find('.sticky-menu_item_content').hide(200, 'linear');
                _this.closest('.sticky-menu_item_wrap').toggleClass('open').find('.sticky-menu_item_content').first().toggle(200, 'linear');
            }
        });

        // Show/Hide Menu Item.
        $('.module-menu_items').on('click', '.fa_arrow_open', function(){
            $(this).closest('.module-menu_item_wrap').toggleClass('open').find('.module-dmenu_editor-item_content').first().toggle(200, 'linear');
        });

        // Change Title Menu Item (current language).
        $('.module-menu_items_wrap').on('change paste keyup', 'input.name_' + dmenu_configLanguageID, function() {
            var _this = $(this);
            var title = _this.val();

            if (title.length <= 0) {
                title = _this.closest('.module-menu_item_wrap').data('title');
            }

            _this.closest('.module-menu_item_wrap').find('.module-dmenu_editor-item_title').first().find('.text').text(title);
        });

        // Lock/Unlock URL field.
        $('.module-menu_items_wrap').on('click', '.item_url i', function() {
            var _this = $(this);
            var lock = _this.data('lock');
            var unlock = _this.data('unlock');

            // Change icon and tooltip.
            if (_this.hasClass('fa-lock')) {
                _this.removeClass('fa-lock').addClass('fa-unlock').attr('data-bs-original-title', lock);
                _this.prev().removeAttr('readonly');
            } else {
                _this.removeClass('fa-unlock').addClass('fa-lock').attr('data-bs-original-title', unlock);
                _this.prev().attr('readonly', '');
            }

            // Refresh tooltips.
            _this.closest('.module-menu_items_wrap').find('[data-bs-toggle="tooltip"]').tooltip();
        });

        // Checked Default Store Menu.
        $('.store-tab_pane .field-store_default').on('change', function() {
            var field = $(this);

            if (field.is(':checked')) {
                field.closest('.module_menu').find('.field-store_default').attr('checked', false).prop('checked', false);
                field.attr('checked', true).prop('checked', true);
            } else {
                field.closest('.module_menu').find('.field-store_default').attr('checked', false).prop('checked', false);
            }
        });

        // Enable/Disable Status.
        $('.module-menu_items_wrap').on('change', '.field-status', function(){
            var field = $(this);

            // Display Status on Item.
            if (field.is(':checked')) {
                field.closest('.module-dmenu_editor-item').find('span.status').removeClass('enabled disabled').addClass('enabled');
            } else {
                field.closest('.module-dmenu_editor-item').find('span.status').removeClass('enabled disabled').addClass('disabled');
            }
        });

        // Show/Hide Title Catalog Settings.
        $('.module-menu_items_wrap').on('change', '.setting-category_menu select', function(){
            var field = $(this);
            var categoryDisplay = field.val();

            if (categoryDisplay == '1') {
                field.closest('.module-dmenu_editor-item_content').find('.setting-category_menu_hidden_block').removeClass('hidden');
            } else {
                field.closest('.module-dmenu_editor-item_content').find('.setting-category_menu_hidden_block').addClass('hidden');
            }
        });

        // Search Sticky Data. AJAX.
        $('.sticky_menu_item_search_button').on('click', function(){
            var _this = $(this);
            var layout = _this.data('layout');
            var search = _this.prev().val();

            var textButton = _this.text();
            var textLoading = _this.data('text_loading');

            $.ajax({
                url: 'index.php?route=extension/dmenu_editor/module/dmenu_editor' + dmenu_routeMethodSeparator + 'search&user_token=' + dmenu_userToken,
                type: 'post',
                data: 'layout=' + layout + '&limit=' + dmenu_searchLimit + '&search=' + search,
                dataType: 'json',
                beforeSend: function(){
                    _this.closest('.tab-pane').find('.sticky_menu_item_search_wrap').html(textLoading);

                    _this.closest('.sticky_menu_item_search_form').find('input').prop('disabled', true);
                    _this.closest('.sticky_menu_item_search_form').find('button').prop('disabled', true);

                    _this.closest('.sticky_menu_item_search_form').find('button').text(textLoading);
                },
                complete: function(){
                    _this.closest('.sticky_menu_item_search_form').find('input').prop('disabled', false);
                    _this.closest('.sticky_menu_item_search_form').find('button').prop('disabled', false);

                    _this.closest('.sticky_menu_item_search_form').find('button').text(textButton);
                },
                success: function(json) {
                    //console.log(json);

                    if (json && (typeof json === 'object') && (Object.keys(json).length !== 0) && (Object.getPrototypeOf(json) !== Object.prototype)) {
                        var searchResults = '';

                        for (var i = 0; i < json.length; i++) {
                            var searchDataNamesSeo = '';

                            for (language in dmenu_languages) {
                                searchDataNamesSeo += ' data-name_' + dmenu_languages[language]["language_id"] + '="' + ( ( typeof json[i]['names'] !== 'undefined' ) && ( typeof json[i]['names'][dmenu_languages[language]["language_id"]] !== 'undefined' ) ? json[i]['names'][dmenu_languages[language]["language_id"]] : '' ) + '" data-url_seo_' + dmenu_languages[language]["language_id"] + '="' + ( ( typeof json[i]['url']['seo'] !== 'undefined' ) && ( typeof json[i]['url']['seo'][dmenu_languages[language]["language_id"]] !== 'undefined' ) ? json[i]['url']['seo'][dmenu_languages[language]["language_id"]] : '' ) + '"';
                            }

                            searchResults += '<div class="sticky-menu_item-item sticky-menu_item-item_' + i + '">';
                                searchResults += '<span data-id="' + json[i]['id'] + '" data-url_link="' + json[i]['url']['link'] + '" data-layout="' + json[i]['layout'] + '" ' + searchDataNamesSeo + '>' + ( json[i]['title'] ? json[i]['title'] : dmenu_translated_text['note_title_empty'] ) + '</span>';
                            searchResults += '</div>';
                        }

                        _this.closest('.tab-pane').find('.sticky_menu_item_search_wrap').html(searchResults);
                    } else {
                        _this.closest('.tab-pane').find('.sticky_menu_item_search_wrap').html(dmenu_translated_text['text_search_missing']);
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        });

        // Remove Menu Item.
        var dmenuTimeoutRemoveID = null;
        $('.module-menu_items_wrap').on('click', '.fa_row_remove', function(){
            var textMissing = '';
            var speedAnimationHide = 200;
            var timeoutBeforeRepair = 500;
            var item = $(this).closest('.module-menu_item_wrap');
            var siblingsLength = item.siblings().length;
            var isSortableWrap = item.parent().is('.module-menu_items_wrap');
            var container = item.closest('.module-menu_items_wrap').prop('id');
            var removeOrNotRemove = null;

            // Remove or Not Remove, that is the question;
            // Whether 'tis nobler in the mind to suffer
            // The Slings and Arrows of outrageous Fortune
            // Or to take arms against a sea of troubles,
            // And by opposing, end them.
            if (item.find('.module-menu_item_wrap').length > 0) {
                removeOrNotRemove = confirm(dmenu_translated_text['note_item_not_empty']);
            }

            // The choice is made.
            // Remove.
            if (removeOrNotRemove || (removeOrNotRemove === null)) {
                // Removing.
                if (siblingsLength > 1) {
                    item.hide(speedAnimationHide, function(){ item.remove(); });
                } else if (siblingsLength == 1) {
                    if (item.siblings('.sortable_filtered').length > 0) {
                        item.hide(speedAnimationHide, function(){ item.remove(); });

                        //textMissing = dmenuDisplayMessageMissing();
                        //item.closest('.module-menu_items_wrap').append(textMissing);
                    } else {
                        item.hide(speedAnimationHide, function(){ item.remove(); });
                    }
                } else {
                    if (!$(this).hasClass('sortable_filtered')) {
                        item.hide(speedAnimationHide, function(){ item.remove(); });

                        if (isSortableWrap) {
                            textMissing = dmenuDisplayMessageMissing();
                            item.closest('.module-menu_items_wrap').append(textMissing);
                        }
                    }
                }

                // Repair Menu Items.
                if (dmenuTimeoutRemoveID != null) {
                    window.clearTimeout(dmenuTimeoutRemoveID);

                    dmenuTimeoutRemoveID = window.setTimeout(function(){
                        dmenuRepairMenuItems(container);
                    }, timeoutBeforeRepair);
                } else {
                    dmenuTimeoutRemoveID = window.setTimeout(function(){
                        dmenuRepairMenuItems(container);
                    }, timeoutBeforeRepair);
                }

            // or Not?..
            } else {}
        });

        // Copy current menu to selected store.
        $('#module-menu-copy_btn').on('click', function(){
            var button = $(this);
            var messageEl = button.closest('.module-copy').find('.copy-message');

            messageEl.removeClass('success error').text(dmenu_translated_text['text_copying']);

            var targetStoreID = button.closest('.module-copy').find('select').val();
            var currentMenuType = $('#tab-content-menu > .tab-pane.active .module-menu_items').data('menu');
            var currentStoreID = $('#tab-content-menu > .tab-pane.active .store-tab_pane.active').data('store');

            var menuStore = $('#tab-content-menu > .tab-pane.active .store-tab_pane.active .module-menu_items_wrap');
            var menuHTML = menuStore.html();

            // Store Menu is not empty.
            if (!menuStore.find('.not_repair').length) {
                switch(targetStoreID) {
                    // Store not selected.
                    case '':
                        // Display message.
                        dmenuTimeoutMessage(messageEl, dmenu_translated_text['text_store_not_selected'], 'error');

                        break;

                    // Copy to all stores in current menu.
                    case 'all':
                        // Set menu HTML to all stores.
                        for (store in dmenu_stores) {
                            if (dmenu_stores[store]['store_id'] != currentStoreID) {
                                var storeTargetID = dmenu_stores[store]['store_id'];
                                var items = $(menuHTML);

                                // Params to change attributes.
                                var dataChange = {
                                    mode           : 'copy',
                                    menu           : currentMenuType,
                                    storeIdCurrent : currentStoreID,
                                    storeIdTarget  : storeTargetID,
                                    attrIdDepth    : false,
                                    attrNameDepth  : false
                                };

                                // Change attributes.
                                items = dmenuChangeAttributes(items, dataChange);

                                // Set new HTML to Document.
                                $('#tab-content-menu #module_menu_' + currentMenuType + '_store_' + storeTargetID + '_sortable_wrap').html(items);

                                // Hide Menu Items.
                                $('#tab-content-menu #module_menu_' + currentMenuType + '_store_' + storeTargetID + '_sortable_wrap').find('.module-menu_item_wrap').removeClass('open').find('.module-dmenu_editor-item_content').css('display', 'none');
                            }
                        }

                        // Display message.
                        dmenuTimeoutMessage(messageEl, dmenu_translated_text['text_message_success'], 'success');

                        break;

                    // Copy to selected Store.
                    default:
                        // Store is not current.
                        if (targetStoreID != currentStoreID) {
                            var items = $(menuHTML);

                            // Params to change attributes.
                            var dataChange = {
                                mode           : 'copy',
                                menu           : currentMenuType,
                                storeIdCurrent : currentStoreID,
                                storeIdTarget  : targetStoreID,
                                attrIdDepth    : false,
                                attrNameDepth  : false
                            };

                            // Change attributes.
                            items = dmenuChangeAttributes(items, dataChange);

                            // Set new HTML to Document.
                            $('#tab-content-menu #module_menu_' + currentMenuType + '_store_' + targetStoreID + '_sortable_wrap').html(items);

                            // Hide Menu Items.
                            $('#tab-content-menu #module_menu_' + currentMenuType + '_store_' + targetStoreID + '_sortable_wrap').find('.module-menu_item_wrap').removeClass('open').find('.module-dmenu_editor-item_content').css('display', 'none');

                            // Display message.
                            dmenuTimeoutMessage(messageEl, dmenu_translated_text['text_message_success'], 'success');

                        // Store current.
                        } else {
                            // Display message.
                            dmenuTimeoutMessage(messageEl, dmenu_translated_text['text_unable_copy_to_store'], 'error');
                        }

                        break;
                }

            // Store Menu is empty.
            } else {
                // Display message.
                dmenuTimeoutMessage(messageEl, dmenu_translated_text['text_menu_item_missing'], 'error');
            }
        });

        // Select Info-block.
        $('#tab-settings_menu .select-setting-display').on('change', function(){
            var _this = $(this);
            var value = _this.val();
            var menu = _this.closest('.tab-pane').data('menu');

            // Show selected Info-block.
            _this.closest('.form-group').find('.alert').css('display', 'none');
            _this.closest('.form-group').find('#alert-' + menu + '_display-' + value).css('display', '');
        });

        // Hide Info-block.
        $('#tab-settings_menu .field-display-hide input').on('change', function(){
            var checkbox = $(this);

            // Hide Info-block in current menu settings.
            if (checkbox.is(':checked')) {
                checkbox.closest('.form-group').find('.field-display-info').css('display', 'none');
            } else {
                checkbox.closest('.form-group').find('.field-display-info').css('display', '');
            }
        });

        // Copy code to clipboard.
        $('#tab-settings_menu .field-display-info .copy-code').on('click', function(){
            var button = $(this);
            var messageEl = button.closest('.block').find('.message');

            // Remove message.
            messageEl.removeClass('success error').html('');

            // Get code.
            var code = button.closest('.dmenu-alert-content').find('code').text();

            // Create temporary INPUT field.
            var tempInput = $('<input type="text">');
            $('body').append(tempInput);

            // Select INPUT field.
            tempInput.val(code).select();

            // Copy code to clipboard.
            try {
                // Copy code.
                document.execCommand('copy');

                // Display message.
                dmenuTimeoutMessage(messageEl, dmenu_translated_text['text_message_success'], 'success');
            } catch (err) {
                // Error.
                console.error('Unable to copy to clipboard!', err);

                // Display message.
                dmenuTimeoutMessage(messageEl, dmenu_translated_text['text_message_error'], 'error');
            }

            // Remove temporary INPUT field.
            tempInput.remove();
        });
    });

    // Sortable Menu Items.
    function dmenuSortableMenuItems () {
        var menuSortable = [];
        var menuSortableWrap = [].slice.call(document.querySelectorAll('.nested-sortable'));

        for (var i = 0; i < menuSortableWrap.length; i++) {
            menuSortable[i] = new Sortable(menuSortableWrap[i], {
                group: {
                    name : 'shared',
                    pull : true,
                    put  : true
                },
                handle               : '.module-dmenu_editor-item_title',
                filter               : '.sortable_filtered',
                preventOnFilter      : false,
                animation            : 150,
                fallbackOnBody       : true,
                fallbackTolerance    : 0, //20
                swapThreshold        : 0.65,
                emptyInsertThreshold : 0, //5

                onEnd: function (event) {
                    //console.log(event);

                    var container = $(event.item).closest('.module-menu_items_wrap').prop('id');

                    // Repair Menu Items.
                    dmenuRepairMenuItems(container);
                }
            });
        }
    }

    // Sortable Sticky Items.
    function dmenuSortableStickyItems () {
        var menuStickySortable = [];
        var menuStickySortableWrap = [].slice.call(document.querySelectorAll('.sticky-clone-sortable'));

        for (var i = 0; i < menuStickySortableWrap.length; i++) {
            menuStickySortable[i] = new Sortable(menuStickySortableWrap[i], {
                group: {
                    name : 'shared',
                    pull : 'clone',
                    put  : false
                },
                animation : 150,
                sort      : false,

                onStart: function (event) {
                    if (widthWindowDME <= 1200) {
                        $(event.item).closest('.module-menu_items_add').toggleClass('active');
                    }
                },

                onEnd: function (event) {
                    //console.log(event);

                    if (!$(event.to).hasClass('sticky-clone-sortable')) {
                        var _item = $(event.item);
                        var container = _item.closest('.module-menu_items_wrap').prop('id');
                        var menu = _item.closest('.module-menu_items').data('menu');
                        var store = _item.closest('.store-tab_pane').data('store');

                        // Remove Missing message.
                        _item.closest('.module-menu_items_wrap').find('.not_repair').remove();

                        // Get data from attributes.
                        var id = _item.children().data('id');
                        var layout = _item.children().data('layout');
                        var urlLink = _item.children().data('url_link');
                        var title = _item.children().text();

                        var urlSeo = [];
                        var names = [];

                        for (language in dmenu_languages) {
                            if (typeof _item.children().data('url_seo_' + dmenu_languages[language]["language_id"]) !== 'undefined') {
                                urlSeo[dmenu_languages[language]["language_id"]] = _item.children().data('url_seo_' + dmenu_languages[language]["language_id"]);
                            } else {
                                urlSeo[dmenu_languages[language]["language_id"]] = '';
                            }

                            if (typeof _item.children().data('name_' + dmenu_languages[language]["language_id"]) !== 'undefined') {
                                names[dmenu_languages[language]["language_id"]] = _item.children().data('name_' + dmenu_languages[language]["language_id"]);
                            } else {
                                names[dmenu_languages[language]["language_id"]] = '';
                            }
                        }

                        // Row number.
                        var row = event.newIndex;

                        // Get current Layout and attribute 'readonly'.
                        var titleNotice = '';
                        var fieldReadonly = 'readonly';
                        var seoDisplay = false;

                        if (layout in dmenu_moduleLayouts) {
                            titleNotice = dmenu_moduleLayouts[layout];

                            switch (layout) {
                                case 'custom':
                                    fieldReadonly = '';
                                    seoDisplay = true;
                                    break;
                                default:
                                    break;
                            }
                        } else {
                            titleNotice = dmenu_translated_text['text_item_desc_none'];
                            fieldReadonly = '';
                        }

                        // Get depth of attributes: FOR, ID, NAME
                        var attrIdDepth = '';
                        var attrNameDepth = '';

                        if ($(event.to).is('.module-menu_items_wrap')) {
                            attrIdDepth = row;
                            attrNameDepth = '[' + row + ']';
                        } else {
                            var targetID = $(event.to).closest('.module-menu_item_wrap').prop('id');
                            var targetIdRows = targetID.match(/\w+$/);
                            var targetNameRows = targetIdRows[0].replaceAll('_', '][rows][');

                            attrIdDepth = targetIdRows[0] + '_' + row;
                            attrNameDepth = '[' + targetNameRows + '][rows][' + row + ']';
                        }

                        // Get Menu Item.
                        if (layout == 'catalog') {
                            // Params to get Menu Item HTML.
                            var dataItem = {
                                menu          : menu,
                                store         : store,
                                layout        : layout,
                                names         : names,
                                attrIdDepth   : attrIdDepth,
                                attrNameDepth : attrNameDepth
                            };

                            // Menu Item HTML.
                            var menuItemHTML = dmenuGetCatalogMenuItem(dataItem);

                            // Prepare Menu Item container.
                            _item.prop('id', menu + '-store_' + store + '-item-' + attrIdDepth).prop('class', 'form-group module-menu_item_wrap catalog_item').attr('data-row', row).data('row', row);
                        } else {
                            // Params to get Menu Item HTML.
                            var dataItem = {
                                menu          : menu,
                                store         : store,
                                id            : id,
                                layout        : layout,
                                title         : title,
                                titleNotice   : titleNotice,
                                urlLink       : urlLink,
                                names         : names,
                                urlSeo        : urlSeo,
                                attrIdDepth   : attrIdDepth,
                                attrNameDepth : attrNameDepth,
                                fieldReadonly : fieldReadonly,
                                seoDisplay    : seoDisplay
                            };

                            // Menu Item HTML.
                            var menuItemHTML = dmenuGetMenuItem(dataItem);

                            // Prepare Menu Item container.
                            _item.prop('id', menu + '-store_' + store + '-item-' + attrIdDepth).prop('class', 'form-group module-menu_item_wrap').attr('data-title', title).data('title', title).attr('data-row', row).data('row', row);
                        }

                        // Set Menu Item.
                        _item.html(menuItemHTML);

                        // Repair Menu Items.
                        dmenuRepairMenuItems(container);

                        // Refresh tooltips.
                        _item.closest('.module-menu_items_wrap').find('[data-bs-toggle="tooltip"]').tooltip();

                        // Refresh Sortable Menu Items.
                        dmenuSortableMenuItems();
                    }
                }
            });
        }
    }

    // Repair Menu Items.
    function dmenuRepairMenuItems(container) {
        // Add visual loader.
        $('#' + container).closest('.module_menu').append('<div class="loader-repair"><div class="lds-dual-ring"></div></div>');

        // Repair attributes.
        dmenuRepairAttributes(container);

        // Remove visual loader.
        $('#' + container).closest('.module_menu').find('.loader-repair').remove();
    }

    // Repair attributes. Recursion.
    function dmenuRepairAttributes(container) {
        var _container = $('#' + container);
        var menu = _container.closest('.module-menu_items').data('menu');
        var store = _container.closest('.store-tab_pane').data('store');

        // Recursion condition.
        if (_container.children().length > 0) {
            _container.children().each(function(index, element) {
                var _this = $(this);

                if (!_this.is('.not_repair')) {
                    // Set data row to each element.
                    _this.attr('data-row', index).data('row', index);

                    // Get depth of attributes: FOR, ID, NAME.
                    var attrIdDepth = '';
                    var attrNameDepth = '';

                    if (_container.is('.module-menu_items_wrap')) {
                        attrIdDepth = index;
                        attrNameDepth = '[' + index + ']';
                    } else {
                        var targetID = _container.closest('.module-menu_item_wrap').prop('id');
                        var targetIdRows = targetID.match(/\w+$/);
                        var targetNameRows = targetIdRows[0].replaceAll('_', '][rows][');

                        attrIdDepth = targetIdRows[0] + '_' + index;
                        attrNameDepth = '[' + targetNameRows + '][rows][' + index + ']';
                    }

                    // Params to change attributes.
                    var dataChange = {
                        mode           : 'sort',
                        menu           : menu,
                        storeIdCurrent : store,
                        storeIdTarget  : store,
                        attrIdDepth    : attrIdDepth,
                        attrNameDepth  : attrNameDepth
                    };

                    // Change attributes.
                    _this = dmenuChangeAttributes(_this, dataChange);

                    // Recursion.
                    if (_this.find('.nested-sortable').first().children().length > 0) {
                        dmenuRepairAttributes(_this.find('.nested-sortable').first().prop('id'));
                    }
                }
            });
        } else {
            return;
        }
    }

    // Change attributes.
    function dmenuChangeAttributes(jqHTML, data) {
        var jqElement = null;
        var regExp = null;
        var attrTarget = null;
        var attrCurrent = null;

        // Change attributes: FOR.
        jqHTML.find('label').each(function(index, element) {
            jqElement = $(element);

            if (jqElement.is('[for]')) {
                attrCurrent = jqElement.prop('for');

                if (data.mode == 'sort') {
                    regExp = new RegExp(data.menu + '-store_' + data.storeIdCurrent + '-item-\\w+-', 'g');
                    attrTarget = attrCurrent.replace(regExp, data.menu + '-store_' + data.storeIdTarget + '-item-' + data.attrIdDepth + '-');
                } else {
                    regExp = new RegExp(data.menu + '-store_' + data.storeIdCurrent + '-item-', 'g');
                    attrTarget = attrCurrent.replace(regExp, data.menu + '-store_' + data.storeIdTarget + '-item-');
                }

                jqElement.attr('for', attrTarget).prop('for', attrTarget);
            }
        });

        // Set attributes: ID.
        if (data.mode == 'sort') {
            jqHTML.attr('id', data.menu + '-store_' + data.storeIdTarget + '-item-' + data.attrIdDepth).prop('id', data.menu + '-store_' + data.storeIdTarget + '-item-' + data.attrIdDepth);
        }

        // Change attributes: ID, NAME.
        jqHTML.find('input, select, a, img').each(function(index, element) {
            jqElement = $(element);

            if (jqElement.is('[id]')) {
                attrCurrent = jqElement.prop('id');

                if (data.mode == 'sort') {
                    regExp = new RegExp(data.menu + '-store_' + data.storeIdCurrent + '-item-\\w+-', 'g');
                    attrTarget = attrCurrent.replace(regExp, data.menu + '-store_' + data.storeIdTarget + '-item-' + data.attrIdDepth + '-');
                } else {
                    regExp = new RegExp(data.menu + '-store_' + data.storeIdCurrent + '-item-', 'g');
                    attrTarget = attrCurrent.replace(regExp, data.menu + '-store_' + data.storeIdTarget + '-item-');
                }

                jqElement.attr('id', attrTarget).prop('id', attrTarget);
            }

            if (jqElement.is('[name]')) {
                attrCurrent = jqElement.prop('name');

                if (data.mode == 'sort') {
                    regExp = new RegExp('module_dmenu_editor_items_' + data.menu + '_' + data.storeIdCurrent + '[\\w\\[\\]]+?\\[data\\]', 'g');
                    attrTarget = attrCurrent.replace(regExp, 'module_dmenu_editor_items_' + data.menu + '_' + data.storeIdTarget + data.attrNameDepth + '[data]');
                } else {
                    regExp = new RegExp('module_dmenu_editor_items_' + data.menu + '_' + data.storeIdCurrent + '\\[', 'g');
                    attrTarget = attrCurrent.replace(regExp, 'module_dmenu_editor_items_' + data.menu + '_' + data.storeIdTarget + '[');
                }

                jqElement.attr('name', attrTarget).prop('name', attrTarget);
            }
        });

        // Change attributes: data-oc-target, data-oc-thumb.
        jqHTML.find('button').each(function(index, element) {
            jqElement = $(element);

            if (jqElement.is('[data-oc-target]')) {
                attrCurrent = jqElement.attr('data-oc-target');

                if (data.mode == 'sort') {
                    regExp = new RegExp(data.menu + '-store_' + data.storeIdCurrent + '-item-\\w+-', 'g');
                    attrTarget = attrCurrent.replace(regExp, data.menu + '-store_' + data.storeIdTarget + '-item-' + data.attrIdDepth + '-');
                } else {
                    regExp = new RegExp(data.menu + '-store_' + data.storeIdCurrent + '-item-', 'g');
                    attrTarget = attrCurrent.replace(regExp, data.menu + '-store_' + data.storeIdTarget + '-item-');
                }

                jqElement.attr('data-oc-target', attrTarget).data('oc-target', attrTarget);
            }

            if (jqElement.is('[data-oc-thumb]')) {
                attrCurrent = jqElement.attr('data-oc-thumb');

                if (data.mode == 'sort') {
                    regExp = new RegExp(data.menu + '-store_' + data.storeIdCurrent + '-item-\\w+-', 'g');
                    attrTarget = attrCurrent.replace(regExp, data.menu + '-store_' + data.storeIdTarget + '-item-' + data.attrIdDepth + '-');
                } else {
                    regExp = new RegExp(data.menu + '-store_' + data.storeIdCurrent + '-item-', 'g');
                    attrTarget = attrCurrent.replace(regExp, data.menu + '-store_' + data.storeIdTarget + '-item-');
                }

                jqElement.attr('data-oc-thumb', attrTarget).data('oc-thumb', attrTarget);
            }
        });

        // Change sortable container attributes: ID.
        jqHTML.find('.module-menu_items_wrap_content').each(function(index, element) {
            jqElement = $(element);

            if (jqElement.is('[id]')) {
                attrCurrent = jqElement.prop('id');

                if (data.mode == 'sort') {
                    regExp = new RegExp('module_menu_' + data.menu + '_store_' + data.storeIdCurrent + '_nested_sortable-\\w+', 'g');
                    attrTarget = attrCurrent.replace(regExp, 'module_menu_' + data.menu + '_store_' + data.storeIdTarget + '_nested_sortable-' + data.attrIdDepth);
                } else {
                    regExp = new RegExp('module_menu_' + data.menu + '_store_' + data.storeIdCurrent + '_nested_sortable-', 'g');
                    attrTarget = attrCurrent.replace(regExp, 'module_menu_' + data.menu + '_store_' + data.storeIdTarget + '_nested_sortable-');
                }

                jqElement.attr('id', attrTarget).prop('id', attrTarget);
            }
        });

        return jqHTML;
    }

    // HTML Menu Item.
    function dmenuGetMenuItem(data) {
        var menuItemHTML = '';

        menuItemHTML += '<div class="module-dmenu_editor-item">';
            menuItemHTML += '<div class="text-left module-dmenu_editor-item_title">';
                menuItemHTML += '<span class="text">' + data.title + '</span>';

                menuItemHTML += '<span class="buttons">';
                    menuItemHTML += '<span class="notice">' + data.titleNotice + '</span>';

                    if (data.urlLink && data.layout != 'custom') {
                        menuItemHTML += '<a href="/' + data.urlLink + '" class="a_item_href" target="_blank">';
                            menuItemHTML += '<i class="fas fa-eye fa_item_href" data-bs-toggle="tooltip" title="' + dmenu_translated_text['button_look_tip'] + '"></i>';
                        menuItemHTML += '</a>';
                    } else {
                        menuItemHTML += '<a class="a_item_href"></a>';
                    }

                    menuItemHTML += '<i class="fas fa-trash-alt fa_row_remove" data-bs-toggle="tooltip" title="' + dmenu_translated_text['button_remove_item_tip'] + '"></i>';
                    menuItemHTML += '<i class="fas fa-angle-down fa_arrow_open" data-bs-toggle="tooltip" title="' + dmenu_translated_text['button_edit_item_tip'] + '"></i>';
                menuItemHTML += '</span>';
            menuItemHTML += '</div>';

            menuItemHTML += '<div class="module-dmenu_editor-item_content" style="display: none;">';
                menuItemHTML += '<div class="card-body">';
                    menuItemHTML += '<div class="field row required">';
                        menuItemHTML += '<label class="col-sm-2 control-label col-form-label" for="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-names_' + dmenu_configLanguageID + '">' + dmenu_translated_text['entry_name'] + '</label>';
                        menuItemHTML += '<div class="col-sm-10 module-dmenu_editor-item_title_languages">';

                            for (language in dmenu_languages) {
                                menuItemHTML += '<div class="input-group pull-left">';
                                    menuItemHTML += '<span class="input-group-text">';
                                        menuItemHTML += '<img src="language/' + dmenu_languages[language]["code"] + '/' + dmenu_languages[language]["code"] + '.png" title="' + dmenu_languages[language]["name"] + '" />';
                                    menuItemHTML += '</span>';

                                    menuItemHTML += '<input type="text" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][names][' + dmenu_languages[language]["language_id"] + ']" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-names_' + dmenu_languages[language]["language_id"] + '" class="form-control name_' + dmenu_languages[language]["language_id"] + '" value="' + data.names[dmenu_languages[language]["language_id"]] + '" required>';
                                menuItemHTML += '</div>';
                            }

                            menuItemHTML += '<div class="input-group pull-left">';
                                menuItemHTML += '<div class="form-check checkbox">';
                                    menuItemHTML += '<input type="checkbox" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][names_hide]" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-names_hide" class="form-check-input names_hide" value="1">';
                                    menuItemHTML += '<label for="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-names_hide">' + dmenu_translated_text['entry_name_hide'] + '</label>';
                                menuItemHTML += '</div>';
                            menuItemHTML += '</div>';

                        menuItemHTML += '</div>';
                    menuItemHTML += '</div>';

                    if (data.seoDisplay) {
                        menuItemHTML += '<div class="field row required">';
                            menuItemHTML += '<label class="col-sm-2 control-label col-form-label" for="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-url-seo_' + dmenu_configLanguageID + '">' + dmenu_translated_text['entry_url'] + '</label>';
                            menuItemHTML += '<div class="col-sm-10 module-dmenu_editor-item_title_languages item_url">';

                                for (language in dmenu_languages) {
                                    menuItemHTML += '<div class="input-group pull-left">';
                                        menuItemHTML += '<span class="input-group-text">';
                                            menuItemHTML += '<img src="language/' + dmenu_languages[language]["code"] + '/' + dmenu_languages[language]["code"] + '.png" title="' + dmenu_languages[language]["name"] + '" />';
                                        menuItemHTML += '</span>';

                                        menuItemHTML += '<input type="text" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][url][seo][' + dmenu_languages[language]["language_id"] + ']" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-url-seo_' + dmenu_languages[language]["language_id"] + '" class="form-control url_seo_' + dmenu_languages[language]["language_id"] + '" value="" ' + data.fieldReadonly + '>';

                                        if (data.fieldReadonly) {
                                            menuItemHTML += '<i class="fas fa-lock" data-bs-toggle="tooltip" data-lock="' + dmenu_translated_text['button_lock_tip'] + '" data-unlock="' + dmenu_translated_text['button_unlock_tip'] + '" title="' + dmenu_translated_text['button_unlock_tip'] + '"></i>';
                                        } else {
                                            menuItemHTML += '<i class="fas fa-unlock" data-bs-toggle="tooltip" data-lock="' + dmenu_translated_text['button_lock_tip'] + '" data-unlock="' + dmenu_translated_text['button_unlock_tip'] + '" title="' + dmenu_translated_text['button_lock_tip'] + '"></i>';
                                        }
                                    menuItemHTML += '</div>';
                                }

                            menuItemHTML += '</div>';
                        menuItemHTML += '</div>';
                    }

                    menuItemHTML += '<div class="field row">';
                        menuItemHTML += '<label class="col-sm-2 control-label col-form-label" for="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-target">' + dmenu_translated_text['entry_target'] + '</label>';
                        menuItemHTML += '<div class="col-sm-10 module-dmenu_editor-item_field">';
                            menuItemHTML += '<select name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][target]" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-target" class="form-select">';
                                menuItemHTML += '<option value="">' + dmenu_translated_text['text_select_none'] + '</option>';
                                menuItemHTML += '<option value="_self">' + dmenu_translated_text['text_target_self'] + '</option>';
                                menuItemHTML += '<option value="_blank">' + dmenu_translated_text['text_target_blank'] + '</option>';
                                menuItemHTML += '<option value="_parent">' + dmenu_translated_text['text_target_parent'] + '</option>';
                                menuItemHTML += '<option value="_top">' + dmenu_translated_text['text_target_top'] + '</option>';
                            menuItemHTML += '</select>';
                        menuItemHTML += '</div>';
                    menuItemHTML += '</div>';

                    menuItemHTML += '<div class="field row">';
                        menuItemHTML += '<label class="col-sm-2 control-label col-form-label" for="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-xfn">' + dmenu_translated_text['entry_xfn'] + '</label>';

                        menuItemHTML += '<div class="col-sm-10 module-dmenu_editor-item_field">';
                            menuItemHTML += '<input name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][xfn]" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-xfn" class="form-control" value="">';
                        menuItemHTML += '</div>';
                    menuItemHTML += '</div>';

                    menuItemHTML += '<div class="field row">';
                        menuItemHTML += '<label class="col-sm-2 control-label col-form-label" for="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-class">' + dmenu_translated_text['entry_class'] + '</label>';
                        menuItemHTML += '<div class="col-sm-10 module-dmenu_editor-item_field">';
                            menuItemHTML += '<input name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][class]" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-class" class="form-control" value="">';
                        menuItemHTML += '</div>';
                    menuItemHTML += '</div>';

                    menuItemHTML += '<div class="field row">';
                        menuItemHTML += '<label class="col-sm-2 control-label col-form-label">' + dmenu_translated_text['entry_icon'] + '</label>';
                        menuItemHTML += '<div class="col-sm-10 module-dmenu_editor-item_field">';
                            menuItemHTML += '<div class="card image module_dmenu_editor-placeholder">';
                                menuItemHTML += '<img src="' + dmenu_itemPlaceholder + '" alt="' + dmenu_translated_text['entry_icon'] + '" title="' + dmenu_translated_text['entry_icon'] + '" data-oc-placeholder="' + dmenu_itemPlaceholder + '" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-icon-thumb" class="card-img-top">';
                                menuItemHTML += '<input type="hidden" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][icon][image]" value="" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-icon-image" class="hidden">';

                                menuItemHTML += '<div class="card-body">';
                                    menuItemHTML += '<button type="button" data-oc-toggle="image" data-oc-target="#' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-icon-image" data-oc-thumb="#' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-icon-thumb" class="btn btn-primary btn-sm btn-block"><i class="fas fa-pencil-alt"></i> ' + dmenu_translated_text['button_edit'] + '</button> ';
                                    menuItemHTML += '<button type="button" data-oc-toggle="clear" data-oc-target="#' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-icon-image" data-oc-thumb="#' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-icon-thumb" class="btn btn-warning btn-sm btn-block"><i class="fas fa-trash-alt"></i> ' + dmenu_translated_text['button_clear'] + '</button>';
                                menuItemHTML += '</div>';
                            menuItemHTML += '</div>';
                        menuItemHTML += '</div>';
                    menuItemHTML += '</div>';

                    menuItemHTML += '<div class="field row">';
                        menuItemHTML += '<label class="col-sm-2 control-label col-form-label" for="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-status">' + dmenu_translated_text['entry_status'] + '</label>';

                        menuItemHTML += '<div class="col-sm-10 module-dmenu_editor-item_field">';
                            menuItemHTML += '<div class="input-group">';
                                menuItemHTML += '<div class="form-check form-switch form-switch-lg">';
                                    menuItemHTML += '<input type="hidden" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][status]" value="0" class="hidden"/>';
                                    menuItemHTML += '<input type="checkbox" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][status]" value="1" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-status" class="form-check-input field-status" checked>';
                                menuItemHTML += '</div>';
                            menuItemHTML += '</div>';
                        menuItemHTML += '</div>';
                    menuItemHTML += '</div>';

                    menuItemHTML += '<div>';
                        menuItemHTML += '<input type="hidden" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][url][link]" class="hidden" value="' + data.urlLink + '">';
                        menuItemHTML += '<input type="hidden" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][id]" class="hidden" value="' + data.id + '">';
                        menuItemHTML += '<input type="hidden" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][layout]" class="hidden" value="' + data.layout + '">';
                    menuItemHTML += '</div>';
                menuItemHTML += '</div>';
            menuItemHTML += '</div>';

            menuItemHTML += '<span class="status enabled"></span>';
        menuItemHTML += '</div>';

        menuItemHTML += '<div class="module-dmenu_editor-item_content_sortable">';
            menuItemHTML += '<div id="module_menu_' + data.menu + '_store_' + data.store + '_nested_sortable-' + data.attrIdDepth + '" class="module-menu_items_wrap_content nested-sortable"></div>';
        menuItemHTML += '</div>';

        // Return.
        return menuItemHTML;
    }

    // HTML Catalog Menu Item.
    function dmenuGetCatalogMenuItem(data) {
        var menuItemHTML = '';

        menuItemHTML += '<div class="module-dmenu_editor-item">';
            menuItemHTML += '<div class="text-left module-dmenu_editor-item_title">';
                menuItemHTML += '<span class="text">' + dmenu_translated_text['text_result_categories'] + '</span>';

                menuItemHTML += '<span class="buttons">';
                    menuItemHTML += '<span class="notice">' + dmenu_translated_text['text_item_desc_catalog'] + '</span>';
                    menuItemHTML += '<a class="a_item_href"></a>';
                    menuItemHTML += '<i class="fas fa-trash-alt fa_row_remove" data-bs-toggle="tooltip" title="' + dmenu_translated_text['button_remove_item_tip'] + '"></i>';
                    menuItemHTML += '<i class="fas fa-angle-down fa_arrow_open" data-bs-toggle="tooltip" title="' + dmenu_translated_text['button_edit_item_tip'] + '"></i>';
                menuItemHTML += '</span>';
            menuItemHTML += '</div>';

            menuItemHTML += '<div class="module-dmenu_editor-item_content" style="display: none;">';
                menuItemHTML += '<div class="card-body">';
                    menuItemHTML += '<div class="row setting-category_menu">';
                        menuItemHTML += '<label class="col-sm-2 control-label col-form-label" for="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-category_menu">' + dmenu_translated_text['entry_category_menu'] + '</label>';

                        menuItemHTML += '<div class="col-sm-10 module-dmenu_editor-item_field">';
                            menuItemHTML += '<select name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][category_menu]" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-category_menu" class="form-select">';
                                menuItemHTML += '<option value="1">' + dmenu_translated_text['text_yes'] + '</option>';
                                menuItemHTML += '<option value="0" selected="selected">' + dmenu_translated_text['text_no'] + '</option>';
                            menuItemHTML += '</select>';
                        menuItemHTML += '</div>';
                    menuItemHTML += '</div>';

                    menuItemHTML += '<div class="setting-category_menu_hidden_block hidden">';
                        menuItemHTML += '<div class="row setting-category_menu_title required">';
                            menuItemHTML += '<label class="col-sm-2 control-label col-form-label" for="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-name_' + dmenu_configLanguageID + '">' + dmenu_translated_text['entry_category_menu_title'] + '</label>';

                            menuItemHTML += '<div class="col-sm-10 module-dmenu_editor-item_field">';

                                for (language in dmenu_languages) {
                                    menuItemHTML += '<div class="input-group pull-left" style="margin-bottom: 5px;">';
                                        menuItemHTML += '<span class="input-group-text">';
                                            menuItemHTML += '<img src="language/' + dmenu_languages[language]["code"] + '/' + dmenu_languages[language]["code"] + '.png" title="' + dmenu_languages[language]["name"] + '" />';
                                        menuItemHTML += '</span>';

                                        menuItemHTML += '<input type="text" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-name_' + dmenu_languages[language]["language_id"] + '" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][category_menu_names][' + dmenu_languages[language]["language_id"] + ']" class="form-control name_' + dmenu_languages[language]["language_id"] + '" value="' + data.names[dmenu_languages[language]["language_id"]] + '">';
                                    menuItemHTML += '</div>';
                                }

                            menuItemHTML += '</div>';
                        menuItemHTML += '</div>';

                        menuItemHTML += '<div class="row setting-category_menu_icon">';
                            menuItemHTML += '<label class="col-sm-2 control-label col-form-label">' + dmenu_translated_text['entry_icon'] + '</label>';

                            menuItemHTML += '<div class="col-sm-10 module-dmenu_editor-item_field">';
                                menuItemHTML += '<div class="card image module_dmenu_editor-placeholder">';
                                    menuItemHTML += '<img src="' + dmenu_itemPlaceholder + '" alt="' + dmenu_translated_text['entry_icon'] + '" title="' + dmenu_translated_text['entry_icon'] + '" data-oc-placeholder="' + dmenu_itemPlaceholder + '" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-icon-thumb" class="card-img-top">';
                                    menuItemHTML += '<input type="hidden" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][icon][image]" value="" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-icon-image" class="hidden">';

                                    menuItemHTML += '<div class="card-body">';
                                        menuItemHTML += '<button type="button" data-oc-toggle="image" data-oc-target="#' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-icon-image" data-oc-thumb="#' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-icon-thumb" class="btn btn-primary btn-sm btn-block"><i class="fas fa-pencil-alt"></i> ' + dmenu_translated_text['button_edit'] + '</button> ';
                                        menuItemHTML += '<button type="button" data-oc-toggle="clear" data-oc-target="#' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-icon-image" data-oc-thumb="#' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-icon-thumb" class="btn btn-warning btn-sm btn-block"><i class="fas fa-trash-alt"></i> ' + dmenu_translated_text['button_clear'] + '</button>';
                                    menuItemHTML += '</div>';
                                menuItemHTML += '</div>';
                            menuItemHTML += '</div>';
                        menuItemHTML += '</div>';
                    menuItemHTML += '</div>';

                    menuItemHTML += '<div class="field row">';
                        menuItemHTML += '<label class="col-sm-2 control-label col-form-label" for="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-status">' + dmenu_translated_text['entry_status'] + '</label>';

                        menuItemHTML += '<div class="col-sm-10 module-dmenu_editor-item_field">';
                            menuItemHTML += '<div class="input-group">';
                                menuItemHTML += '<div class="form-check form-switch form-switch-lg">';
                                    menuItemHTML += '<input type="hidden" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][status]" value="0" class="hidden"/>';
                                    menuItemHTML += '<input type="checkbox" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][status]" value="1" id="' + data.menu + '-store_' + data.store + '-item-' + data.attrIdDepth + '-data-status" class="form-check-input field-status" checked>';
                                menuItemHTML += '</div>';
                            menuItemHTML += '</div>';
                        menuItemHTML += '</div>';
                    menuItemHTML += '</div>';
                menuItemHTML += '</div>';
            menuItemHTML += '</div>';

            menuItemHTML += '<span class="status enabled"></span>';
        menuItemHTML += '</div>';

        menuItemHTML += '<div>';
            menuItemHTML += '<input type="hidden" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][url][link]" class="hidden" value="">';
            menuItemHTML += '<input type="hidden" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][id]" class="hidden" value="0">';
            menuItemHTML += '<input type="hidden" name="module_dmenu_editor_items_' + data.menu + '_' + data.store + data.attrNameDepth + '[data][layout]" class="hidden" value="' + data.layout + '">';
        menuItemHTML += '</div>';

        // Return.
        return menuItemHTML;
    }

    // HTML Missing message.
    function dmenuDisplayMessageMissing() {
        var textMissing = '';

        textMissing += '<div class="not_repair sortable_filtered">';
            textMissing += '<div class="module-menu_items_missing_text">' + dmenu_translated_text['text_menu_item_missing'] + '</div>';
        textMissing += '</div>';

        // Return.
        return textMissing;
    }

    // Timeout with message.
    var dmenuTimeoutClearMessage = null;
    function dmenuTimeoutMessage(container, html, classText) {
        container.addClass(classText).html(html);

        if (dmenuTimeoutClearMessage != null) {
            window.clearTimeout(dmenuTimeoutClearMessage);
        }

        dmenuTimeoutClearMessage = window.setTimeout(function(){
            container.removeClass('success error').html('');
        }, 3000);
    }
});