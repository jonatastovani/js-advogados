import { commonFunctions } from "./commonFunctions";
import { enumAction } from "./enumAction";

export class connectAjax {

    #urlApi;
    #action;
    #data;
    #param;
    #debugMode;
    #debugStackMode;
    #csrfToken;
    #addCsrfTokenBln;

    constructor(urlApi) {
        this.#urlApi = urlApi;
        this.#action = null;
        this.#data = null;
        this.#param = null;
        this.#debugMode = true;
        this.#debugStackMode = false;
        this.#addCsrfTokenBln = true;
    }

    setAction(action) {
        if (enumAction.isValid(action)) {
            this.#action = action;
            return true;
        } else {
            console.error('Ação inválida');
            return false;
        }
    }

    setData(data) {
        this.#data = data;
    }

    setParam(param) {
        this.#param = param;
    }

    get _getUrlApi() {
        return this.#urlApi;
    }

    set _setUrlApi(urlApi) {
        this.#urlApi = urlApi;
    }

    set setCsrfToken(token) {
        this.#csrfToken = token;
    }

    set setAddCsrfTokenBln(bool) {
        this.#addCsrfTokenBln = bool;
    }

    async getRequest() {
        const param = this.#param ? `/${this.#param}` : '';
        const method = this.#action || "GET";
        const data = this.#data ? JSON.stringify(this.#data) : null;

        this.#debug(`URL = ${this.#urlApi + param}`, `Param = ${param}`, `Method = ${method}`, `Data = ${data}`);

        try {
            if (this.#addCsrfTokenBln == true) {
                this.#addAuthorizationToken();
            }
            const response = await $.ajax({
                url: this.#urlApi + param,
                method: method,
                contentType: "application/json",
                data: data,
                dataType: "json",
            });
            this.#debug('Response = ', response);
            return response;
        } catch (xhr) {
            this.#debugError(xhr);
            throw this.#handleError(xhr);
        }
    }

    async envRequest() {
        const param = this.#param ? `/${this.#param}` : '';

        this.#debug(`URL = ${this.#urlApi + param}`, `Param = ${param}`, `Method = ${this.#action}`, `Data = ${JSON.stringify(this.#data)}`);

        try {
            if (this.#addCsrfTokenBln == true) {
                this.#addAuthorizationToken();
            }
            const response = await $.ajax({
                url: this.#urlApi + param,
                method: this.#action,
                data: JSON.stringify(this.#data),
                contentType: "application/json",
            });
            this.#debug('Response = ', response);
            return response;
        } catch (xhr) {
            this.#debugError(xhr);
            throw this.#handleError(xhr);
        }
    }

    async deleteRequest() {
        const param = this.#param ? `/${this.#param}` : '';

        this.#debug(`URL = ${this.#urlApi + param}`, `Param = ${param}`, `Method = ${this.#action}`, `Data = ${JSON.stringify(this.#data)}`);

        try {
            if (this.#addCsrfTokenBln == true) {
                this.#addAuthorizationToken();
            }
            const response = await $.ajax({
                url: this.#urlApi + param,
                type: this.#action,
                contentType: "application/json",
            });
            return response;
        } catch (xhr) {
            this.#debugError(xhr);
            throw this.#handleError(xhr);
        }
    }

    #handleError(xhr) {
        // if (xhr.status === 401) {
        //     return { status: xhr.status, message: 'Este usuário não possui permissão para realizar esta ação. Possíveis razões:', itemsMessage: ['Sessão expirada', 'Permissões insuficientes'] };
        // } else 
        if (xhr.responseText) {
            return this.#parseErrorResponse(xhr);
        } else {
            return { xhr: xhr, status: xhr.status, message: 'Erro interno no servidor API.' };
        }
    }

    #parseErrorResponse(xhr) {
        try {
            const responseText = JSON.parse(xhr.responseText);
            let errors = responseText.data?.errors || [];

            if (!Array.isArray(errors)) {
                errors = Object.values(errors).flat();
            }

            let error = responseText.data?.error || '';
            if (error) {
                errors.push(error);
            }
            
            const messageNotify = `${responseText.message}\n${errors.join('\n')}`;

            return {
                status: xhr.status,
                message: responseText.message,
                traceId: responseText.trace_id ?? undefined,
                errors: errors,
                itemsArray: errors,
                joinErrors: errors.join('\n'),
                messageNotify: messageNotify,
                htmlAlert: commonFunctions.returnHTMLAlert(responseText.message, 'error', { itemsArray: errors }),
            };
        } catch (parseError) {
            console.error('Erro ao parsear resposta:', parseError);
            return { status: xhr.status, description: xhr.responseText };
        }
    }

    #debug(...messages) {
        if (this.#debugMode) {
            messages.forEach(msg => console.log(msg));
        }
        if (this.#debugStackMode) {
            try {
                throw new Error();
            } catch (e) {
                console.log(`Pilha = ${e.stack}`);
            }
        }
    }

    #debugError(xhr) {
        if (this.#debugMode) {
            console.error(xhr);
        }
    }

    #addAuthorizationToken() {
        let csrfToken = this.#csrfToken ?? $('meta[name="csrf-token"]').attr('content');
        let xsrfCookie = '';

        if (!csrfToken) {
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                if (cookie.trim().startsWith('XSRF-TOKEN=')) {
                    xsrfCookie = decodeURIComponent(cookie.trim().substring('XSRF-TOKEN='.length));
                    break;
                }
            }
        }

        if (csrfToken) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
        } else if (xsrfCookie) {
            $.ajaxSetup({
                headers: {
                    'X-XSRF-TOKEN': xsrfCookie,
                    'Accept': 'application/json',
                }
            });
        } else {
            console.error('Token CSRF não encontrado na página ou nos cookies.');
        }
    }

}