define(['jquery', 'moment', 'validation'], function ($, moment) {
	'use strict';
	$.widget('blog.form', {
    	options: {
            validCurrentPage: true
        },

		/**
		 * handle reply action
		 */
		replyAction: function() {
			$('.blog-comment-reply-link').click(function() {
				var parent = $(this).closest('li');
				$('.blog-comment-form').insertAfter(parent);
				$('#ccontent').trigger('focus');
				var parentId = parseInt($(this).data('parent-id'));
				$('.blog-comment-form').find('input[name=parent_id]').val(parentId);
			});
			$('.cancel-comment-reply-link').click(function(e) {
				$('.blog-comment-form').insertAfter('.blog-commentlist');
				$('.blog-comment-form').find('input[name=parent_id]').val(0);
			});
		},

        /**
         *
         * @private
         */
        _create: function () {
        	var self = this,
        		form = self.element;

        	this.element.mage('validation', {
        		submitHandler: function () {
        			var commentClass = '',
        				url = form.attr('data-url'),
    					date = moment(),
						month = (new Date).toUTCString().split(' ').slice(2,3).join(''),
						dateFormat = date.format("DD, Y \\a\\t H:m");

				    $.ajax({
				  		type: 'POST',
						url: form.attr('action'),
				  		data: form.serialize(),
				  		dataType: "json",
						cache: false,
						beforeSend: function() {
							form.append('<div class="page-loading"><div class="ajax-loading-comment"></div></div>');
						},
						success: function(data) {
				    		$('#blog-comment .actions-toolbar').prepend('<div class="message-success success message">Your comment has been successfully submitted</div>');
							$('#blog-comment .page-loading').remove();
							setTimeout(function(){
		                        $(".message-success").remove();
		                    },3000);
							if(data.comment_approve === '0'){
								if(data.comment_info.parent_id === '0') {
									commentClass = '#blog-post-comments .blog-block-content > ul';
								} else {
									if ($( `#blog-post-comments .blog-block-content #comment-` + data.comment_info.parent_id).find( "ul.blog-comment-children" ).length < 1) {
										$( `#blog-post-comments .blog-block-content #comment-` + data.comment_info.parent_id).append( '<ul class="blog-comment-children"></ul>' );
									}
									commentClass = `#blog-post-comments .blog-block-content ul #comment-` + data.comment_info.parent_id + ` .blog-comment-children`
								}

								$(commentClass).append(`<li id="comment-` + data.comment_info.comment_id + `">
									    <div class="blog-comment-wrapper">
									        <div class="blog-comment-avatar"><img src="` + data.image + `" height="65" width="65"></div>
									        <div class="blog-comment-content-wrapper">
									            <div class="blog-comment-author-wrapper">
									                <div class="blog-comment-author"><span>` + $('#cname').val() + `</span></div>
									                <div class="blog-comment-meta"><a href="` + url + `#comment-` + data.comment_info.comment_id + `">` + month + ` ` + dateFormat + `</a></div>
									            </div>
									            <div class="blog-comment-content">` + $('#ccontent').val().replace(/\n/g, "</br>") + `</div>
									            <div class="blog-comment-content-reply"><span class="blog-comment-reply-link" data-parent-id="` + data.comment_info.comment_id + `">Reply</span></div>
									        </div>
									    </div>
									</li>`
								);
								$('#blog-post-comments > .blog-block-title > h3 > span').html($('#blog-post-comments .blog-block-content > ul > li').length + " comments");
								self.replyAction();
							}

							$('#blog-comment')[0].reset();
						},
						error: function() {
							$('#blog-comment .actions-toolbar').prepend('<div class="message-error error message">An error occurred. Please try again !</div>');
							setTimeout(function(){
		                        $(".message-error").remove();
		                    },3000);
						}
					});
					return false;
        		}
        	});
        }
    });

    return $.blog.form;
})

