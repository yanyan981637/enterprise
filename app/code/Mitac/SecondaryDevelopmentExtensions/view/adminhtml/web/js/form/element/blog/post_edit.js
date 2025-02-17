require([
    'jquery'
], function ($) {
    'use strict';

    $(document).ready(function (){
        var selector = $('#post_post_content_type');
        var related = {
            'content': function (){
                $('.field-post_content').show();
                $('.field-post_external_link').hide()
            },
            'external_link': function (){
                $('.field-post_content').hide();
                $('.field-post_external_link').show()
            }
        }

        var init_content_type_val = selector.val() || 'content';
        related[init_content_type_val]();

        selector.change(function (e) {
            var post_content_type_val = e.target.value;
            if(post_content_type_val){
                related[post_content_type_val]();
            }
        })

    })
});
