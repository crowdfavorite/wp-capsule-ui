(function($) {

	window.editors = {},
	window.Capsule = {},
	Capsule.delaySave = {};

	Capsule.spinner = function (text) {
		if (typeof text == 'undefined') {
			text = capsuleL10n.loading;
		}
		return '<div class="spinner"><span>' + text + '</span></div>';
	};
	
	Capsule.authCheck = function(response) {
		if (typeof response.result != 'undefined' && response.result == 'unauthorized') {
			alert(response.msg);
			location.href = response.login_url + '?redirect_to=' + encodeURIComponent(location.href);
			return false;
		}
		return true;
	};
	
	Capsule.get = function(url, args, callback, type) {
		$.get(url, args, function(response) {
			if (Capsule.authCheck(response)) {
				callback.call(this, response);
			}
		}, type);
	};
	
	Capsule.post = function(url, args, callback, type) {
		$.post(url, args, function(response) {
			if (Capsule.authCheck(response)) {
				callback.call(this, response);
			}
		}, type);
	};
	
	Capsule.loadExcerpt = function($article, postId) {
		$article.addClass('unstyled').children().addClass('transparent').end()
			.append(Capsule.spinner());
		Capsule.get(
			capsuleL10n.endpointAjax,
			{
				capsule_action: 'post_excerpt',
				post_id: postId
			},
			function(response) {
				if (response.html) {
					$article.replaceWith(response.html);
					$article = $('#post-content-' + postId);
					Capsule.postExpandable($article);
					$('#post-content-' + postId).scrollintoview({ offset: 10 });
				}
			},
			'json'
		);
	};

	Capsule.centerEditor = function(postId) {
		$.scrollTo('#post-edit-' + postId, {offset: -10});
	};

	Capsule.loadEditor = function($article, postId) {
		$article.addClass('unstyled').children().addClass('transparent').end()
			.append(Capsule.spinner());
		Capsule.get(
			capsuleL10n.endpointAjax,
			{
				capsule_action: 'post_editor',
				post_id: postId
			},
			function(response) {
				if (response.html) {
					$article.replaceWith(response.html);
					Capsule.centerEditor(postId);
					Capsule.sizeEditor();
					Capsule.initEditor(postId, response.content);
				}
			},
			'json'
		);
	};
	
	Capsule.createPost = function($article) {
		$article.addClass('unstyled').children().addClass('transparent').end()
			.append(Capsule.spinner());
		Capsule.post(
			capsuleL10n.endpointAjax,
			{
				capsule_action: 'create_post'
			},
			function(response) {
				if (response.html) {
					$article.replaceWith(response.html);
					Capsule.centerEditor(response.post_id);
					Capsule.sizeEditor();
					Capsule.initEditor(response.post_id, '');
				}
			},
			'json'
		);
	};
	
	Capsule.watchForEditorChanges = function(postId, $article, suppress_time_display) {
		if (typeof $article == 'undefined') {
			$article = $('#post-edit-' + postId);
		}
		if (typeof suppress_time_display == 'undefined') {
			suppress_time_display = false;
		}

		var timestamp = (new Date()).getTime() / 1000,
			updated = date('g:i a', timestamp),
			save_cb = function() {
				Capsule.delaySave[postId] = null;
				Capsule.updatePost(postId, window.editors[postId].getSession().getValue());
			},
			change_cb = function() {
				$article.clearQueue().addClass('dirty');
				if (Capsule.delaySave[postId]) {
					clearTimeout(Capsule.delaySave[postId]);
				}
				Capsule.delaySave[postId] = setTimeout(save_cb, 10000);
				window.editors[postId].getSession().removeEventListener('change', change_cb);
				return true;
			};
		if (!suppress_time_display) {
			$article.find('span.post-last-saved').html(updated);
		}
		window.editors[postId].getSession().on('change', change_cb);

		// Debounce clearing the dirty flag slightly
		$article.delay(50).queue(function() {
			$(this).removeClass('dirty').dequeue();
		});
	};

	Capsule.updatePost = function(postId, content, $article, loadExcerpt) {
		if (typeof loadExcerpt == 'undefined') {
			loadExcerpt = false;
		}
		if (typeof $article == 'undefined') {
			$article = $('#post-edit-' + postId);
		}
		if (loadExcerpt) {
			$article.addClass('unstyled')
				.children().addClass('transparent').end()
				.append(Capsule.spinner());
		}
		else {
			$article.addClass('saving');
		}
		// strip code blocks before extracting projects and tags
		var prose = content.replace(/^```([^]+?)^```/mg, '')
				.replace(/<pre>([^]+?)<\/pre>/mg, '')
				.replace(/<code>([^]+?)<\/code>/mg, ''),
			projects = twttr.txt.extractMentions(prose),
			tags = twttr.txt.extractHashtags(prose),
			code = Capsule.extractCodeLanguages(content);
		Capsule.post(
			capsuleL10n.endpointAjax,
			{
				capsule_action: 'update_post',
				post_id: postId,
				content: content,
				projects: JSON.stringify(projects),
				post_tag: JSON.stringify(tags),
				code: JSON.stringify(code)
			},
			function(response) {
				if (response.result == 'success') {
					if (loadExcerpt) {
						Capsule.loadExcerpt($article, postId);
					}
					else {
						$article.removeClass('saving');
						Capsule.watchForEditorChanges(postId, $article);
					}
				}
			},
			'json'
		);
	};
	
	Capsule.deletePost = function(postId, $article) {
		$article.addClass('unstyled').children().addClass('transparent').end()
			.append(Capsule.spinner());
		Capsule.post(
			capsuleL10n.endpointAjax,
			{
				capsule_action: 'delete_post',
				post_id: postId
			},
			function(response) {
				if (response.result == 'success') {
					$article.replaceWith(response.html);
				}
				else {
					alert(response.msg);
					$article.removeClass('unstyled').children().removeClass('transparent').end()
						.find('.spinner').remove();
				}
			},
			'json'
		);
	};
	
	Capsule.undeletePost = function(postId, $article) {
		$article.addClass('unstyled').children().addClass('transparent').end()
			.append(Capsule.spinner());
		Capsule.post(
			capsuleL10n.endpointAjax,
			{
				capsule_action: 'undelete_post',
				post_id: postId
			},
			function(response) {
				if (response.result == 'success') {
					$article.replaceWith(response.html);
					$article = $('#post-content-' + postId);
					Capsule.postExpandable($article);
				}
				else {
					alert(response.msg);
					$article.removeClass('unstyled').children().removeClass('transparent').end()
						.find('.spinner').remove();
				}
			},
			'json'
		);
	};
	
	Capsule.stickPost = function(postId, $article) {
		$article.addClass('sticky-loading');
		Capsule.post(
			capsuleL10n.endpointAjax,
			{
				capsule_action: 'stick_post',
				post_id: postId
			},
			function(response) {
				if (response.result == 'success') {
					$article.addClass('sticky').removeClass('sticky-loading');
				}
				else {
					alert(response.msg);
				}
			},
			'json'
		);
	};
	
	Capsule.unstickPost = function(postId, $article) {
		$article.addClass('sticky-loading');
		Capsule.post(
			capsuleL10n.endpointAjax,
			{
				capsule_action: 'unstick_post',
				post_id: postId
			},
			function(response) {
				if (response.result == 'success') {
					$article.removeClass('sticky sticky-loading');
				}
				else {
					alert(response.msg);
				}
			},
			'json'
		);
	};
	
	Capsule.initEditor = function(postId, content) {
		window.Capsule.CFMarkdownMode = require("cf/js/syntax/cfmarkdown").Mode;
		window.editors[postId] = ace.edit('ace-editor-' + postId);
		window.editors[postId].getSession().setUseWrapMode(true);
		window.editors[postId].getSession().setMode('cf/js/syntax/cfmarkdown');
		window.editors[postId].setShowPrintMargin(false);
		window.editors[postId].setTheme('ace/theme/twilight');
		window.editors[postId].getSession().setValue(content);
		window.editors[postId].container.style.lineHeight = '20px';
		window.editors[postId].renderer.setPadding(12);
		window.editors[postId].commands.addCommand({
			name: 'save',
			bindKey: {
				win: 'Ctrl-S',
				mac: 'Command-S'
			},
			exec: function(editor) {
				Capsule.updatePost(postId, editor.getSession().getValue());
			}
		});
		window.editors[postId].commands.addCommand({
			name: 'recenter',
			bindKey: {
				mac: 'Command-Shift-0',
				win: 'Ctrl-Shift-0'
			},
			exec: function(editor) {
				Capsule.centerEditor(postId);
			}
		});

		Capsule.watchForEditorChanges(postId, undefined, true);
		window.editors[postId].focus();
	};
	
	Capsule.sizeEditor = function() {
		$('.ace-editor:not(.resized)').each(function() {
			$(this).height(
				($(window).height() - $(this).closest('article').find('header').height() - 70) + 'px'
			);
		});
	};

	Capsule.saveAllEditors = function() {
		$('.ace-editor').each(function() {
			var $article = $(this).closest('article'),
				postId = $article.data('post-id');
			if ($article.hasClass('dirty')) {
				Capsule.updatePost(postId, window.editors[postId].getSession().getValue());
			}
		});
	}
	
	Capsule.extractCodeLanguages = function(content) {
		var block = new RegExp("^```[a-zA-Z]+\\s*$", "gm"),
			tag = new RegExp("[a-zA-Z]+", ""),
			tags = [],
			matches = content.match(block);
		if (matches != null && matches.length) {
			$.each(matches, function(i, val) {
				tags.push(val.match(tag)[0].replace(/^js$/i, "javascript"));
			});
		}
		return tags;
	};
	
	Capsule.postExpandable = function($article) {
		if ($article.find('.post-content:first')[0].scrollHeight > $article[0].scrollHeight) {
			$article.addClass('toggleable');
		}
	};
	
	$(function() {
		$(document).on('click', 'article.excerpt.toggleable .post-content', function(e) {
			// load full content on excerpt click
			$(this).closest('article.excerpt.toggleable').removeClass('excerpt').addClass('open');
		}).on('click', 'article:not(.excerpt, a.post-edit-link) .post-toggle', function(e) {
			// load excerpt on content click
			$(this).closest('article').removeClass('open').addClass('excerpt');
		}).on('click', 'article .post-edit-link', function(e) {
			// load editor
			var $article = $(this).closest('article'),
				postId = $article.data('post-id');
			Capsule.loadEditor($article, postId);
			e.preventDefault();
			// don't allow bubbling to load content
			if ($article.hasClass('excerpt')) {
				e.stopPropagation();
			}
		}).on('click', 'article .post-close-link', function(e) {
			e.preventDefault();
			// save content and load excerpt
			var $article = $(this).closest('article'),
				postId = $article.data('post-id');
			Capsule.updatePost(postId, window.editors[postId].getSession().getValue(), $article, true);
		}).on('click', 'article .post-save-link', function(e) {
			e.preventDefault();
			var $article = $(this).closest('article'),
				postId = $article.data('post-id');
			Capsule.updatePost(postId, window.editors[postId].getSession().getValue());
			window.editors[postId].focus();
		}).on('click', 'article .post-delete-link', function(e) {
			e.stopPropagation();
			e.preventDefault();
			var $article = $(this).closest('article'),
				postId = $article.data('post-id');
			Capsule.deletePost(postId, $article);
		}).on('click', 'article .post-undelete-link', function(e) {
			e.stopPropagation();
			e.preventDefault();
			var $article = $(this).closest('article'),
				postId = $article.data('post-id');
			Capsule.undeletePost(postId, $article);
		}).on('click', 'article:not(.sticky) .post-stick-link', function(e) {
			e.stopPropagation();
			e.preventDefault();
			var $article = $(this).closest('article'),
				postId = $article.data('post-id');
			Capsule.stickPost(postId, $article);
		}).on('click', 'article.sticky .post-stick-link', function(e) {
			e.stopPropagation();
			e.preventDefault();
			var $article = $(this).closest('article'),
				postId = $article.data('post-id');
			Capsule.unstickPost(postId, $article);
		}).on('mousewheel', 'article.edit', function(e) {
			e.preventDefault();
		}).on('click', '.post-new-link', function(e) {
			e.preventDefault();
			var $article = $('<article></article>').height('400px');
			$('.body').prepend($article);
			Capsule.createPost($article);
		});
		$(window).on('resize', function() {
			Capsule.sizeEditor();
		}).on('blur', function() {
			Capsule.saveAllEditors();
		})
		$('article').each(function() {
			Capsule.postExpandable($(this));
		});
		
	});

})(jQuery);
