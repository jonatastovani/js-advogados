import { connectAjax } from "../commons/connectAjax";

export class RequestsHelpers {

    static async getRecurse(options = {}) {
        const { idRegister, urlApi } = options;
        try {
            const obj = new connectAjax(urlApi);
            obj.setParam(idRegister);
            return await obj.getRequest();
        } catch (error) {
            throw new Error(error.message);
        }
    }
}