import InstanceManager from "../commons/InstanceManager";
import { TenantTypeDomainCustom } from "../commons/TenantTypeDomainCustom";
import TenantTypeDomainCustomHelper from "../helpers/TenantTypeDomainCustomHelper";
import { URLHelper } from "../helpers/URLHelper";

class DefaultScriptLayoutBefore {

    constructor() {
        this.initEvents();
    }

    initEvents() {
        if (window.domainCustom) {
            this.#addEventsSelectedCustomDomain();
        }
    }

    /**
     * Intercepta a URL para obter o domínio herdado antes de definir o valor customizado.
     */
    #handleDomainInheritance() {
        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
        const inheritedDomain = URLHelper.getParameterURL(instance.getNameAttributeKey);

        if (inheritedDomain) {
            URLHelper.removeURLParameter(instance.getNameAttributeKey);

            instance.setSelectedValue = inheritedDomain;
        }

        this.#interceptNewTabClicks();
    }

    /**
     * Intercepta cliques em links para adicionar o parâmetro para herdar o domínio ao abrir novas guias.
     */
    #interceptNewTabClicks() {
        document.addEventListener("click", (event) => {
            const link = event.target.closest("a");
            if (!link || !link.href) return;

            // Verifica se o clique é para abrir em uma nova guia
            if (event.ctrlKey || event.metaKey || event.button === 1 || link.target === "_blank") {
                link.href = this.#appendDomainToUrl(link.href);
            }
        });

        const originalWindowOpen = window.open;
        window.open = (url, name, specs) => {
            return originalWindowOpen(this.#appendDomainToUrl(url), name, specs);
        };
    }

    /**
     * Adiciona o parâmetro para herdar o domínio à URL.
     * @param {string} url - URL original.
     * @returns {string} URL modificada com para herdar o domínio, se aplicável.
     */
    #appendDomainToUrl(url) {
        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
        const selectedId = instance.getSelectedValue;

        if (!selectedId) return url;

        const newUrl = new URL(url, window.location.origin);
        if (!newUrl.searchParams.has(instance.getNameAttributeKey)) {
            newUrl.searchParams.set(instance.getNameAttributeKey, selectedId);
        }
        return newUrl.toString();
    }

    /**
     * Adiciona eventos ao seletor de domínios, garantindo que a seleção seja aplicada corretamente.
     */
    #addEventsSelectedCustomDomain() {
        const self = this;

        /** @type {TenantTypeDomainCustom} */
        const instance = InstanceManager.setInstance(TenantTypeDomainCustomHelper.getNameInstanceCustomDomain, new TenantTypeDomainCustom());

        instance.setDomainCustom = window.domainCustom;

        this.#handleDomainInheritance();

        const select = $(`#${instance.getNameAttributeKey}`);
        select.on('change', function () {
            const selectedValue = select.val();
            instance.setSelectedValue = selectedValue;
            self.#updateDisplayCustomDomain();
            instance.setEnqueueReady();
        });

        // Aplica o valor herdado ou já salvo
        const selected = instance.getSelectedValue;
        if (!Number.isNaN(selected) && Number.isInteger(selected)) {
            select.val(selected);
        }

        self.#updateDisplayCustomDomain();
    }

    /**
     * Atualiza a exibição do domínio no título da página e nos elementos visuais.
     */
    #updateDisplayCustomDomain() {
        const config = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
        if (config) {
            let name = '';
            if (!Number.isNaN(config.getSelectedValue) && config.getSelectedValue > 0) {
                const current = config.getDataCurrentDomain;
                name = ` • ${current.name}`;
            }

            const pageTitle = $(`#page-title`);
            let yieldTitle = pageTitle.data('yield-title');

            yieldTitle = yieldTitle ? ` - ${yieldTitle}` : ``;

            pageTitle.html(`${pageTitle.data('base-title')}${name}${yieldTitle}`);
            $(`.current-domain-name`).html(name);
        }
    }
}

// Inicializa a classe ao carregar a página
$(function () {
    new DefaultScriptLayoutBefore();
});
