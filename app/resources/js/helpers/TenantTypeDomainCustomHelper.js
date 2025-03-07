import instanceManager from "../commons/instanceManager";
import { TenantTypeDomainCustom } from "../commons/TenantTypeDomainCustom";

export default class TenantTypeDomainCustomHelper {

    /** @returns {TenantTypeDomainCustom | false} */
    static get getInstanceTenantTypeDomainCustom() {
        return instanceManager.instanceVerification(this.getNameInstanceCustomDomain);
    }

    static get getNameInstanceCustomDomain() {
        return 'TenantTypeDomainCustom';
    }

    static get getDomainsOptions() {
        if (this.getInstanceTenantTypeDomainCustom) {
            const custom = this.getInstanceTenantTypeDomainCustom;
            return custom.getArrayDomains;
        }
    }

    static get getDataCurrentDomain() {
        return this.getInstanceTenantTypeDomainCustom.getDataCurrentDomain;
    }

    static setThHTMLTable() {

    }
}
