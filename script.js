console.log("Hello from ChatSpace...");
let request;

function handleUserLogin() {
    $('.login').submit(function (event) {
        event.preventDefault();
        if(request){
            request.abort();
        }
        let $form = $(this);
        let $inputs = $form.find("input, select, button, textarea");
        let form_data = $($form).serialize();
        $inputs.prop("disable", true);
        //console.log("form data" + form_data);
        request = $.ajax({url:'auth.php', type:'post', data:form_data});
        request.done(function(result) {
            console.log("result");
            alert(result);
        });
        request.fail(function(jqXHR, result, errorThrown) {
            console.error(result, errorThrown);
        });
        request.always(function() {
            $inputs.prop("disable",false);
        });

    });
}
