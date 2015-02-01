(function(user, key, ajax, url) {
	url += 'key=' + key +
		'&user=' + user +
		'&name=' + encodeURIComponent(prompt('Bookmark name:', document.title)) +
		'&url=' + encodeURIComponent(location.href) +
		'&tags=' + encodeURIComponent(prompt('Bookmark Tags:'));
	ajax.onload = function() {
		if(ajax.status >= 200 && ajax.status < 300)
			alert('Bookmark saved successfully');
		else {
			console.error(ajax.response);
			alert('Something went wrong, check the console');
		}
	}
	ajax.open('GET', url, true);
	ajax.send(null);
})('{user}', '{key}', new XMLHttpRequest(), '{root}/api.public.php?');