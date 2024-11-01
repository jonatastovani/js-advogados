
class PageLogin {

    constructor() {
        this.initEvents();
    }

    initEvents() {
        this.#addEventosBotoes();
    }

    #addEventosBotoes() {

        $("#show-password").click(function () {
            var passwordField = $("#password");
            var passwordType = passwordField.attr("type");
            if (passwordType === "password") {
                passwordField.attr("type", "text");
                $("#show-password i").removeClass("bi bi-eye-fill");
                $("#show-password i").addClass("bi bi-eye-slash-fill");
            } else {
                passwordField.attr("type", "password");
                $("#show-password i").removeClass("bi bi-eye-slash-fill");
                $("#show-password i").addClass("bi bi-eye-fill");
            }

        });

        $('#send').click(async function () {
            $('.error_login').html('');
        });

        $('#username').val('admin');
        $('#password').val('admin123');    
    }
}

$(function () {
    new PageLogin();
});