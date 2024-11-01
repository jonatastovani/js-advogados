import { BootstrapFunctionsHelper } from "../helpers/BootstrapFunctionsHelper";
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
        BootstrapFunctionsHelper.addEventPopover();
        BootstrapFunctionsHelper.addEventTooltip();
    }
}

$(function () {
    new DefaultScriptLayout();
});