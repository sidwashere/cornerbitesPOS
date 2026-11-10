/**
 * wpos.js is part of Wallace Point of Sale system (WPOS)
 *
 * wpos.js Provides core functions for the admin dashboard.
 *
 * Cornerbites POS is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License, or (at your option) any later version.
 *
 * Cornerbites POS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details:
 * <https://www.gnu.org/licenses/lgpl.html>
 *
 * @package    wpos
 * @copyright  Copyright (c) 2014 WallaceIT. (https://wallaceit.com.au)
 * @author     Michael B Wallace <micwallace@gmx.com>
 * @since      Class created 15/1/13 12:01 PM
 */

function changehash(hash){
    document.location.hash = hash;
}

function setActiveMenuItem(secname){
    // remove active from previous
    $(".nav-list li").removeClass('active');
    $(".submenu li").removeClass('active');
    // add active to clicked
    var li = $('a[href="#!'+secname+'"]').parent('li');
    $(li).addClass('active');
    // set the parent item if its a submenu
    if ($(li).parent('ul').hasClass('submenu')){
        $(li).parent('ul').parent('li').addClass('active');
    }
}
var WPOS;
//On load page, init the timer which check if the there are anchor changes
$(function(){
    // initiate WPOS object
    WPOS = new WPOSAdmin();
    // init
    WPOS.isLogged();
    // Admin login must always require explicit username/password.
    // Pos-side auto-login is handled from the root POS app only.
});
function WPOSAdmin(){
    // AJAX PAGE LOADER FUNCTIONS
    var currentAnchor = '0';
    var currentsec = '';
    var lastAnchor = null;
    // Are there anchor changes, if there are, calculate request and send
    this.checkAnchor = function(){
        //Check if it has changes
        if((currentAnchor != document.location.hash)){
            lastAnchor = currentAnchor;
            currentAnchor = document.location.hash;
            if(currentAnchor){
                var splits = currentAnchor.substring(2).split('&');
                //Get the section
                sec = splits[0];
                // has the section changed
                if (sec==currentsec &&  currentAnchor.indexOf('&query')!=-1){
                    // load some subcontent
                } else {
                    // if we are leaving realtime dash, close socket connection
                    if (currentsec == "realtime"){
                        WPOS.stopSocket();
                    }
                    // set new current section
                    currentsec=sec;
                    // set menu items active
                    setActiveMenuItem(sec);
                    // close mobile menu
                    if ($("#menu-toggler").is(":visible")){
                        $("#sidebar").removeClass("display");
                    }
                    // start the loader
                    WPOS.util.showLoader();
                    //Creates the  string callback. This converts the url URL/#! &amp;amp;id=2 in URL/?section=main&amp;amp;id=2
                    delete splits[0];
                    //Create the params string
                    var params = splits.join('&');
                    //Send the ajax request
                    WPOS.loadPageContent(params);
                }
            } else {
                WPOS.goToHome();
            }
        }
    };
    var timerId = null;
    this.startPageLoader = function(){
        // Event-driven (no busy 300ms poll): hashchange + popstate drive navigation.
        // Keep the 300ms fallback only for ancient browsers without hashchange.
        if (timerId) return;
        var onHash = function(){ try { WPOS.checkAnchor(); } catch(e){} };
        window.addEventListener('hashchange', onHash, false);
        if (!('onhashchange' in window)) {
            timerId = setInterval("WPOS.checkAnchor();", 1000);
        }
        WPOS._hashHandler = onHash;
        // fire once immediately so refresh/bookmark renders without waiting
        onHash();
    };
    this.stopPageLoader = function(){
        currentAnchor = '0';
        if (timerId) { clearInterval(timerId); timerId = null; }
        if (WPOS._hashHandler) { window.removeEventListener('hashchange', WPOS._hashHandler, false); WPOS._hashHandler = null; }
    };
    this.loadPageContent = function(query){
        var contenturl;
        if (sec=="faq"){
            contenturl = "https://cornerbites.co.ke/content/faq.php"
        } else {
            contenturl = "content/"+sec+".php";
        }
        // cache:false appends ?_=timestamp so section pages (e.g. the Website CMS)
        // are NEVER served from browser cache — admins always see the latest page.
        if (query) query += "&_=" + Date.now(); else query = "_=" + Date.now();
        // Stale-while-revalidate: keep the old section on screen (no blank flash),
        // swap only when the new HTML has fully arrived. Kill old pollers first
        // so stale intervals cannot repaint over the new section (multi-flash bug).
        try { if (window.CBClearPolls) window.CBClearPolls(); } catch(e){}
        try { ['_p1','_p2','_p3'].forEach(function(k){ if (window[k]) { clearInterval(window[k]); window[k] = null; } }); } catch(e){}
        var $main = $("#maincontent");
        // aria-busy while loading; keep content visible (subtle top progress, not full blank)
        $main.attr('aria-busy', 'true');
        $.get(contenturl, query, function(data){
            if (data=="AUTH"){
                WPOS.sessionExpired();
            } else {
                // Single rAF paint; old scroll resets intentionally on section change
                requestAnimationFrame(function(){
                    $main.html(data);
                    $main.attr('aria-busy', 'false');
                });
            }
        }, "html");
    };
    this.goToHome = function(){
        if (curuser.isadmin==1 || (curuser.sections.dashboard == "both" || curuser.sections.dashboard == "both")){
            changehash("!dashboard");
        } else {
            if (curuser.sections.dashboard == "realtime"){
                changehash("!realtime");
            } else {
                // load the first allowed section
                var secval;
                for (var i in curuser.sections){
                    if (i!="access" && i!="dashboard"){
                        secval = curuser.sections[i];
                        if (secval>0){
                            changehash("!"+i);
                            return;
                        }
                    }
                }
            }
        }
    };
    var curuser = "";
    // authentication — refresh must NOT force re-login when the PHP session is still valid.
    // Order: hello (session) → silent authrenew from persisted hash → only then show login.
    this.isLogged = function(){
        var showLogin = function(){
            $('#loadingdiv').hide();
            $('#logindiv').show();
            $("#loginbutton").removeAttr('disabled', 'disabled');
            $('#loginmodal').show();
            $("#loguser").val('');
            WPOS.util.hideLoader();
        };
        var adoptAndEnter = function(user){
            try {
                curuser = user;
                $.ajaxSetup({ beforeSend: function(xhr){ xhr.setRequestHeader('anti-csrf-token', (curuser ? curuser.csrf_token : '')); } });
                try {
                    if (curuser.username && (curuser.auth_hash || curuser.token)) localStorage.setItem('cb_auth', JSON.stringify({username: curuser.username, auth_hash: curuser.auth_hash || curuser.token}));
                } catch(e) {}
                WPOS.initAdmin();
                return true;
            } catch(e) { return false; }
        };
        // 1. Existing PHP session? (refresh with valid 12h sliding cookie)
        getLoginStatus(function(user){
            if (user && user !== false && user.id) { if (adoptAndEnter(user)) return; }
            // 2. Session gone (restart/expired)? Silent renew from persisted hash
            var saved = null;
            try { saved = JSON.parse(localStorage.getItem('cb_auth') || 'null'); } catch(e) {}
            if (saved && saved.username && saved.auth_hash) {
                try {
                    var r = WPOS.sendJsonData('authrenew', JSON.stringify({username: saved.username, auth_hash: saved.auth_hash}));
                    if (r && r.id) { if (adoptAndEnter(r)) return; }
                } catch(e) {}
            }
            // 3. Truly logged out — show the usual login screen.
            //    (No auto-login with hardcoded credentials: admin access requires
            //    an explicit login. Session resume above handles returning users.)
            showLogin();
        });
    };
    function getLoginStatus(callback){
        return WPOS.getJsonDataAsync("hello", callback);
    }
    var sessionTimer = null;
    var _sessionFailures = 0;
    function startSessionCheck(){
        if (sessionTimer==null){
            sessionTimer = setInterval(startSessionCheck, 120000);
            return;
        }
        getLoginStatus(function(user){
            if (user && user !== false && user.id) { _sessionFailures = 0; return; }
            // hello returned false — try a silent renew before counting a failure
            _sessionFailures++;
            try {
                var saved = JSON.parse(localStorage.getItem('cb_auth') || 'null');
                if (saved && saved.username && saved.auth_hash) {
                    var r = WPOS.sendJsonData('authrenew', JSON.stringify({username: saved.username, auth_hash: saved.auth_hash}));
                    if (r && r.id) { _sessionFailures = 0; curuser = r;
                        $.ajaxSetup({ beforeSend: function(xhr){ xhr.setRequestHeader('anti-csrf-token', (curuser ? curuser.csrf_token : '')); } });
                        try { localStorage.setItem('cb_auth', JSON.stringify({username: curuser.username, auth_hash: curuser.auth_hash || curuser.token})); } catch(e){}
                        return;
                    }
                }
            } catch(e) {}
            // Only log out after many consecutive failures (20 min) so a
            // transient blip never kicks the admin out.
            if (_sessionFailures >= 10) {
                _sessionFailures = 0;
                WPOS.sessionExpired();
            }
        });
    }
    function stopSessionCheck(){
        clearInterval(sessionTimer);
        sessionTimer = null;
    }
    function showLoginError(message){
        $("#login-banner-txt").text(message);
        $("#login-banner").removeClass('alert-success').addClass('alert-danger').show();
    }
    function showLoginDiv(message){
        if (message){
            $("#login-banner-txt").text(message);
            $("#login-banner").show();
        } else {
            $("#login-banner").hide();
        }
        // swap the loading panel for the login form (was missing -> stuck on "Initializing")
        $('#loadingdiv').hide();
        $('#logindiv').show();
        $('#loginbutton').removeAttr('disabled');
        $('#loginmodal').show();
    }
    function hideLoginDiv(){
        $('#loginmodal').hide();
        $('#loadingdiv').hide();
        $('#logindiv').show();
        $("#loginbutton").removeAttr('disabled', 'disabled');
    }
    this.login = function () {
        try {
            WPOS.util.showLoader();
            performLogin();
        } catch (e) {
            console.error("Login error:", e);
            alert("Login failed to start: " + (e && e.message ? e.message : e));
            WPOS.util.hideLoader();
            $("#loginbutton").removeAttr('disabled').val('Login');
        }
    };
    function performLogin(){
        WPOS.util.showLoader();
        var loginbtn = $('#loginbutton');
        // get form values
        var userfield = $("#loguser");
        var passfield = $("#logpass");
        var username = (userfield.val() || "").trim();
        var password = passfield.val() || "";
        // validate input before hitting the server
        if (!username || !password) {
            WPOS.util.hideLoader();
            showLoginError("Please enter both a username and a password.");
            alert("Please enter both a username and a password.");
            $(loginbtn).removeAttr('disabled').val('Login');
            return;
        }
        // disable login button
        $(loginbtn).attr('disabled', 'disabled');
        $(loginbtn).val('Proccessing');
        // hash password
        var hashed;
        try {
            hashed = WPOS.util.SHA256(password);
        } catch (e) {
            console.error("SHA256 error:", e);
            showLoginError("Could not process password.");
            alert("Could not process password: " + (e && e.message ? e.message : e));
            $(loginbtn).removeAttr('disabled').val('Login');
            WPOS.util.hideLoader();
            return;
        }
        // authenticate
        WPOS.sendJsonDataAsync("auth", JSON.stringify({username: username, password: hashed, getsessiontokens: true}), function(user){
            try {
                if (user !== false && user !== null && user !== undefined) {
                    if (user.isadmin==1 || (user.sections!=null && user.sections.access!='no')){
                        curuser = user;

                        $.ajaxSetup({
                            beforeSend: function(xhr, settings) {
                                xhr.setRequestHeader("anti-csrf-token", (curuser ? curuser.csrf_token : ""));
                            }
                        });

                        WPOS.initAdmin();
                    } else {
                        showLoginError("You do not have permission to enter this area.");
                        alert("You do not have permission to enter this area.");
                    }
                } else {
                    showLoginError("Incorrect username or password.");
                    alert("Login failed: incorrect username or password.");
                }
            } catch (e) {
                console.error("Login callback error:", e);
                showLoginError("Login error: " + (e && e.message ? e.message : e));
                alert("Login error: " + (e && e.message ? e.message : e));
            }
            passfield.val('');
            WPOS.util.hideLoader();
            $(loginbtn).val('Login');
            $(loginbtn).removeAttr('disabled', 'disabled');
        }, function(err){
            console.error("Login request error:", err);
            showLoginError("Network/server error: " + err);
            alert("Login request failed: " + err);
            passfield.val('');
            WPOS.util.hideLoader();
            $(loginbtn).val('Login');
            $(loginbtn).removeAttr('disabled', 'disabled');
        });
    }
    this.logout = function () {
        var answer = confirm("Are you sure you want to logout?");
        if (answer) {
            WPOS.util.showLoader();
            performLogout();
        }
    };
    function performLogout(){
        WPOS.util.showLoader();
        WPOS.stopSocket();
        WPOS.stopPageLoader();
        stopSessionCheck();
        WPOS.getJsonData("logout");
        showLoginDiv();
        WPOS.util.hideLoader();
    }
    this.sessionExpired = function(){
        // Aggressive keep-alive: try silent auth renew from persisted hash before logging out.
        // Transient failures (restart, blip) renew without ever flashing the login screen.
        try {
            var saved = null;
            try { saved = JSON.parse(localStorage.getItem('cb_auth') || 'null'); } catch(e) {}
            if (saved && saved.username && saved.auth_hash) {
                var renewed = false;
                try {
                    var r = WPOS.sendJsonData('authrenew', JSON.stringify({username: saved.username, auth_hash: saved.auth_hash}));
                    if (r !== false && r) {
                        curuser = r;
                        $.ajaxSetup({ beforeSend: function(xhr){ xhr.setRequestHeader('anti-csrf-token', (curuser ? curuser.csrf_token : '')); } });
                        renewed = true;
                    }
                } catch(e) {}
                if (renewed) { try { WPOS.initAdmin(); } catch(e) {} return; }
            }
        } catch(e) {}
        // Last resort: show the usual login screen (no silent auto-login).
        WPOS.stopPageLoader();
        WPOS.stopSocket();
        stopSessionCheck();
        showLoginDiv("Your session has expired, please login again.");
        WPOS.util.hideLoader();
    };
    this.initAdmin = function(){
        try {
            if (typeof initOrderSound === 'function') initOrderSound();
            // Persist for silent auto-renew ( survives PHP restart / cookie expiry )
            try {
                var ah = curuser && (curuser.auth_hash || curuser.token);
                if (curuser && curuser.username && ah) localStorage.setItem('cb_auth', JSON.stringify({username: curuser.username, auth_hash: ah}));
            } catch(e) {}
        } catch(e) {}
        // hide unallowed sections
        hidePermSections();
        // Load needed config (never let a config failure block navigation/page loader)
        try { fetchConfigTable(); } catch (e) { console.error('fetchConfigTable failed:', e); }
        // Start the page loader FIRST so section navigation always works even if config fetch failed.
        try { WPOS.startPageLoader(); } catch (e) { console.error('startPageLoader failed:', e); }
        startSessionCheck();
        hideLoginDiv();
    };
    function hidePermSections(){
        // hide/show settings
        if (curuser.isadmin==1){
            $(".privmenuitem").show();
            return;
        } else {
            $("#menusettings").hide();
        }
        // hide/show dashboard
        var dash = $("#menudashboard");
        var real = $("#menurealtime");
        switch (curuser.sections.dashboard){
            case "both":
                dash.show();
                real.show();
                break;
            case "standard":
                dash.show();
                real.hide();
                break;
            case "realtime":
                dash.hide();
                real.show();
                break;
            case "none":
                dash.hide();
                real.hide();
                break;
        }
        // hide/show sections
        curuser.sections.reports>0?$("#menureports").show():$("#menureports").hide();
        curuser.sections.reports>0?$("#menuaccounts").show():$("#menuaccounts").hide();
        curuser.sections.reports>0?$("#menuwebsitecms").show():$("#menuwebsitecms").hide();
        curuser.sections.graph>0?$("#menugraph").show():$("#menugraph").hide();
        curuser.sections.sales>0?$("#menusales").show():$("#menusales").hide();
        curuser.sections.invoices>0?$("#menuinvoices").show():$("#menuinvoices").hide();
        curuser.sections.items>0?$("#menuitems").show():$("#menuitems").hide();
        curuser.sections.stock>0?$("#menuinventory").show():$("#menuinventory").hide();
        curuser.sections.suppliers>0?$("#menusuppliers").show():$("#menusuppliers").hide();
        curuser.sections.customers>0?$("#menucustomers").show():$("#menucustomers").hide();
        // hide parent items menu if no submenu items visible
        if ((curuser.sections.items==0 && curuser.sections.categories==0) && (curuser.sections.suppliers==0 && curuser.sections.customers==0)){
            $("#menuparentitems").hide();
        } else {
            $("#menuparentitems").show();
        }
    }
    // data handling functions
    this.getJsonData = function(action){
        return getJsonData(action)
    };
    function getJsonData(action) {
        // send request to server
        var response = $.ajax({
            url     : "/api/"+action,
            type    : "GET",
            dataType: "text",
            timeout : 10000,
            cache   : false,
            async   : false
        });
        if (response.status == "200") {
            var json = JSON.parse(response.responseText);
            var errCode = json.errorCode;
            var err = json.error;
            if (err == "OK") {
                // echo warning if set
                if (json.hasOwnProperty('warning') && json.warning && String(json.warning).trim() !== ''){
                    alert(json.warning);
                }
                return json.data;
            } else {
                if (errCode == "auth") {
                    WPOS.sessionExpired();
                    return false;
                } else {
                    alert(err);
                    return false;
                }
            }
        }

        alert("There was an error connecting to the server: \n"+response.statusText);
        return false;
    }

    this.getJsonDataAsync = function (action, callback) {
        // send request to server
        try {
            $.ajax({
                url     : "/api/"+action,
                type    : "GET",
                dataType: "json",
                timeout : 10000,
                cache   : false,
                success : function(json){
                    var errCode = json.errorCode;
                    var err = json.error;
                    if (err == "OK") {
                        // echo warning if set
                        if (json.hasOwnProperty('warning') && json.warning && String(json.warning).trim() !== ''){
                            alert(json.warning);
                        }
                        if (callback)
                            callback(json.data);
                    } else {
                        if (errCode == "auth"){
                            WPOS.sessionExpired();
                            return false;
                        }
                        alert(err);
                        if (callback)
                            callback(false);
                    }
                },
                error   : function(jqXHR, status, error){
                    alert(error);
                    if (callback)
                        callback(false);
                }
            });
        } catch (ex) {
            alert("Exception: "+ex);
            if (callback)
                callback(false);
        }
    };

    this.sendJsonData = function  (action, data) {
        // send request to server
        var response = $.ajax({
            url     : "/api/"+action,
            type    : "POST",
            data    : {data: data},
            dataType: "text",
            timeout : 10000,
            cache   : false,
            async   : false
        });
        if (response.status == "200") {
            var json = JSON.parse(response.responseText);
            if (json == null) {
                alert("Error: The response that was returned from the server could not be parsed!");
                return false;
            }
            var errCode = json.errorCode;
            var err = json.error;
            if (err == "OK") {
                // echo warning if set
                if (json.hasOwnProperty('warning') && json.warning && String(json.warning).trim() !== ''){
                    alert(json.warning);
                }
                return json.data;
            } else {
                if (errCode == "auth") {
                    WPOS.sessionExpired();
                    return false;
                } else {
                    alert(err);
                    return false;
                }
            }
        }
        alert("There was an error connecting to the server: \n"+response.statusText);
        return false;
    };

    this.sendJsonDataAsync = function (action, data, callback, errorCallback) {
        // send request to server
        try {
            $.ajax({
                url     : "/api/"+action,
                type    : "POST",
                data    : {data: data},
                dataType: "json",
                timeout : 10000,
                cache   : false,
                success : function(json){
                    var errCode = json.errorCode;
                    var err = json.error;
                    if (err == "OK") {
                        // echo warning if set
                        if (json.hasOwnProperty('warning') && json.warning && String(json.warning).trim() !== ''){
                            alert(json.warning);
                        }
                        callback(json.data);
                    } else {
                        if (errCode == "auth") {
                            WPOS.sessionExpired();
                        } else {
                            if (typeof errorCallback == "function")
                                return errorCallback(json.error);
                            alert(err);
                        }
                        callback(false);
                    }
                },
                error   : function(jqXHR, status, error){
                    if (typeof errorCallback == "function")
                        return errorCallback(error);

                    alert(error);
                    callback(false);
                }
            });
            return true;
        } catch (ex) {
            if (typeof errorCallback == "function")
                return errorCallback(error.message);

            alert(ex.message);
            callback(false);
            return false;
        }
    };

    this.uploadFile = function (event, callback){
        event.stopPropagation(); // Stop stuff happening
        event.preventDefault(); // Totally stop stuff happening
        WPOS.util.showLoader();
        var files = event.target.files;
        // Create a formdata object and add the files
        var fd = new FormData();
        fd.append("file", files[0]);
        $.ajax({
            url: '/api/file/upload',
            type: 'POST',
            data: fd,
            cache: false,
            dataType: 'json',
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            success: function(data, textStatus, jqXHR){
                var errCode = data.errorCode;
                var err = data.error;
                if(err == 'OK'){
                    // Success so call function to process the form
                    callback(data.data);
                } else {
                    if (errCode == "auth") {
                        WPOS.sessionExpired();
                        return false;
                    } else {
                        alert(err);
                        return false;
                    }
                }
                // hide loader
                WPOS.util.hideLoader();
                return true;
            },
            error: function(jqXHR, textStatus, errorThrown){
                // Handle errors here
                alert("There was an error connecting to the server: \n"+response.statusText);
                // hide loader
                WPOS.util.hideLoader();
            }
        });
    };

    // function for event source processes
    this.startEventSourceProcess = function(url, dataCallback, errorCallback){
        if (typeof(EventSource) === "undefined"){
            alert("Your browser does not support EventSource, please update your browser to continue.");
            return;
        }
        showModalLoader();
        var jsonStream = new EventSource(url);
        jsonStream.onmessage = function (e) {
            var message = JSON.parse(e.data);
            if (message.hasOwnProperty('error') || message.hasOwnProperty('result'))
                jsonStream.close();

            if (typeof dataCallback == "function")
                dataCallback(message);
        };
        jsonStream.onerror = function(e){
            jsonStream.close();
            console.log("Stream closed on error");
            if (typeof errorCallback == "function")
                errorCallback(e);
        }
    };

    // socket control
    // Websocket updates & commands
    var socket = null;
    var socketon = false;
    var authretry = false;
    this.startSocket = function() {
        if (socket === null){
            var proxy = this.getConfigTable().general.feedserver_proxy;
            var port = this.getConfigTable().general.feedserver_port;
            var socketPath = window.location.protocol+'//'+window.location.hostname+(proxy==false ? ':'+port : '');
            socket = io.connect(socketPath);
            socketon = true;
            socket.on('connect_error', socketError);
            socket.on('reconnect_error', socketError);
            socket.on('error', socketError);
            socket.on('connect', function(){ socketon = true; if (typeof WPOS.onFeedUp === 'function') WPOS.onFeedUp(); });

            socket.on('updates', function (data) {
                switch (data.a) {
                    case "devices":
                        onlinedev = JSON.parse(data.data);
                        populateOnlineDevices(onlinedev);
                        break;

                    case "sale":
                        //alert(data.data);
                        processIncomingSale(data.data);
                        break;

                    case "regreq":
                        socket.emit('reg', {deviceid: 0, username: curuser.username});
                        break;

                    case "config":
                        if (data.type=="deviceconfig"){
                            if (data.data.hasOwnProperty('a')){
                                if (data.data.a=="removed")
                                    delete WPOS.devices[data.id];
                            } else {
                                WPOS.devices[data.data.id] = data.data;
                                WPOS.locations[data.data.locationid] = {name: data.data.locationname};
                            }
                        }
                        break;

                    case "error":
                        if (!authretry && data.data.hasOwnProperty('code') && data.data.code=="auth"){
                            authretry = true;
                            WPOS.stopSocket();
                            var result = WPOS.getJsonData('auth/websocket');
                            if (result===true){
                                WPOS.startSocket();
                                return;
                            }
                        }

                        alert(data.data.message);
                        break;
                }
                //alert(data.a);
            });
        } else {
            // This should never happen, kept for historic purposes
            socket.connect();
        }
    };

    function socketError(){
        socketon = false;
        authretry = false;
        // Do not show alerts for feed connectivity issues
        // Fall back to HTTP polling when the node feed server is unavailable (shared hosting).
        if (typeof WPOS.onFeedDown === 'function') WPOS.onFeedDown();
    }

    this.stopSocket = function(){
        if (socket !== null){
            socketon = false;
            authretry = false;
            socket.disconnect();
            socket = null;
        }
    };

    window.onbeforeunload = function(){
        socketon = false;
    };

    // data & config
    var configtable;

    this.devices = null;
    this.locations = null;
    this.users = null;
    function fetchConfigTable() {
        configtable = getJsonData("adminconfig/get");
        WPOS.devices = configtable.devices;
        WPOS.locations = configtable.locations;
        WPOS.users = configtable.users;
    }

    this.getConfigTable = function () {
        if (configtable == null) {
            return false;
        }
        return configtable;
    };

    this.setConfigSet = function (key, data) {
        configtable[key] = data;
    };

    this.getTaxTable = function () {
        if (configtable == null) {
            return false;
        }
        return configtable.tax;
    };

    this.putTaxTable = function(taxtable){
        configtable.tax = taxtable;
        this.transactions.refreshTaxSelects();
    };

    this.updateConfig = function(key, data){
        key = key.split("~");
        switch(key.length){
            case 1: configtable[key[0]] = data; return;
            case 2: configtable[key[0]][key[1]] = data; return;
            case 3: configtable[key[0]][key[1]][key[2]] = data; return;
        }
    };

    // CSV export functions
    this.initSave = function(filename, data){
        // Neat, reliable download: Blob URL (data: URIs truncate on large files in some browsers)
        var dlelem = $('#dlelem');
        var fname = (filename || 'export').replace(/[^A-Za-z0-9._-]/g, '_') + '.csv';
        try {
            var blob = new Blob(["\ufeff" + data], {type: 'text/csv;charset=utf-8;'});
            var url = window.URL && window.URL.createObjectURL ? window.URL.createObjectURL(blob) : ('data:text/csv;charset=utf8,' + encodeURIComponent(data));
            dlelem.attr('href', url).attr('download', fname)[0].click();
            setTimeout(function(){ try { window.URL && window.URL.revokeObjectURL(url); } catch(e) {} }, 4000);
        } catch(e) {
            dlelem.attr('href','data:text/csv;charset=utf8,' + encodeURIComponent(data)).attr('download', fname);
            $('#dlelem').get(0).click();
        }
    };

    this.table2CSV = function(el) {

        var csvData = [];

        //header
        var tmpRow = []; // construct header avalible array

        $(el).find('th').each(function() {
            if (!$(this).hasClass('noexport')) tmpRow[tmpRow.length] = formatData($(this).html());
        });

        row2CSV(tmpRow);

        // actual data
        $(el).find('tr').each(function() {
            var tmpRow = [];
            $(this).find('td').each(function() {
                if (!$(this).hasClass('noexport')) tmpRow[tmpRow.length] = formatData($(this).text());
            });
            tmpRow = row2CSV(tmpRow);
            if (tmpRow!=null)
                csvData[csvData.length] = tmpRow;
        });

        return csvData.join('\n');
    };

    this.data2CSV = function(headers, fields, data) {

        var csvData = [];

        //header
        csvData[csvData.length] = row2CSV(headers);

        for (var i in data){
            if (data.hasOwnProperty(i)) {
                var record = data[i];
                var tmpRow = [];
                for (var x = 0; x < fields.length; x++) {
                    var key = fields[x];
                    if (typeof key === 'object'){
                        tmpRow[tmpRow.length] = formatData(key.func(record[key.key], record));
                    } else {
                        if (record.hasOwnProperty(key)) {
                            tmpRow[tmpRow.length] = formatData(record[key]);
                        } else {
                            tmpRow[tmpRow.length] = '';
                        }
                    }
                }
                tmpRow = row2CSV(tmpRow);
                if (tmpRow!=null)
                    csvData[csvData.length] = tmpRow;
            }
        }

        return csvData.join('\n');

    };

    function row2CSV(tmpRow) {
        var tmp = tmpRow.join(''); // to remove any blank rows
        // alert(tmp);
        if (tmpRow.length > 0 && tmp != '') {
            return tmpRow.join(",");
        }
        return null;
    }

    function formatData(input) {
        if (typeof input === 'number')
            return input;

        if (typeof input === 'object')
            input = JSON.stringify(input);
        // replace " with â€œ
        var regexp = new RegExp(/["]/g);
        var output = input.replace(regexp, '""');
        //HTML
        regexp = new RegExp(/\<[^\<]+\>/g);
        output = output.replace(regexp, "");
        if (output == "") return '';
        return '"' + output + '"';
    }

    // Load globally accessable objects
    this.util = new WPOSUtil();
    this.transactions = new WPOSTransactions();
    this.customers = new WPOSCustomers();
}