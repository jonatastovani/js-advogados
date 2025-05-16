export class PessoaNomeHelper {

    /**
     * Retorna o nome da pessoa baseado no tipo (física ou jurídica).
     * @param {Object} perfil - Estrutura contendo a chave 'pessoa' com os dados.
     * @param {Object} [options={}] - Parâmetros opcionais para personalizações futuras.
     * @returns {Object} Retorna um objeto com:
     *                   - 'nome_completo': Nome completo ou nome fantasia
     *                   - 'primeiro_nome': Apenas o primeiro nome (quando possível)
     */
    static extrairNome(perfil = {}, options = {}) {
        let nomeCompleto = '';

        if (!perfil?.pessoa || !perfil?.pessoa?.pessoa_dados_type) {
            return { nome_completo: '', primeiro_nome: '' };
        }

        const pessoa = perfil.pessoa;
        const tipo = pessoa.pessoa_dados_type;

        switch (tipo) {
            case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                nomeCompleto = pessoa?.pessoa_dados?.nome || '';
                break;

            case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                nomeCompleto = pessoa?.pessoa_dados?.nome_fantasia || '';
                break;

            default:
                nomeCompleto = 'Desconhecido';
                break;
        }

        const primeiroNome = nomeCompleto.split(' ')[0] || '';

        return {
            nome_completo: nomeCompleto,
            primeiro_nome: primeiroNome,
        };
    }

    /**
     * Retorna os nomes de várias pessoas a partir de um array de referências.
     * @param {Array} arrayPerfis - Lista de itens com chave 'perfil'.
     * @param {Object} [options={}] - Parâmetros opcionais para personalizações futuras.
     * @returns {Array} Array contendo os nomes completos e primeiros nomes.
     */
    static extrairNomes(arrayPerfis = [], options = {}) {
        return arrayPerfis
            .map(item => this.extrairNome(item.perfil, options))
            .filter(resultado => resultado.nome_completo);
    }
}
