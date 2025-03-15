import { CommonFunctions } from "../commons/CommonFunctions";
import { BootstrapFunctionsHelper } from "../helpers/BootstrapFunctionsHelper";
import { RedirectHelper } from "../helpers/RedirectHelper";
import SimpleBarHelper from "../helpers/SimpleBarHelper";

class DefaultScriptLayoutAfter {

    constructor() {
        this.initEvents();
    }

    initEvents() {
        // Verifica o parâmetro uuid_message e exibe a mensagem, se necessário
        RedirectHelper.checkUUIDMessage();
        SimpleBarHelper.apply();
        BootstrapFunctionsHelper.addEventPopover();
        BootstrapFunctionsHelper.addEventTooltip();
        this.#toggleCollapseMenu();
    }

    #toggleCollapseMenu() {

        // Seleciona todos os itens de menu com dropdown-indicator
        const menuItems = $('.nav-link.dropdown-indicator');

        // Restaura o estado do menu com base no localStorage
        menuItems.each(function () {
            const menu = $(this);
            const menuId = menu.attr('href').substring(1); // Extrai o ID do menu (ex: 'nv-financeiro')
            const isExpanded = CommonFunctions.getItemLocalStorage(`menu-${menuId}`) === 'true'; // Verifica o estado salvo

            if (isExpanded) {
                const parentWrapper = $(`#${menuId}`);
                parentWrapper.addClass('show'); // Expande o menu
                menu.removeClass('collapsed'); // Ajusta o indicador visual
                menu.attr('aria-expanded', 'true');
            }
        });

        // Adiciona evento para salvar o estado do menu ao expandir/recolher
        menuItems.on('click', function () {
            const menu = $(this);
            const menuId = menu.attr('href').substring(1); // Extrai o ID do menu (ex: 'nv-financeiro')
            const isExpanded = menu.hasClass('collapsed'); // Verifica se está colapsado

            // Salva o estado no localStorage usando as funções auxiliares
            CommonFunctions.setItemLocalStorage(`menu-${menuId}`, !isExpanded);
        });
    }
}

$(function () {
    new DefaultScriptLayoutAfter();
});