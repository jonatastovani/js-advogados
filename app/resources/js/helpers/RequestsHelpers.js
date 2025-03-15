import { ConnectAjax } from "../commons/ConnectAjax";
import { EnumAction } from "../commons/EnumAction";

export class RequestsHelpers {

    static async get(options = {}) {
        const { urlApi } = options;
        const obj = new ConnectAjax(urlApi);
        return await obj.getRequest();
    }

    static async getRecurse(options = {}) {
        const { idRegister, urlApi } = options;
        const obj = new ConnectAjax(urlApi);
        obj.setParam(idRegister);
        return await obj.getRequest();
    }

    static async delRecurse(options = {}) {
        const { idRegister, urlApi } = options;
        const obj = new ConnectAjax(urlApi);
        obj.setParam(idRegister);
        obj.setAction(EnumAction.DELETE)
        return await obj.deleteRequest();
    }
}