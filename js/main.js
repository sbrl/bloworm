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
				if(blow_worm.env.loggedin)
				{
					console.info("[setup] Logged in with session key ", blow_worm.env.sessionkey);
					console.info("[setup] Starting setup...");
					
					document.title = "Blow Worm";
					
					// display the information that we have now
					document.getElementById("display-login-status").innerHTML = "You are loggged in as " + blow_worm.env.username + ".";
					
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
									.then(blow_worm.actions.setup); //setup the interface
							}
						}]
					}).show();
				}
			}, function(response) {
				console.error("Error when checking login status:", response);
				nanoModal("Something went wrong when checking your current login status!<br />\nCHeck the console for more details.", { autoRemove: true, buttons: [] }).show();
			});
		}
	}
};

//event listeners
window.addEventListener("load", blow_worm.events.load);