if (typeof console === "undefined") {
	console = {
		log: function() { },
	};
}

// http://stackoverflow.com/questions/6312993/javascript-seconds-to-time-string-with-format-hhmmss
String.prototype.toHHMMSS = function () {
	var sec_num = parseInt(this, 10); // don't forget the second param
	var hours   = Math.floor(sec_num / 3600);
	var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
	var seconds = sec_num - (hours * 3600) - (minutes * 60);

	if (hours   < 10) {hours   = "0"+hours;}
	if (minutes < 10) {minutes = "0"+minutes;}
	if (seconds < 10) {seconds = "0"+seconds;}
	var time    = hours+':'+minutes+':'+seconds;
	return time;
}


var updateCurrentTimeout;


function setVoteKey(key) {
	$('#voteUp').attr('data-key', key);
	$('#voteDown').attr('data-key', key);
}

$(document).ready(function() {
	$('#voteUp').on('click', function() {
		// console.log( $(this).attr('data-key') );
		$.post('json_vote.php', {id:$(this).attr('data-key'), vote:'up'}, function(response) {
			// console.log(response);
			$().dpToast(response.success);
			try{ clearTimeout(updateCurrentTimeout); } catch(e) {}
			updateCurrent();
		});
	});


	$('#voteDown').on('click', function() {
		// console.log( $(this).attr('data-key') );
		$.post('json_vote.php', {id:$(this).attr('data-key'), vote:'down'}, function(response) {
			// console.log(response);
			$().dpToast(response.success);
			try{ clearTimeout(updateCurrentTimeout); } catch(e) {}
			updateCurrent();
		});
	});
});


function updateCurrent() {
	try {
		$.get('json_current.php', null, function(response) {
			$('#cNickname span').html(response.result[0].by_nickname);
			$('#cName').html(response.result[0].Name);
			$('#cArtist').html(response.result[0].Artist);
			$('#cAlbum span').html(response.result[0].Album);
			if(response.result[0].Composer != null) {
				$('#cComposer span').html(response.result[0].Composer).parent().show();
			} else {
				$('#cComposer').hide();
			}
			if(response.result[0].Year != null) {
				$('#cYear span').html(response.result[0].Year).parent().show();
			} else {
				$('#cYear').hide();
			}
			$('#cDuration span').html(response.result[0].duration.toHHMMSS());

			if(response.result[0].hasCover == 1) {
				$('#cCover').html('<img src="covers/' + response.result[0].TrackID + '_100x100.jpg" alt=""/>');
			} else {
				$('#cCover').html('<img src="img/keep-calm-and-party-on-garth_100x100.png" alt=""/>');
			}


			var $ul = $('#next-up'), html = '';
			$ul.html('<li><div class="ui-loader"><span class="ui-icon ui-icon-loading"></span></div></li>');
			try {
				$ul.listview('refresh');
				$ul.trigger('updatelayout');
			} catch(e) {}

			$.each(response.result, function(i, val) {
				if(val.running == null) {
					html += '<li data-icon="gear">';
						html += '<a href="#vote" data-rel="popup" data-position-to="window" data-transition="pop" onclick="setVoteKey(' + val.id + ');"><h3>' + val.Name + (val.Artist == null ? '' : ', ' + val.Artist) + ' (via ' + val.by_nickname + ')</h3> <span class="ui-li-count">' + val.upvote + '</span></a>';
					html += '</li>';
				}
			});
			if(html == '') {
				html += '<li>might be a random selection... sweet!</li>';
			}
			$ul.html(html);
			try {
				$ul.listview('refresh');
				$ul.trigger('updatelayout');
			} catch(e) {}
		});
	} catch(e) {}

	try{ clearTimeout(updateCurrentTimeout); } catch(e) {}
	updateCurrentTimeout = setTimeout(updateCurrent, 5000);
}
$(document).on('pagecreate', '#current', function() {
	updateCurrent();
});




$(document).on('pagecreate', '#search', function() {
	console.log('#search');

	$('#autocomplete-song_artist').on('filterablebeforefilter', function ( e, data ) {
		var $ul = $(this),
			$input = $(data.input),
			value = $input.val(),
			html = '';
		$ul.html('');
		if(value && value.length >= 1) {
			$ul.html('<li><div class="ui-loader"><span class="ui-icon ui-icon-loading"></span></div></li>');
			$ul.listview('refresh');
			$.ajax({
				url: 'json_search.php',
				dataType: 'json',
				data: {
					term: $input.val()
				}
			})
			.then( function(response) {
				$.each(response.result, function(i, val) {
					html += '<li data-rel="' + val.trackid + '">' + val.label + '</li>';
				});
				$ul.html(html);
				$ul.listview('refresh');
				$ul.trigger('updatelayout');
			});
		}
	});


	$(document).on('vclick', '#autocomplete-song_artist li', function() {
		// console.log($(this).attr('data-rel'));
		$.get('json_detail.php', {trackid:$(this).attr('data-rel')}, function(response) {
			try {
				$('#dTrackID').val(response.result.TrackID);
				$('#dName').html(response.result.Name);
				$('#dArtist').html(response.result.Artist);
				$('#dAlbum span').html(response.result.Album);
				if(response.result.Composer != null) {
					$('#dComposer span').html(response.result.Composer).parent().show();
				} else {
					$('#dComposer').hide();
				}
				if(response.result.Year != null) {
					$('#dYear span').html(response.result.Year).parent().show();
				} else {
					$('#dYear').hide();
				}
				$('#dDuration span').html(response.result.duration.toHHMMSS());

				if(response.result.hasCover == 1) {
					$('#dCover').html('<img src="covers/' + response.result.TrackID + '_100x100.jpg" alt=""/>');
				} else {
					$('#dCover').html('<img src="img/keep-calm-and-party-on-garth_100x100.png" alt=""/>');
				}

			} catch(e) {}
			$('#popup-song-detail').popup('open');
		});

		return false;
	});


	$('#requestSongButton').on('submit', function() {
		$.post(
			'json_request.php',
			{trackid: $('#dTrackID').val()},
			function(response) {
				console.log(response);
				$('#popup-song-detail').popup('close');
				if(response.error != '') {
					$().dpToast(response.error);
					// alert(response.error)
				} else {
					$().dpToast(response.success);
					// alert(response.success);
				}
			}
		);

		return false;
	});

});


$(document).on('pagecreate', '#profile', function() {
	console.log('#profile');

	// pull nickname
	$.get('json_profile.php', {mode:'getNick'}, function(response) {
		$('#nickname').val(response.success);
	});

	// save nickname
	$('#profileForm').on('submit', function() {
		$.post(
			'json_profile.php',
			{nickname: $('#nickname').val()},
			function(response) {
				console.log(response);
				if(response.error != '') {
					// alert(response.error)
					$().dpToast(response.error);
				} else {
					// alert(response.success);
					$().dpToast(response.success);
				}
			}
		);

		return false;
	});
});


