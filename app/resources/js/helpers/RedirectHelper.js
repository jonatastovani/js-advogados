import { CommonFunctions } from "../commons/CommonFunctions";
import { URLHelper } from "./URLHelper";
import { UUIDHelper } from "./UUIDHelper";

export class RedirectHelper {

    /**
     * Gera um UUID, armazena uma mensagem no localStorage e redireciona para a URL com o UUID como parâmetro.
     * A mensagem será exibida apenas na página de destino que contiver o UUID na URL.
     * 
     * @param {string} url - A URL para onde redirecionar o usuário.
     * @param {string} message - A mensagem a ser exibida na próxima página.
     * @param {string} type - O tipo da mensagem (success, error, info, etc.).
     */
    static async redirectWithUUIDMessage(url, message, type = 'info') {
        // Gera o UUID e armazena a mensagem no localStorage
        const uuid = await UUIDHelper.generateUUID();
        const messageData = { message, type };
        localStorage.setItem(uuid, JSON.stringify(messageData));

        // Redireciona para a URL com o UUID como parâmetro
        const redirectUrl = new URL(url, window.location.origin);
        redirectUrl.searchParams.set('uuid_message', uuid);
        window.location.href = redirectUrl.toString();
    }

    /**
     * Verifica se o parâmetro 'uuid_message' está presente na URL e, se sim, exibe a mensagem associada no localStorage.
     * Após a exibição, a mensagem é removida e o parâmetro 'uuid_message' é removido da URL.
     */
    static async checkUUIDMessage() {
        const uuid = URLHelper.getParameterURL('uuid_message');
        if (uuid) {
            // Verifica se o UUID existe no localStorage
            const messageData = localStorage.getItem(uuid);
            if (messageData) {
                const { message, type } = JSON.parse(messageData);
                RedirectHelper.showNotification(message, type);

                // Remove a mensagem do localStorage
                localStorage.removeItem(uuid);

                // Remove o parâmetro 'uuid_message' da URL
                URLHelper.removeURLParameter('uuid_message');
            }
        }
    }

    /**
     * Exibe a notificação (pode ser substituída pela sua função personalizada de exibir notificação).
     * 
     * @param {string} message - O texto da mensagem.
     * @param {string} type - O tipo da notificação (success, error, info, etc.).
     */
    static showNotification(message, type = 'info') {
        CommonFunctions.generateNotification(message, type);
    }

    /**
     * Gera e redireciona para uma URL com parâmetros opcionais, abrindo em uma nova aba.
     * Se nenhum parâmetro for enviado, redireciona apenas para a URL base.
     * 
     * @param {string} url - A URL base.
     * @param {Object} params - (Opcional) Objeto contendo os parâmetros para a query string.
     */
    static openURLWithParams(url, params = {}) {
        let queryString = '';

        if (params && typeof params === 'object') {
            // Flatten o objeto para gerar os parâmetros
            const flattenedParams = URLHelper.flattenObject(params);

            // Constrói a query string
            Object.keys(flattenedParams).forEach(function (key) {
                queryString += encodeURIComponent(key) + '=' + encodeURIComponent(flattenedParams[key]) + '&';
            });

            // Remove o último '&'
            queryString = queryString.slice(0, -1);
        }

        // Abre a URL em uma nova aba
        const fullURL = queryString ? `${url}?${queryString}` : url;
        window.open(fullURL, '_blank');
    }
}
