blow_worm.actions.bookmarks = {
	// function to update the list of bookmarks shown to the user
	update: function() {
		return new Promise(function(resolve, reject) {
			var url = "api.php?action=search",
				query = document.getElementById("search-box").value;
			
			if(query.trim().length > 0)
			{
				url += "&query=" + encodeURIComponent(query.trim());
			}
			
			get(url).then(function(response) {
				var resp = JSON.parse(response);
				
				// todo display the list of the user's bookmarks
				
				
				resolve();
			}, function(response) {
				console.error("Error fetching bookmark list during setup:", response);
				nanoModal("Something went wrong when loading your bookmarks!<br />\nCheck the console for more information.", { autoRemove: true, buttons: [] }).show();
				reject(response);
			});
		});
	},
	
	//function to convert a bookmark object to the appropriate html
	render: function(bookmark) {
		/*
		 * <div class='bookmark' data-id='...'>
		 *	<input type='checkbox' class='bookmark-favicon' />
		 *	<div class='bookmark-details flex down'>
		 *		<div class='top-row'>
		 * 			<span class='bookmark-name'></span>
		 * 			<span class='bookmark-date'></span>
		 * 		</div>
		 * 		<div class='bottom-row'>
		 * 			<span class='bookmark-url'></span>
		 * 			<span class='bookmark-tags'>
		 * 				<span class='tag'></span>
		 * 				<span class='tag'></span>
		 * 				<span class='tag'></span>
		 * 			</span>
		 * 		</div>
		 * 	</div>
		 * </div>
		 */
		var html = document.createElement("div");
		html.classList.add("bookmark", "flex", "across");
		html.dataset.id = bookmark.id;
		
		html.appendChild(document.createElement("input"))
		html.querySelector("input").type = "checkbox";
		html.querySelector("input").dataset["favicon-url"] = bookmark.faviconurl;
		html.querySelector("input").classList.add("bookmark-favicon");
		
		html.appendChild(document.createElement("div"));
		html.querySelector("div").classList.add("bookmark-details", "flex", "down");
		html.querySelector(".bookmark-details").appendChild(document.createElement("div"))
		html.querySelector(".bookmark-details div").classList.add("top-row");
		html.querySelector(".bookmark-details").appendChild(document.createElement("div"))
		html.querySelectorAll(".bookmark-details div:nth-child(2)").classList.add("bottom-row");
		
		html.querySelector(".top-row").appendChild(document.createElement("span"));
		html.querySelector(".top-row").appendChild(document.createElement("span"));
		html.querySelector(".top-row span:nth-child(1)").classList.add("bookmark-name");
		html.querySelector(".top-row span:nth-child(2)").classList.add("bookmark-date");
		
		html.querySelector(".bottom-row").appendChild(document.createElement("span"));
		html.querySelector(".bottom-row").appendChild(document.createElement("span"));
		html.querySelector(".bottom-row span:nth-child(1)").classList.add("bookmark-url");
		html.querySelector(".bottom-row span:nth-child(2)").classList.add("bookmark-tags");
		
		html.querySelector(".bookmark-name").appendChild(document.createTextNode(bookmark.name));
		html.querySelector(".bookmark-url").appendChild(document.createTextNode(bookmark.url));
		
		var html_tags = html.querySelector(".bookmark-tags");
		bookmark.tags.forEach(function(tag) {
			var new_tag_html = document.createElement("span");
			new_tag_html.classList.add("tag");
			new_tag_html.appendChild(document.createTextNode(tag));
			html_tags.appendChild(new_tag_html);
		});
		
		//todo format and add the date
		
		return html;
	},
};