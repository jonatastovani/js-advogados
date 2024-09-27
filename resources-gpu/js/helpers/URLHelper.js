
export class URLHelper {

    /**
     * Obtém o valor de um parâmetro da URL.
     * 
     * @param {string} strParam - O nome do parâmetro a ser obtido.
     * @param {string|null} [strUrl=null] - A URL da qual o parâmetro será extraído (padrão: URL atual).
     * @returns {string|null} - O valor do parâmetro ou null se não estiver presente.
     */
    static getParameterURL(strParam, strUrl = null) {
        const url = new URL(strUrl ?? window.location.href);
        const params = new URLSearchParams(url.search);
        return params.get(strParam) || null;
    }

    /**
     * Remove um parâmetro da URL sem recarregar a página.
     * 
     * @param {string} param - O nome do parâmetro a ser removido.
     */
    static removeURLParameter(param) {
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);

        if (params.has(param)) {
            params.delete(param);
            const newUrl = `${url.origin}${url.pathname}?${params.toString()}`;
            window.history.replaceState({}, document.title, newUrl);
        }
    }

    /**
     * Função para capturar um parâmetro específico da URL ou o último por padrão.
     * 
     * @param {number|null} index - O índice do segmento da URL que você deseja capturar. Por padrão, captura o último segmento.
     * @returns {string|null} - O valor do segmento da URL ou null se o segmento não for encontrado.
     */
    static getURLSegment(index = null) {
        const url = window.location.pathname;
        const segments = url.split('/').filter(segment => segment); // Remove segmentos vazios

        if (index === null) {
            // Retorna o último segmento por padrão
            return segments.length > 0 ? segments[segments.length - 1] : null;
        }

        // Retorna o segmento no índice fornecido, ou null se não existir
        return segments[index] || null;
    }
}