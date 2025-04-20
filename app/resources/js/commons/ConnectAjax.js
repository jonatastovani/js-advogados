import TenantTypeDomainCustomHelper from "../helpers/TenantTypeDomainCustomHelper";
import { CommonFunctions } from "./CommonFunctions";
import { EnumAction } from "./EnumAction";

export class ConnectAjax {

    #urlApi;
    #action = null;;
    #data = null;;
    #param = null;;
    #debugMode = true;;
    #debugStackMode = false;
    #csrfToken = undefined;
    #addCsrfTokenBln = true;
    #forcedDomainCustomId = undefined;

    constructor(urlApi) {
        this.#urlApi = urlApi;
    }

    setAction(action) {
        if (EnumAction.isValid(action)) {
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

    set setForcedDomainCustomId(id) {
        this.#forcedDomainCustomId = id;
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
            this.#checkTenantTypeDomainCustom();

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
            this.#checkTenantTypeDomainCustom();

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
        this.#action = EnumAction.DELETE;

        this.#debug(`URL = ${this.#urlApi + param}`, `Param = ${param}`, `Method = ${this.#action}`, `Data = ${JSON.stringify(this.#data)}`);

        try {
            if (this.#addCsrfTokenBln == true) {
                this.#addAuthorizationToken();
            }
            this.#checkTenantTypeDomainCustom();

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
        if (xhr.responseText) {
            return this.#parseErrorResponse(xhr);
        } else if (xhr.status === 0) {
            if (!navigator.onLine) {
                return {
                    status: 0,
                    message: 'Sem conexão com a internet. Verifique sua rede e tente novamente.',
                };
            } else {
                return {
                    status: 0,
                    message: 'Não foi possível conectar ao servidor de API. O servidor pode estar fora do ar.',
                };
            }
        } else {
            return {
                xhr: xhr,
                status: xhr.status,
                message: 'Erro interno no servidor API.',
            };
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
                htmlAlert: CommonFunctions.returnHTMLAlert(responseText.message, 'error', { itemsArray: errors }),
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
            if (xhr.responseJSON) {
                console.error(xhr.responseJSON);
            }
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

    #checkTenantTypeDomainCustom() {
        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
        if (instance && instance.getStatusBlnCustom) {
            // console.warn(`Valor do domain: ${custom.getSelectedValue}`);
            $.ajaxSetup({
                headers: {
                    [instance.getHeaderAttributeKey]: this.#forcedDomainCustomId ?? instance.getSelectedValue,
                },
            });
        }
    }

    async downloadPdf(fileName = 'document.pdf', openInNewWindow = false) {
        const param = this.#param ? `/${this.#param}` : '';
        const method = this.#action || "GET";
        const data = this.#data ? JSON.stringify(this.#data) : null;

        this.#debug(`URL = ${this.#urlApi + param}`, `Param = ${param}`, `Method = ${method}`, `Data = ${data}`);

        try {
            if (this.#addCsrfTokenBln) {
                this.#addAuthorizationToken();
            }
            this.#checkTenantTypeDomainCustom();

            const response = await $.ajax({
                url: this.#urlApi + param,
                method: method,
                contentType: "application/json",
                data: data,
                xhrFields: {
                    responseType: 'blob', // Manipula a resposta como blob para PDF
                },
            });

            const blob = new Blob([response], { type: 'application/pdf' });
            const blobUrl = window.URL.createObjectURL(blob);

            if (openInNewWindow) {
                // Abre o PDF em uma nova janela ou aba
                window.open(blobUrl, '_blank');
            } else {
                // Baixa o PDF
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = fileName;
                link.click();
            }

            // Revoga a URL do Blob para liberar memória
            window.URL.revokeObjectURL(blobUrl);
        } catch (xhr) {
            this.#debugError(xhr);
            throw this.#handleError(xhr);
        }
    }

}
