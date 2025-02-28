define([
    'jquery',
    'jquery-ui-modules/widget',
    'debounce'
], function ($, _ ,debounce) {
    'use strict';

    const breakpoint = 1280;

    let currentDevice = 'desktop';



    $.widget('magezon.mgzTabs', {
        _create: function () {
            var self = this;

            const initTab = debounce(function () {
                currentDevice = $(window).width() >= breakpoint ? 'desktop' : 'mobile';

                if(currentDevice === 'mobile') {

                    $tabsList.children().removeClass('mgz-active');
                    $tabsContent.children().removeClass('mgz-active');
                }

                if(currentDevice === 'desktop') {
                    $tabsList.children().removeClass('mgz-active');
                    $tabsContent.children().removeClass('mgz-active');
                    $tabsList.children().eq(0).addClass('mgz-active');
                    $tabsContent.children('.mgz-tabs-tab-title').eq(0).addClass('mgz-active');
                    $tabsContent.children('.mgz-tabs-tab-content').eq(0).addClass('mgz-active');
                }
            },10)

            $('.mgz-tabs-tab-content:not(.mgz-active) .owl-carousel').addClass('mgz-carousel-hidden');

            var $tabsList    = this.element.children('.mgz-tabs-nav');
            var $tabsContent = this.element.children('.mgz-tabs-content');

            $tabsList.children('.mgz-tabs-tab-title').each(function(index, el) {
                var outerHTML = $(this)[0].outerHTML;
                var anchor    = $(this).children('a');
                var targetId  = $(this).children('a').data('id');
                if (targetId) {
                    self.element.find(targetId).before(outerHTML);
                }
            });


            initTab();

            var activeTab = function(tab) {
                $tabsList.children().removeClass('mgz-active');
                $tabsContent.children().removeClass('mgz-active');
                var parentId = tab.parent().attr('data-id');
                self.element.find('.' + parentId).addClass('mgz-active');
                var targetId = tab.data('id') ? tab.data('id') : tab.attr('href');
                var target = self.element.find(targetId);
                target.addClass('mgz-active');
                $(self.element).parents('.mgz-element').trigger('mgz:change');
                setTimeout(function() {
                    target.find('.owl-carousel.mgz-carousel-hidden').removeClass('mgz-carousel-hidden');
                }, 500);

                return true;
            }

            if (this.options.hover_active) {
                $tabsList.children().hover(function(e) {
                    if (currentDevice === 'desktop'){
                        activeTab($(this).children('a'));
                    }
                });
            }

            $tabsList.children().click(function(e) {
                if ($(this).children('a').attr('href').indexOf('#') !== -1) {
                    e.preventDefault();
                    activeTab($(this).children('a'));
                    return false;
                }
            });


            // 上面的過程中已使$tabsContent 中包含title, 故而可以 獲取到。
            $tabsContent.children('.mgz-tabs-tab-title').find('.tabs-opener').click(function(e) {
                e.preventDefault();
                // mobile 端 檢測
                if (currentDevice === 'mobile'){

                    $(this).parent().parent().toggleClass('mgz-active');
                    var tabId = $(this).parent().attr('data-id');
                    // ..

                    if($(this).parent().parent().hasClass('mgz-active')){
                        $(tabId).stop(true, true).slideDown(300, function(e){
                            $(tabId).addClass('mgz-active').attr('style', '')
                        });
                    }else{
                        $(tabId).stop(true, true).slideUp(300, function(e){
                            $(tabId).removeClass('mgz-active').attr('style', '')
                        });
                    }
                }
                return false;
            });

            $(window).resize(initTab)
        }
    });

    return $.magezon.mgzTabs;
});
