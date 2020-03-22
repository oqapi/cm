function start_session(type, lang, success_fun, cancelled_fun, error_fun) {
    console.log("Button clicked");
    $.get('start_session.php?type=' + type + '&lang=' + lang, function(sessionpackagejson) {
        let sessionpackage = JSON.parse(sessionpackagejson);
        console.log(sessionpackage);
        let options = {server: sessionpackage.sessionPtr.u.split('/irma')[0], token: sessionpackage.token, language: lang};
        let promise = irma.handleSession(sessionpackage.sessionPtr, options);

        let success = function (data) {
            console.log("Session successful!");
            console.log("Result:", data);
            success_fun(data);
        };

        let error = function(data) {
            if(data === 'CANCELLED') {
                console.log("Session cancelled!");
                cancelled_fun(data);
            }
            else {
                console.log("Session failed!");
                console.log("Error data:", data);
                error_fun(data);
            }
        };

        promise.then(success, error);
    });
}
