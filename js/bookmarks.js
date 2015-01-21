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
				
				display.innerHTML = "";
				
				// render each bookmark
				// todo display the relevance of each bookmark
				resp.bookmarks.forEach(function(bookmark) {
					display.appendChild(blow_worm.actions.bookmarks.render(bookmark));
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
		html.classList.add("bookmark", "flex", "across");
		html.dataset.id = bookmark.id;
		html.innerHTML = "<input type='checkbox' class='bookmark-favicon' />" + 
		"<div class='bookmark-details flex down flex-1'>" + 
		"	<div class='top-row flex across'>" + 
		"			<span class='bookmark-name flex-3'></span>" + 
		"			<span class='bookmark-date'></span>" + 
		"			<img src='images/icons/cog.png' class='bookmark-update tiny-image clickable' />" + 
		"		</div>" + 
		"		<div class='bottom-row flex across'>" + 
		"			<a target='_blank' class='bookmark-url flex-2'></a>" + 
		"			<span class='bookmark-tags flex-2'></span>" + 
		"		</div>" + 
		"	</div>";
		
		// insert the name / url / favicon url
		html.querySelector(".bookmark-name").innerText = bookmark.name;
		html.querySelector(".bookmark-url").href = bookmark.url;
		html.querySelector(".bookmark-url").innerText = bookmark.url;
		html.querySelector(".bookmark-favicon").dataset.faviconurl = bookmark.faviconurl;
		html.querySelector(".bookmark-favicon").style.backgroundImage = "url(" + bookmark.faviconurl + ")";
		
		// attach the event handler to the update button
		html.querySelector(".bookmark-update").addEventListener("click", function(event) {
			var bookmark_html = event.target.parentElement.parentElement.parentElement;
			console.log(bookmark_html);
			blow_worm.modals.update(bookmark_html);
		})
		
		// todo format and add the date
		
		// insert the tags
		var html_tags = html.querySelector(".bookmark-tags");
		bookmark.tags.forEach(function(tag) {
			var new_tag_html = document.createElement("span");
			new_tag_html.classList.add("tag");
			new_tag_html.appendChild(document.createTextNode(tag));
			html_tags.appendChild(new_tag_html);
			html_tags.appendChild(document.createTextNode(" "));
		});
		
		return html;
	},
};