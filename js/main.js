/// Micro Snippets ///
// #2 - Postify
function postify(a){return Object.keys(a).map(function(k){return [k,encodeURIComponent(a[k])].join("=")}).join("&")}
// #8 - Promisified GET
function get(u){return new Promise(function(r,t,a){a=new XMLHttpRequest();a.onload=function(b,c){b=a.status;c=a.response;if(b>199&&b<300){r(c)}else{t(c)}};a.open("GET",u,true);a.send(null)})}
// #9 - Promisified POST
function post(u,d,h){return new Promise(function(r,t,a){a=new XMLHttpRequest();a.onload=function(b,c){b=a.status;c=a.response;if(b>199&&b<300){r(c)}else{t(c)}};a.open("POST",u,true);a.setRequestHeader("content-type",h?h:"application/x-www-form-urlencoded");a.send(d)})}

blow_worm = {
	env: {
		// the mode we should operate in
		// can either be "login", or "view-share".
		// currently not used.
		mode: "login",
		
		// the name of the user who is currently logged in
		username: "",
		
		// our current session token
		sessionkey: "",
		
		// whether we are logged in or not
		loggedin: false,
		
		// The id of the setTimeOut used to schedule the updating of the search results.
		// We do this so that we don't update the search box on every character
		// that the user types, rather we update when the user stops typing.
		next_update: -1
		
	},
	actions: {
		///////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////// Login ////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////
		login: function(username, password) {
			return new Promise(function(resolve, reject) {
				// show a progress box
				var login_progress_modal = nanoModal(document.getElementById("modal-login-progress"), {
						overlayClose: false,
						buttons: []
					}).show(),
					login_display = document.getElementById("display-login-progress");
				
				login_display.innerHTML += "Acquiring session token...<br />\n";
				
				// send the login request
				var data = {
						user: username,
						pass: password
					};
				
				post("api.php?action=login", postify(data)).then(function(response) {
					// the request was successful
					login_display.innerHTML += "Response recieved: login successful, cookie set.<br />\n";
					
					// read the response and set the environment variables
					var respjson = JSON.parse(response);
					blow_worm.env.loggedin = true;
					blow_worm.env.username = respjson.user;
					blow_worm.env.sessionkey = respjson.sessionkey;
					
					// hide and reset the login progress box
					login_progress_modal.hide();
					login_display.innerHTML = "";
					
					resolve(respjson);
				}, function(response) {
					login_display.innerHTML += "Login failed! See the console for more details.<br />\n";
					console.error(response);
					reject(response);
				});
				login_display.innerHTML += "Login request sent to server.<br />\n";
			});
		},
		
		///////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////// setup ////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////
		// function to setup the interface after login
		setup: function() {
			return new Promise(function(resolve, reject) {
				console.info("[setup] Adding handlers to icons...");
				/// add the handlers to the icons
				// logout
				document.getElementById("button-logout").addEventListener("click", function(event) {
					blow_worm.actions.logout()
						.then(function() {
							window.location.reload();
						});
				});
				// remove bookmarks
				document.getElementById("button-remove-bookmarks").addEventListener("click", blow_worm.modals.delete);
				// add bookmark
				document.getElementById("button-add-bookmark").addEventListener("click", blow_worm.modals.create);
				// settings
				document.getElementById("button-settings").addEventListener("click", blow_worm.modals.settings);
				// update the search box as the user types
				document.getElementById("search-box").addEventListener("keyup", blow_worm.events.searchbox.keyup);
				
				if(blow_worm.env.loggedin)
				{
					console.info("[setup] Logged in with session key ", blow_worm.env.sessionkey);
					console.info("[setup] Starting setup...");
					
					document.title = "Bloworm";
					
					// display the information that we have now
					document.getElementById("display-login-name").innerHTML = blow_worm.env.username;
					
					// update the list of bookmarks
					
					blow_worm.actions.bookmarks.update();
					
					// todo fetch tag information from the server
					
				}
				else
				{
					// the user is not logged in for some reason, give them the login modal so they can do so
					nanoModal("You are not logged in. Tag sharing has not been implemented yet, so you will now be shown the login dialog box.", {
						autoRemove: true,
						overlayClose: false,
					}).show().onHide(blow_worm.actions.login);
				}
			});
		},
		
		//////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////// Logout ///////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////
		logout: function() {
			return new Promise(function(resolve, reject) {
				get("api.php?action=logout").then(resolve, function(response) {
					try {
						JSON.parse(response);
						blow_worm.actions.display_error(response)
							.then(location.reload);
					} catch (error) {
						nanoModal("Something went wrong while trying to log you out!<br />Please check the console for more information.", {
							autoRemove: true,
							overlayClose: false,
							buttons: []
						}).show();
						console.error(response);
						reject(response);
					}
				});
			});
		},
		
		display_error: function(response) {
			return new Promise(function(resolve, reject) {
				var error = JSON.parse(response),
					html = "<h2>Something went wrong!</h2>";
				html += "<p>Error Code: <strong>" + error.code + "</strong></p>";
				html += "<p>" + error.type + "</p>";
				html += "<p><strong>Techincal Details:</strong></p>";
				html += "<p><small><em>(Make sure to include all these details if you report this issue)</em></small></p>";
				html += "<pre>" + response + "</pre>";
				
				nanoModal(html, {
					autoRemove: true,
					buttons: [{
						text: "Continue",
						primary: true,
						handler: resolve
					}]
				});
			});
		}
	},
	
	modals: {
		login: function() {
			return new Promise(function(resolve, reject) {
				var loginmodal = nanoModal(document.getElementById("modal-login"), {
					overlayClose: false,
					buttons: [{
						text: "Login",
						primary: true,
						handler: function() {
							loginmodal.hide(); // hide the dialog box
							
							// grab the details the user entered
							var userbox = document.getElementById("login-user"),
								passbox = document.getElementById("login-pass"),
								user = userbox.value,
								pass = passbox.value;
							
							// reset the input boxes
							userbox.value = "";
							passbox.value = "";
							
							// make sure that that the user actually entered both the username and password
							if(user.length === 0 || pass.length === 0)
							{
								nanoModal("The username and / or password box(es) were empty.", { autoRemove: true}).show().onHide(loginmodal.show);
							}
							
							// call the login handler
							blow_worm.actions.login(user, pass)
								.then(resolve); //setup the interface
						}
					}]
				}).show();
			});
		},
		
		create: function() {
			return new Promise(function(resolve, reject) {
				nanoModal(document.getElementById("modal-create"), { buttons: [{
					text: "Create",
					primary: true,
					handler: function(modal) {
						console.log("[create] adding bookmark...");
						
						// create a new modal dialog to tell the user that we are adding the bookmark
						// grab references to the input boxes
						var namebox = document.getElementById("create-name"),
							urlbox = document.getElementById("create-url"),
							tagsbox = document.getElementById("create-tags"),
							
							requrl = "api.php?action=create";
						
						if(urlbox.value.length == 0)
						{
							resolve();
							return;
						}
						
						// disable the button that the user clicked on
						// we don't want them clicking it more than once :)
						// https://github.com/kylepaulsen/NanoModal/issues/1
						modal.event.target.setAttribute("disabled", "disabled");
						
						var progress_modal = nanoModal("Adding Bookmark...", { overlayClose: false, autoRemove: true, buttons: [] }).show();
						
						//add 'http://' if the user forgot to do that
						if(!urlbox.value.match(/^[a-z]+\:(?:\/\/)?/i))
							urlbox.value = "http://" + urlbox.value;
						
						
						// build the url
						requrl += "&url=" + encodeURIComponent(urlbox.value);
						if(namebox.value.length > 0)
							requrl += "&name=" + encodeURIComponent(namebox.value);
						if(tagsbox.value.length > 0)
							requrl += "&tags=" + encodeURIComponent(tagsbox.value);
						
						// hide and reset the input modal
						namebox.value  = "";
						urlbox.value = "";
						tagsbox.value = "";
						
						get(requrl).then(function(response) {
							var respjson = JSON.parse(response);
							console.log("[create] Response recieved", respjson);
							
							// render and insert the new bookmark into the display
							var newhtml = blow_worm.actions.bookmarks.render(respjson.newbookmark),
								bookmarks_display = document.getElementById("bookmarks");
							
							bookmarks_display.insertBefore(newhtml, bookmarks_display.firstChild);
							
							// update the count of the number of bookmarks that the user has
							var display_bookmark_count = document.getElementById("display-bookmark-count");
							display_bookmark_count.innerHTML = parseInt(display_bookmark_count.innerHTML) + 1;
							
							// hide and reset the modal dialogs
							modal.event.target.removeAttribute("disabled");
							modal.hide();
							progress_modal.hide();
							
							// resolve the promise
							resolve(respjson);
						}, function(response) {
							console.error("[create] Error occurred. ", response);
							try {
								var resp = JSON.parse(response);
								
								blow_worm.actions.display_error(response)
									.then(function() { reject(resp); });
							} catch (error) {
								nanoModal("Something went wrong!<br />Please check the console for more information.", { autoRemove: true }).show();
								reject(response);
							}
						});
						console.log("[create] request sent");
					}
				}] }).show();
			});
		},
		
		delete: function() {
			return new Promise(function(resolve, reject) {
				nanoModal("<h2>Delete Bookmarks</h2><p>Are you sure you want to delete these bookmarks?</p><p>It can't be undone!</p>", {
					autoRemove: true,
					buttons: [
						{
							text: "Yes",
							primary: true,
							handler: function(modal) {
								modal.hide();
								
								var bookmarks = document.querySelectorAll(".bookmark"),
									to_delete = [];
								
								for(var i = 0; i < bookmarks.length; i++)
								{
									if(bookmarks[i].querySelector("input[type=checkbox]").checked)
									{
										to_delete.push(bookmarks[i].dataset.id);
										bookmarks[i].parentElement.removeChild(bookmarks[i]);
									}
								}
								
								if(to_delete.length == 0)
								{
									resolve();
									return;
								}
								
								modal.hide();
								var progress_modal = nanoModal("Deleting...", { autoRemove: true, overlayClose: false, buttons: [] }).show();
								
								get("api.php?action=delete&ids=" + to_delete.join(",")).then(function(response) {
									// the server deleted the bookmarks successfully
									progress_modal.hide();
									resolve(response);
								}, function(response) {
									try {
										JSON.parse(response);
										blow_worm.actions.display_error(response)
											.then(window.location.reload);
									} catch(error) {
										console.error(response);
										nanoModal("<p>Something went wrong!</p><p>Check the console for more information.</p><p>Press 'continue' to reload the page.</p>", {
											autoRemove: true,
											buttons: [{
												text: "Continue",
												primary: true,
												handler: window.location.reload
											}]
										}).show();
									}
								});
							}
						},
						{
							text: "No",
							handler: "hide"
						}
					]
				}).show();
			});
		},
		
		update: function(bookmark_html) {
			return new Promise(function(resolve, reject) {
				var update_html = document.getElementById("modal-update"),
					// grab the bookmark's data
					id = bookmark_html.dataset.id,
					name =  bookmark_html.querySelector(".bookmark-name").innerHTML,
					bookmark_url = bookmark_html.querySelector(".bookmark-url").href,
					
					tags_html_list = bookmark_html.querySelectorAll(".tag"),
					tags = "";
				
				// todo add the favicon url as an advanced option
				// note we could display a preview of the favicon they have chosen with an <img /> tag
				
				// obtain the tags
				[].forEach.call(tags_html_list, function(tag) {
					tags += tag.innerHTML.trim() + ", ";
				});
				tags = tags.replace(/^\s+|,\s+$/g, ""); // remove any whitespace and the last comma
				
				// update the interface
				var namebox = document.getElementById("update-name"),
					urlbox = document.getElementById("update-url"),
					tagsbox = document.getElementById("update-tags");
				
				namebox.value = name;
				urlbox.value = bookmark_url;
				tagsbox.value = tags;
				
				// show the user a modal they can use to edit the bookmark
				nanoModal(update_html, {
					buttons: [
						{
							text: "Update",
							primary: true,
							handler: function(modal) {
								modal.hide();
								var progress_modal = nanoModal("Updating bookmark...", { autoRemove: true }).show(),
									url = "api.php?action=update";
								
								url += "&id=" + id;
								
								// todo don't do anything if they are all the same
								
								if(name != namebox.value)
									url += "&name=" + encodeURIComponent(namebox.value);
								if(bookmark_url != urlbox.value)
									url += "&url=" + encodeURIComponent(urlbox.value);
								if(tags != tagsbox.value)
									url += "&tags=" + encodeURIComponent(tagsbox.value);
								
								get(url).then(function(response) {
									// success!
									bookmark_html.querySelector(".bookmark-name").innerHTML = namebox.value;
									bookmark_html.querySelector(".bookmark-url").innerHTML = urlbox.value;
									bookmark_html.querySelector(".bookmark-url").href = urlbox.value;
									
									var tags_html_str = "",
										tags_split = tagsbox.value.split(/, ?/g);
									
									for(var i = tags_split.length - 1; i >= 0; i--)
									{
										tags_html_str = "<span class='tag'>" + tags_split[i] + "</span>" + tags_html_str;
									}
									bookmark_html.querySelector(".bookmark-tags").innerHTML = tags_html_str;
									
									// hide the progress modal
									progress_modal.hide();
									
									resolve(response);
								}, function(response) {
									try {
										JSON.parse(response);
										blow_worm.actions.display_error(response);
									} catch (error) {
										console.error(response);
										nanoModal("<p>Something went wrong!</p><p>Check the console for more information.</p>", {
											autoRemove: true,
											buttons: [{
												text: "Continue",
												primary: true,
												handler: "hide"
											}]
										}).show();
									}
									reject(response);
								});
							}
						},
						{
							text: "Cancel",
							handler: "hide"
						}
					]
				}).show();
			});
		},
		
		settings: function() {
			return new Promise(function(resolve, reject) {
				nanoModal(document.getElementById("modal-settings"), {
					buttons: [{
						text: "Save",
						primary: true,
						handler: function(modal) {
							var boxes = {
								oldpass: document.getElementById("settings-passchange-oldpass"),
								newpass: document.getElementById("settings-passchange-newpass"),
								newpassconf: document.getElementById("settings-passchange-newpassconf")
							};
							if(boxes.oldpass.value.length > 0 &&
							   boxes.newpass.value.length > 0 &&
							   boxes.newpassconf.value.length > 0)
							{
								if(boxes.newpass.value !== boxes.newpassconf.value)
								{
									nanoModal("The passwords didn't match.", { autoRemove: true, buttons: [{ text: "Go Back", primary: true, handler: "hide" }] }).show();
									return;
								}
								//the user want's to change their password
								var data = {
									key: "password",
									value: boxes.newpass.value,
									oldpass: boxes.newpassconf.value
								};
								
								post("api.php?action=usermod", postify(data)).then(function(response) {
									// success!
									console.log("[usermod] Usermod successful. Reloading page...");
								}, function(response) {
									try {
										JSON.parse(response);
										blow_worm.actions.display_error(response);
									} catch (error) {
										console.error(response);
										nanoModal("<p>Something went wrong!</p><p>Check the console for more information.</p>", {
											autoRemove: true,
											buttons: [{
												text: "Continue",
												primary: true,
												handler: "hide"
											}]
										}).show();
									}
								}).then(blow_worm.actions.logout)
								.then(window.location.reload);
							}
						}
					}, {
						text: "Cancel",
						handler: "hide"
					}]
				}).show();
			});
		}
	},
	
	events: {
		load: function(event) {
			// check whether the user is logged in
			console.log("Checking login status...");
			get("api.php?action=checklogin").then(function(response) {
				console.log(response);
				var resp = JSON.parse(response);
				if(resp.logged_in)
				{
					// We are already logged in! Setup the environment and then commence setup immediately.
					blow_worm.env.loggedin = true;
					blow_worm.env.username = resp.user;
					blow_worm.env.sessionkey = resp.sessionkey;
					
					console.info("Already logged in. Session key: " + blow_worm.env.sessionkey);
					
					blow_worm.actions.setup();
				}
				else
				{
					// We are not logged in, give the user a modal so they can do so
					blow_worm.modals.login()
						.then(blow_worm.actions.setup);
				}
			}, function(response) {
				console.error("Error when checking login status:", response);
				nanoModal("Something went wrong when checking your current login status!<br />\nCheck the console for more details.", { autoRemove: true }).show();
			});
		},
		searchbox: {
			keyup: function(event) {
				// clear the previous update that was scheduled
				clearInterval(blow_worm.env.next_update);
				// schedule a new one to reset the timeout
				blow_worm.env.next_update = setTimeout(blow_worm.actions.bookmarks.update, 300);
			}
		}
	}
};

//event listeners
window.addEventListener("load", blow_worm.events.load);