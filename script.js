console.log("Hello from ChatSpace...");
let request;

function handleUserLogin() {
    handleAjax('.login', 'auth.php');
}

function handleUserSignUp() {
    handleAjax('.signUp', 'signUp.php');
}

function handleCheckEmail() {
    checkValidityAjax('.emailAddr', '.emailAddr', 'checkEmail.php', '#email_err', "Email already exists.");
}

function handleCheckUserName() {
    checkValidityAjax('.usrName', '.usrName', 'checkUserName.php', '#username_err', "This User Name already exists.");
}

function handleCheckDOB() {
    $('.dob').change(function () {
        console.log(this.value);
        var eighteenYearsAgo = moment().subtract(18, 'years');
        var dateOfBirth = moment(this.value);
        if(dateOfBirth.isAfter(eighteenYearsAgo)) {
            $('#dob_err').text('You must be 18 years or older.');
        } else {
            $('#dob_err').text("");
        }
    });
    //checkValidityAjax('.emailAddr', 'checkEmail.php', "Email already exists.");
}

function handleCheckPassword() {
    checkValidityAjax('.pass2', '.signUp', 'checkPasswordMatch.php', '#password_err', "Passwords do not match.");
}


//.change
function checkValidityAjax(classField, serialField, fileURL, errID, errString) {
    $(classField).keyup(function () {
        //var text = $(this).value
        let data = $(serialField).serialize();
        request = $.ajax({url:fileURL, type:'post', data:data});
        request.done(function (result) {
           console.log(result);
           if(result == true) {
               $(errID).text(errString);
           } else {
               $(errID).text("");
           }
        });

    })
}

function handleAjax(formClass, fileURL) {
    $(formClass).submit(function (event) {
        event.preventDefault();
        if(request){
            request.abort();
        }
        let $form = $(this);
        let $inputs = $form.find("input, select, button, textarea");
        let form_data = $($form).serialize();
        $inputs.prop("disable", true);
        //console.log("form data" + form_data);
        request = $.ajax({url:fileURL, type:'post', data:form_data});
        request.done(function(result) {
            console.log(result);
            if (formClass === '.login'){
                handleResultLogin(result)
            } else if (formClass === '.signUp') {
                handleResultSignUp(result)
            }
        });
        request.fail(function(jqXHR, result, errorThrown) {
            console.error(result, errorThrown);
        });
        request.always(function() {
            $inputs.prop("disable",false);
        });

    });
}

function handleResultLogin(result) {
    alert(result);
}

function handleResultSignUp(result) {
    if(result == true) {
        $('#form_err').text("One or more fields are incorrect.");
    } else {
        $('#form_err').text("");
        alert("You were successful signing up for ChatSpace.");
        location.href = 'index.html';
    }
}