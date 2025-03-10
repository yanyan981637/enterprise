require([
    'jquery'
], function ($) {
    'use strict';

    $(document).ready(function (){
        var selector = $('#post_post_content_type');

        const all_related_fields = ['post_content','post_content','event_start_date','event_end_date','post_external_link'];

        const related = {
          normal_news:['post_content'],
          event: ['event_start_date','event_end_date','post_external_link'],
          mitac_in_news:['post_external_link'],
        }

        function showRelatedFields(type) {
          if(!type || !related[type]){
            return;
          }
          all_related_fields.forEach(function (field) {
            console.log(field);
            $(`.field-${field}`).hide();
          })
          console.log(type);
          console.log(related[type])
          related[type].forEach(function(field){
            $(`.field-${field}`).show();
          })
        }
        // 初始化
        var init_content_type_val = selector.val() || 'normal_news';
        showRelatedFields(init_content_type_val);

        selector.change(function (e) {
            var post_content_type_val = e.target.value;
            if(post_content_type_val){
              showRelatedFields(post_content_type_val);
            }
        })

    })
});
