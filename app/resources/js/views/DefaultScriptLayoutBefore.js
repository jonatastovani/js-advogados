import instanceManager from "../commons/instanceManager";
import { TenantTypeConfig } from "../commons/TenantTypeConfig";

class DefaultScriptLayoutBefore {

    constructor() {
        this.initEvents();
    }

    initEvents() {
        this.#addEventsSelectedCustomDomain();
    }

    #addEventsSelectedCustomDomain() {

        if (window.domainCustom) {
            /** @type {TenantTypeConfig} */
            const config = instanceManager.setInstance('TenantTypeConfig', new TenantTypeConfig());
            config.setDomainCustom = window.domainCustom;

            const select = $(`#${config.getNameAttributeKey}`);
            select.on('change', function () {
                const selectedValue = select.val();
                config.setSelectedValue = selectedValue;
            });
        }
    }
}

$(function () {
    new DefaultScriptLayoutBefore();
});