function init() {
    onInit(); //create offline db
    var sql = 'CREATE TABLE IF NOT EXISTS authentication(id VARCHAR NOT NULL PRIMARY KEY, user VARCHAR NOT NULL, pass VARCHAR NOT NULL);';
    createTable(sql); //create table
}

function authentication() {
    var user = $("#username").val();
    var pass = $("#password").val();

    if (!validation(user, pass)) //Validation iput data
        return false;

    if (checkNetworkStatus()) //Check user online
    {
        validUserOnline(user, pass);
    } 
}

function validation(user, pass) {
    if (user == '' || pass == '') {
        alert('Username and password must not be empty');
        return false
    }

    return true;
}

function validUserOnline(user, pass) {
    data = {
        username: user,
        password: pass
    };
	doAjax('dclogin', 'post', data, 'Authenticating...',
	//doAjax('dc/authentication.php', 'post', data, 'Authenticating...',
		function(data) {
			if (data == 'OK' || data.message=='ok') {
				insertUser(generateID(), user, pass);
				writeParameter('username',user);
				var gotoPage = readParameter("loginGotoPage");
				var loginToDo = readParameter("loginToDo");
				checkLoginTo(loginToDo, gotoPage);
			} else {
				alert('Invalid user');
			}
		},
		function(data) {
			alert('Authentication fail. Please check network connection to Energy Builder system.\n\n'+data.responseText);
		},
	);
}

function insertUser(id, username, password) {
    var query = 'insert into authentication(id, user, pass) values (?,?,?)';

    var params = [id, username, password];
    try {
        localDB.transaction(function(transaction) {
            transaction.executeSql(query, params, nullDataHandler, errorHandler);
            console.log("insertUser: insert user successful.");
        });
    } catch (e) {
        console.log("insertUser - catch: " + e);
        return;
    }
}

function isUserExists(user, pass) {
    var query = 'select 1 from authentication where user = ? and pass = ?';
    var params = [user, pass];
    localDB.transaction(function(transaction) {
        transaction.executeSql(query, params, function(transaction, results) {
            if (results.rows.length > 0) {
                window.location.assign("main.html");
                console.log("User exists");
                return true;
            } else {
                window.location.assign("login.html");
                alert('User invalid');
                console.log('User not exists');
            }
            return false;
        }, errorHandler);
    });
}