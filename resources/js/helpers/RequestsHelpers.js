import { connectAjax } from "../commons/connectAjax";
import { enumAction } from "../commons/enumAction";

export class RequestsHelpers {

    static async get(options = {}) {
        const { urlApi } = options;
        const obj = new connectAjax(urlApi);
        return await obj.getRequest();
    }

    static async getRecurse(options = {}) {
        const { idRegister, urlApi } = options;
        const obj = new connectAjax(urlApi);
        obj.setParam(idRegister);
        return await obj.getRequest();
    }

    static async delRecurse(options = {}) {
        const { idRegister, urlApi } = options;
        const obj = new connectAjax(urlApi);
        obj.setParam(idRegister);
        obj.setAction(enumAction.DELETE)
        return await obj.deleteRequest();
    }
}