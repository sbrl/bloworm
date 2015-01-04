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
				}),
				login_display = document.getElementById("display-login-progress");
			
			login_display.value += "Acquiring session token...\n";
			
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
					login_display.value += "Response recieved: login successful, cookie set.\n";
					
					//todo setup the interface
				}
				else
				{
					login_display.value += "Login failed! See the console for more details.";
					console.error(ajax);
				}
			};
			
			ajax.open("POST", "api.php?action=login");
			ajax.send(postify(data));
			login_display.value += "Login request sent to server.\n";
		}
	},
	events: {
		load: function(event) {
			var loginmodal = nanoModal(document.getElementById("modal-login"), {
				overlayClose: false,
				buttons: [{
					text: "Login",
					primary: true,
					hander: function() {
						var user = document.getElementById("login-user").value,
							pass = document.getElementById("login-pass").value;
						
						if(user.length === 0 || pass.length === 0)
						{
							nanoModal("The username and / or password box(es) were empty.", { autoRemove: true}).show().onHide(loginmodal.show);
						}
					}
				}]
			}).show();
		}
	}
};

//event listeners
window.addEventListener("load", blow_worm.events.load);