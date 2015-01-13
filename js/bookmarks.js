blow_worm.actions.bookmarks = {
	// function to update the list of bookmarks shown to the user
	update: function() {
		return new Promise(function(resolve, reject) {
			console.info("[setup/update] fetching list of bookmarks...");
			var url = "api.php?action=search",
				query = document.getElementById("search-box").value;
			
			if(query.trim().length > 0)
			{
				url += "&query=" + encodeURIComponent(query.trim());
			}
			
			get(url).then(function(response) {
				console.info("[setup/update] done");
				console.info("[setup/update] rendering bookmarks to console....");
				var resp = JSON.parse(response),
					display = document.getElementById("bookmarks");
				
				// render each bookmark
				// todo display the relevance of each bookmark
				resp.bookmarks.forEach(function(bookmark) {
					display.appendChild(blow_worm.actions.bookmarks.render());
				});
				console.info("[setup/update] done");
				
				// update the count of the total number of bookmarks that the user has, but only if the query was empty since we want to count *all* of them
				if(query.trim().length === 0)
					document.getElementById("display-bookmark-count").innerText = resp.bookmarks.length;
				
				resolve();
			}, function(response) {
				console.error("Error fetching bookmark list during setup:", response);
				nanoModal("Something went wrong when loading your bookmarks!<br />\nCheck the console for more information.", { autoRemove: true, buttons: [] }).show();
				reject(response);
			});
		});
	},
	
	// function to convert a bookmark object to the appropriate html
	render: function(bookmark) {
		var html = document.createElement("div");
		html.dataset.id = bookmark.id;
		html.innerHTML = "<div class='bookmark' data-id='...'>" +
		"<input type='checkbox' class='bookmark-favicon' />" + 
		"<div class='bookmark-details flex down'>" + 
		"	<div class='top-row'>" + 
		"			<span class='bookmark-name'></span>" + 
		"			<span class='bookmark-date'></span>" + 
		"		</div>" + 
		"		<div class='bottom-row'>" + 
		"			<span class='bookmark-url'></span>" + 
		"			<span class='bookmark-tags'></span>" + 
		"		</div>" + 
		"	</div>" + 
		"</div>";
		
		// insert the name / url / favicon url
		html.querySelector(".bookmark-name").innerText = bookmark.name;
		html.querySelector(".bookmark-url").innerText = bookmark.url;
		html.querySelector(".bookmark-favicon").dataset.faviconurl = bookmark.faviconurl;
		
		// todo format and add the date
		
		// insert the tags
		var html_tags = html.querySelector(".bookmark-tags");
		bookmark.tags.forEach(function(tag) {
			var new_tag_html = document.createElement("span");
			new_tag_html.classList.add("tag");
			new_tag_html.appendChild(document.createTextNode(tag));
			html_tags.appendChild(new_tag_html);
		});
		
		return html;
	},
};