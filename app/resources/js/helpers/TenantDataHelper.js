import InstanceManager from "../commons/InstanceManager";
import { TenantData } from "../commons/TenantData";

export class TenantDataHelper {

    static async getTenantData(options = {}) {
        /**
         * @type {TenantData}
         */
        const tenantData = InstanceManager.getOrCreateInstance('TenantData', () => new TenantData());
        return await tenantData.getTenantData();
    }
}
