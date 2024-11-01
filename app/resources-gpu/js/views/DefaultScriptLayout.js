import { RedirectHelper } from "../helpers/RedirectHelper";

class DefaultScriptLayout {
    constructor() {
        this.initEvents();
    }
    initEvents() {
        // Verifica o parâmetro uuid_message e exibe a mensagem, se necessário
        RedirectHelper.checkUUIDMessage();
    }
}

$(function () {
    new DefaultScriptLayout();
});