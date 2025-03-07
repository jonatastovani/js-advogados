import instanceManager from "../commons/instanceManager";
import { TenantTypeDomainCustom } from "../commons/TenantTypeDomainCustom";
import TenantTypeDomainCustomHelper from "../helpers/TenantTypeDomainCustomHelper";

class DefaultScriptLayoutBefore {

    constructor() {
        this.initEvents();
    }

    initEvents() {
        this.#addEventsSelectedCustomDomain();
    }

    #addEventsSelectedCustomDomain() {
        const self = this;

        if (window.domainCustom) {
            /** @type {TenantTypeDomainCustom} */
            const custom = instanceManager.setInstance(TenantTypeDomainCustomHelper.getNameInstanceCustomDomain, new TenantTypeDomainCustom());
            custom.setDomainCustom = window.domainCustom;

            const select = $(`#${custom.getNameAttributeKey}`);
            select.on('change', function () {
                const selectedValue = select.val();
                custom.setSelectedValue = selectedValue;
                self.#updateDisplayCustomDomain();
                custom.setEnqueueReady();
            });

            const selected = custom.getSelectedValue;
            if (!Number.isNaN(selected) && Number.isInteger(selected)) {
                select.val(selected);
            }

            self.#updateDisplayCustomDomain();
        }
    }

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

$(function () {
    new DefaultScriptLayoutBefore();
});