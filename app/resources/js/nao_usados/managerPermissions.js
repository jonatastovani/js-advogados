import { connectAjax } from "../commons/connectAjax";

export class managerPermissions {

    #objConfigs;
    #setPermissions;
    #loadingPromise;

    constructor() {
        this.#objConfigs = {
            blnLoadingPermissions: false,
            blnLoadInitial: false,
        };
        this.#setPermissions = new Set();
        this.#loadingPromise = null;
    }

    async #loadPermissions() {
        if (!this.#objConfigs.blnLoadingPermissions) {
            this.#objConfigs.blnLoadingPermissions = true;
            this.#loadingPromise = this.#fetchPermissions().then(permissions => {
                permissions.forEach(permission => this.#setPermissions.add(permission));
                this.#objConfigs.blnLoadingPermissions = false;
            }).catch(error => {
                console.error('Erro ao carregar permissões:', error);
                this.#objConfigs.blnLoadingPermissions = false;
            });
        }
        return this.#loadingPromise;
    }

    async #fetchPermissions() {
        try {
            const obj = new connectAjax(`api/routes/user/getBuscarPermissoesDoUsuarioAutenticado.php`);
            const response = await obj.getRequest();
            if (response.data) {
                if (response.data.length) {
                    return response.data;
                }
            }
            return [];
        } catch (error) {
            console.error(error);
            return [];
            const traceId = error.traceId ? error.traceId : undefined;
            // commonFunctions.generateNotification(commonFunctions.firstUppercaseLetter(error.message), 'error', { traceId: traceId });
        }
    }

    async hasPermission(arrPermission, any = false) {
        const self = this;
        if (!self.#objConfigs.blnLoadInitial) {
            self.#loadingPromise = await self.#loadPermissions();
            self.#objConfigs.blnLoadInitial = true;
        }
        await self.#loadingPromise;

        let countSuccess = 0;
        for (const permission of arrPermission) {
            for (const item of self.#setPermissions) {
                if (item == permission) {
                    if (any === true || arrPermission.length == 1) {
                        return true;
                    }
                    countSuccess++;
                }
            }
            if (any === false) {
                return false;
            }
        }
        if (arrPermission.length === countSuccess) {
            return true;
        }
        return false;
    }
}
