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
				var ajax = new XMLHttpRequest(),
					data = {
						user: username,
						pass: password
					};
				
				ajax.onload = function() {
					if(ajax.status >= 200 && ajax.status < 300)
					{
						// the request was successful
						login_display.innerHTML += "Response recieved: login successful, cookie set.<br />\n";
						// read the response and set the environment variables
						var response = JSON.parse(ajax.response);
						blow_worm.env.loggedin = true;
						blow_worm.env.username = response.user;
						blow_worm.env.sessionkey = response.sessionkey;
						
						
						//hide and reset the login progress box
						login_progress_modal.hide();
						login_display.innerHTML = "";
						
						resolve(response);
					}
					else
					{
						login_display.innerHTML += "Login failed! See the console for more details.<br />\n";
						console.error(ajax);
						reject(response);
					}
				};
				
				ajax.open("POST", "api.php?action=login");
				ajax.setRequestHeader("content-type", "application/x-www-form-urlencoded");
				ajax.send(postify(data));
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
				// update the search box as the user types
				document.getElementById("search-box").addEventListener("keyup", blow_worm.events.searchbox.keyup);
				
				if(blow_worm.env.loggedin)
				{
					console.info("[setup] Logged in with session key ", blow_worm.env.sessionkey);
					console.info("[setup] Starting setup...");
					
					document.title = "Blow Worm";
					
					// display the information that we have now
					document.getElementById("display-login-status").innerHTML = "You are logged in as " + blow_worm.env.username + ".";
					
					// update the list of bookmarks
					
					blow_worm.actions.bookmarks.update();
					
					// todo fetch some additional information from the server
					
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
				var ajax = new XMLHttpRequest();
				ajax.onload = function() {
					if(ajax.status >= 200 && ajax.status < 300)
					{
						resolve(ajax.response);
					}
					else
					{
						nanoModal("Something went wrong while trying to log you out!<br />Please check the console for more information.", {
							autoRemove: true,
							overlayClose: false,
							buttons: []
						}).show();
						console.error(ajax.response);
						reject(ajax.response);
					}
				};
				ajax.open("GET", "api.php?action=logout", true);
				ajax.send(null);
			})
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
						
						// disable the button that the user clicked on
						// we don't want the mclicking it more than once :)
						// https://github.com/kylepaulsen/NanoModal/issues/1
						modal.event.target.setAttribute("disabled", "disabled");
						
						
						// create a new modal dialog to tell the user that we are adding the bookmark
						var progress_modal = nanoModal("Adding Bookmark...", { overlayClose: false, autoRemove: true, buttons: [] }).show(),
							
							// grab references to the input boxes
							namebox = document.getElementById("create-name"),
							urlbox = document.getElementById("create-url"),
							tagsbox = document.getElementById("create-tags"),
							
							requrl = "api.php?action=create";
						
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
						
						var ajax = new XMLHttpRequest();
						ajax.onload = function() {
							console.log("[create] response recieved", ajax.response);
							var respjson = JSON.parse(ajax.response);
							
							if(ajax.status == 201)
							{
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
								resolve(JSON.parse(ajax.response));
							}
							else
							{
								nanoModal("Something went wrong!<br />Please check the console for more information.", { autoRemove: true }).show();
								console.error(ajax.response, JSON.parse(ajax.response));
								reject(JSON.parse(ajax.response));
							}
						};
						ajax.open("GET", requrl, true);
						ajax.send(null);
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
								}
								
								modal.hide();
								var progress_modal = nanoModal("Deleting...", { autoRemove: true, overlayClose: false, buttons: [] }).show(),
									ajax = new XMLHttpRequest();
								
								ajax.onload = function() {
									if(ajax.status >= 200 && ajax.status < 300)
									{
										// the server deleted the bookmarks successfully
										progress_modal.hide();
										
										resolve();
									}
									else
									{
										console.error(ajax.response);
										nanoModal("<p>Something went wrong!</p><p>Check the console for more information.</p><p>Press 'continue' to reload the page.</p>", {
											autoRemove: true,
											overlayClose: true,
											buttons: [{
												text: "Continue",
												primary: true,
												handler: window.location.reload
											}]
										}).show();
									}
								}
								ajax.open("GET", "api.php?action=delete&ids=" + to_delete.join(","), true);
								ajax.send(null);
							}
						},
						{
							text: "No",
							handler: "hide"
						}
					]
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