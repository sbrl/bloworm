/// Micro Snippets ///
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
		// whether we are logged in or not
		loggedin: false,
	},
	events: {
		load: function(event) {
			var loginmodal = nanoModal(document.getElementById("modal-login"), {
				overlayClose: false,
				buttons: [{
					text: "Login",
					hander: function() {
						var user = document.getElementById("login-user").value,
							pass = document.getElementById("login-pass").value;
						
						if(user.length === 0 || pass.length === 0)
						{
							nanoModal("The username and / or password box(es) were empty.", { autoRemove: true}).show().onHide(loginmodal.show);
						}
					}
				}]
			});
		}
	}
};

//event listeners
window.addEventListener("load", blow_worm.events.load);