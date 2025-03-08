import instanceManager from "../commons/instanceManager";
import { TenantTypeDomainCustom } from "../commons/TenantTypeDomainCustom";

export default class TenantTypeDomainCustomHelper {

    /**
     * Obtém a instância da classe TenantTypeDomainCustom.
     * @returns {TenantTypeDomainCustom | false} Retorna a instância ou `false` se não estiver disponível.
     */
    static get getInstanceTenantTypeDomainCustom() {
        return instanceManager.instanceVerification(this.getNameInstanceCustomDomain);
    }

    /**
     * Nome da instância gerenciada no instanceManager.
     * @returns {string}
     */
    static get getNameInstanceCustomDomain() {
        return 'TenantTypeDomainCustom';
    }

    /**
     * Seletor da classe da `th` que será adicionada/removida dinamicamente.
     * @returns {string}
     */
    static get getNameClassTbodyThCustomDomain() {
        return '.th-domain-custom';
    }

    /**
     * Obtém as opções de domínios disponíveis.
     * @returns {Array|undefined} Lista de domínios ou `undefined` se a instância não existir.
     */
    static get getDomainsOptions() {
        const instance = this.getInstanceTenantTypeDomainCustom;
        return instance ? instance.getArrayDomains : undefined;
    }

    /**
     * Obtém os dados do domínio atualmente selecionado.
     * @returns {Object|undefined} Dados do domínio ou `undefined` se a instância não existir.
     */
    static get getDataCurrentDomain() {
        const instance = this.getInstanceTenantTypeDomainCustom;
        return instance ? instance.getDataCurrentDomain : undefined;
    }

    /**
 * Insere a célula `<td>` do domínio na primeira posição da linha (`<tr>`) correspondente ao `idTr`.
 * Se nenhum domínio for encontrado ou `idTr` não existir, retorna `false`.
 *
 * @param {Object} item - Objeto contendo `idTr` e `domain_id`.
 * @param {Object} [options={}] - Opções adicionais.
 * @param {string} [options.appendClass=''] - Classe CSS adicional para estilização.
 * @returns {boolean} `true` se inseriu a célula, `false` se não foi necessário.
 */
    static checkInsertTdDomainInTable(item, options = {}) {
        const instance = this.getInstanceTenantTypeDomainCustom;
        if (!instance || !item?.idTr) return false; // Garante que há instância e `idTr`

        const { appendClass = '' } = options;

        if (instance.getSelectedValue == 0) {

            if (item?.domain_id) {

                const domainName = this.getDomainNameById(item.domain_id);
                let tdContent = 'N/C'; // Padrão caso não encontre um domínio válido

                if (domainName) {
                    tdContent = domainName; // Se encontrar um nome válido, atualiza o conteúdo
                }

                const tr = $(`#${item.idTr}`);
                if (!tr) return false; // Se a `tr` não existe, não faz nada

                // Verifica se já existe um `<td>` do domínio para evitar inserção duplicada
                if (!tr.find('.td-domain-custom').length) {
                    const td = document.createElement('td');
                    td.className = `td-domain-custom ${appendClass}`;
                    td.textContent = tdContent;
                    tr.prepend(td); // Insere no início da `<tr>`
                }

                this.checkThThead(true, options);
                return true;
            }
        }

        this.checkThThead(false, options);
        return false;
    }

    /**
     * Busca o nome do domínio pelo `domain_id`.
     * 
     * @param {number|string} domainId - ID do domínio.
     * @returns {string|null} Nome do domínio ou `null` se não encontrado.
     */
    static getDomainNameById(domainId) {
        const domains = this.getDomainsOptions;
        if (!domains) return null;

        const domain = domains.find(domain => domain.id == domainId);
        return domain ? domain.name : null;
    }

    /**
     * Adiciona ou remove a `<th>` no `thead` da tabela, dependendo da necessidade.
     * 
     * @param {boolean} statusShow - `true` para exibir a `<th>`, `false` para removê-la.
     * @param {Object} [options={}] - Opções adicionais.
     * @param {string} [options.tbody] - Seletor do `tbody` da tabela.
     */
    static checkThThead(statusShow, options = {}) {
        if (!options.tbody) return;

        const table = $(options.tbody).closest('table');
        const thead = table.find('thead tr');

        let th = thead.find(this.getNameClassTbodyThCustomDomain);

        if (statusShow) {
            // Se a `th` já existe, retorna
            if (th.length) return;

            // Se não existe, insere na primeira posição do `thead`
            thead.prepend(`<th class="${this.getNameClassTbodyThCustomDomain.replace('.', '')}">Unidade</th>`);
        } else {
            th.remove();
        }
    }

    /**
     * Remove a coluna `<th>` do domínio se não houver registros na consulta
     * e se o domínio selecionado for diferente de `0`.
     *
     * @param {Object} [options={}] - Opções adicionais.
     * @param {string} [options.tbody] - Seletor do `<tbody>` da tabela.
     */
    static checkThIfNoRecords(options = {}) {
        const instance = this.getInstanceTenantTypeDomainCustom;
        if (!instance || instance.getSelectedValue != 0) {
            this.checkThThead(false, options);
        } else {
            this.checkThThead(true, options);
        }
    }
}
