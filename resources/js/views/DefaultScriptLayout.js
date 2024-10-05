import { RedirectHelper } from "../helpers/RedirectHelper";
import SimpleBarHelper from "../helpers/SimpleBarHelper";

class DefaultScriptLayout {
    constructor() {
        this.initEvents();
    }
    initEvents() {
        // Verifica o parâmetro uuid_message e exibe a mensagem, se necessário
        RedirectHelper.checkUUIDMessage();
        SimpleBarHelper.apply();
    }
}

$(function () {
    new DefaultScriptLayout();
});