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
		session_token: "",
		
		// whether we are logged in or not
		loggedin: false,
	},
	actions: {
		login: function(username, password) {
			//show a progress box
			var login_progress_modal = nanoModal(document.getElementById("modal-login-progress"), {
					overlayClose: false,
					buttons: []
				}).show(),
				login_display = document.getElementById("display-login-progress");
			
			login_display.innerHTML += "Acquiring session token...<br />\n";
			
			//send the login request
			var ajax = new XMLHttpRequest(),
				data = {
					user: username,
					pass: password
				};
			
			ajax.onload = function() {
				if(ajax.status >= 200 && ajax.status < 300)
				{
					//the request was successful
					login_display.innerHTML += "Response recieved: login successful, cookie set.<br />\n";
					
					//todo setup the interface
				}
				else
				{
					login_display.innerHTML += "Login failed! See the console for more details.<br />\n";
					console.error(ajax);
				}
			};
			
			ajax.open("POST", "api.php?action=login");
			ajax.setRequestHeader("content-type", "application/x-www-form-urlencoded");
			ajax.send(postify(data));
			login_display.innerHTML += "Login request sent to server.<br />\n";
		}
	},
	events: {
		load: function(event) {
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
						blow_worm.actions.login(user, pass);
					}
				}]
			}).show();
		}
	}
};

//event listeners
window.addEventListener("load", blow_worm.events.load);