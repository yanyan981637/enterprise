require(['jquery', 'mage/accordion'], function ($) {
    $(document).ready(function () {
        $('#narrow-by-list').accordion({
            collapsible: true,
            active: true, // 強制展開
            openedState: 'active',
            multipleCollapsible: false
        });
        // 強制移除隱藏樣式
        $('.filter-options-content').css('display', 'block');
    });
});
