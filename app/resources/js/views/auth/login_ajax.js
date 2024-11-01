
import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";

$(document).ready(function () {

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

    // $('#send').click(async function () {
    //     const data = commonFunctions.getInputsValues($('#form_login')[0]);
    //     if (sendVerifications(data)) {
    //         try {
    //             commonFunctions.simulateLoading(this);

    //             const apiService = new ApiService('http://10.14.20.22:8000');
    //             await apiService.initCsrf();

    //             const get = await apiService.post(window.apiRoutes.urlLogin, data);
    //             console.log(get);
    //             window.location.href = get.data.redirect;

    //         } catch (error) {
    //             commonFunctions.generateNotificationErrorCatch(error);
    //         } finally {
    //             commonFunctions.simulateLoading(this, false);
    //         }
    //     }
    // })

    $('#send').on('click', async function (e) {
        e.preventDefault();
        const data = commonFunctions.getInputsValues($('#form_login')[0]);
        if (sendVerifications(data)) {
            try {
                commonFunctions.simulateLoading(this);

                const objCsrf = new connectAjax('/sanctum/csrf-cookie');
                objCsrf.setAddCsrfTokenBln = false;
                const responseCsrf = await objCsrf.getRequest();

                const obj = new connectAjax(window.apiRoutes.urlLogin);
                obj.setAction(enumAction.POST);
                obj.setData(data);
                const response = await obj.envRequest();
                console.log(response);
                window.location.href = response.data.redirect;
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(this, false);
            }
        }
    })

    function sendVerifications(data) {
        let blnSave = commonFunctions.verificationData(data.username, { field: $('#username'), messageInvalid: 'O Username deve ser informado.', setFocus: true });
        blnSave = commonFunctions.verificationData(data.password, { field: $('#password'), messageInvalid: 'A senha deve ser informada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        return blnSave;
    }

    $('#username').val('admin');
    $('#password').val('admin123');
    // $('#send').click();

});