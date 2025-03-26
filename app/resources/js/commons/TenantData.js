import { DateTimeHelper } from "../helpers/DateTimeHelper";
import { CommonFunctions } from "./CommonFunctions";
import { ConnectAjax } from "./ConnectAjax";

export class TenantData {
    /**
     * Objeto de configuração interna
     * @private
     * @type {Object}
     */
    _objConfigs = {
        url: {
            base: `${window.apiRoutes.baseTenant}/current`,
        },
        data: {
            tenant: undefined,
            last_updated: undefined,
        },
    };

    constructor() { }

    /**
     * Atualiza os dados do tenant e o timestamp de atualização
     * @param {Object} response
     */
    set setTenantData(response) {
        this._objConfigs.data.tenant = response.data;
        this._objConfigs.data.last_updated = DateTimeHelper.retornaDadosDataHora(new Date(), 11);
    }

    /**
     * Retorna os dados do tenant, buscando da API se necessário
     * @returns {Promise<Object>}
     */
    async getTenantData() {
        if (this.#precisaAtualizarTenant()) {
            await this.#buscaDadosTenant();
        }
        return this._objConfigs.data.tenant;
    }

    /**
     * Verifica se os dados do tenant precisam ser atualizados
     * @private
     * @returns {boolean}
     */
    #precisaAtualizarTenant() {
        const last = this._objConfigs.data.last_updated;
        const now = DateTimeHelper.retornaDadosDataHora(new Date(), 11);

        if (!this._objConfigs.data.tenant || !last) return true;

        return DateTimeHelper.retornaDiferencaDeDataEHora(last, now, 6) > 5;
    }

    /**
     * Busca os dados atualizados do tenant da API
     * @private
     */
    async #buscaDadosTenant() {
        try {
            const objConn = new ConnectAjax(this._objConfigs.url.base);
            const response = await objConn.getRequest();
            this.setTenantData = response; // chama o setter
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        }
    }
}
