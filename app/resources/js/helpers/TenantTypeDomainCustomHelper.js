import { commonFunctions } from "../commons/commonFunctions";
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
     * Obtém o nome da classe de componentes para identificação do domínio.
     * @returns {string|undefined} Nome da classe de identificação do domínio ou `undefined` se a instância não existir.
     */
    static get getDomainCustomIdentificationClassName() {
        const instance = this.getInstanceTenantTypeDomainCustom;
        return instance ? instance.getDomainCustomIdentificationClassName : undefined;
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

    //#region Métodos para a verificação de inserção de informações de domínio em tabelas

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

    //#endregion

    //#region Verificações de visualização de campos de domínio customizado

    /**
     * Verifica e manipula os elementos de domínio customizado na interface.
     * Se a configuração `applyBln` estiver ativa, garante que os elementos sejam visíveis e interativos.
     * Caso contrário, oculta os elementos.
     *
     * @param {Object} instanceParent - Instância do componente pai.
     * @param {Object} [options={}] - Opções adicionais.
     */
    static checkElementsDomainCustom(instanceParent, options = {}) {
        const instance = this.getInstanceTenantTypeDomainCustom;
        if (!instance) return;

        const selector = `#${instanceParent.getSufixo} .${instance.getDomainCustomIdentificationClassName}`;
        const elementsDomain = $(selector);

        if (instanceParent._objConfigs?.domainCustom?.applyBln) {
            if (!elementsDomain.length) {
                commonFunctions.generateNotification('Campos de domínio customizado não encontrados. Contate o suporte.', 'error');
            } else {
                // Executa a ação de exibição/ocultação e adiciona na fila para futuras execuções
                this.toggleElementsDomainCustom(instanceParent, options);
                instance.setEnqueueAction(() => this.toggleElementsDomainCustom(instanceParent, options));
            }
        } else {
            // Remove os elementos de seleção de domínio
            this.#hideElementsDomainCustom(elementsDomain);
        }
    }

    /**
     * Alterna a exibição dos elementos de domínio customizado com base na seleção atual do usuário.
     * Se `getSelectedValue` for diferente de `0`, oculta os elementos. Caso contrário, exibe-os.
     *
     * @param {Object} instanceParent - Instância do componente pai.
     * @param {Object} [options={}] - Opções adicionais.
     */
    static toggleElementsDomainCustom(instanceParent, options = {}) {
        const instance = this.getInstanceTenantTypeDomainCustom;
        if (!instance) return;

        const selector = `#${instanceParent.getSufixo} .${instance.getDomainCustomIdentificationClassName}`;
        const elementsDomain = $(selector);

        if (!elementsDomain.length) {
            commonFunctions.generateNotification('Elementos de domínio customizado não encontrados. Contate o suporte.', 'error');
            return;
        }

        if (instance.getSelectedValue != 0) {
            this.#hideElementsDomainCustom(instanceParent, elementsDomain);
        } else {
            this.#showElementsDomainCustom(instanceParent, elementsDomain);
        }
    }

    /**
     * Exibe os elementos de domínio customizado, garantindo que sejam visíveis e interativos.
     * Também adiciona o evento `change` no `<select>` correspondente.
     *
     * @param {Object} instanceParent - Instância do componente pai.
     * @param {JQuery} elementsDomain - Elementos a serem exibidos.
     * @private
     */
    static #showElementsDomainCustom(instanceParent, elementsDomain) {
        elementsDomain.addClass('d-inline-flex').css('display', 'inline-flex')
            .find('select').removeAttr('disabled');

        // Adiciona evento change ao select do domínio
        this.#addEventsSelectDomainCustom(instanceParent, elementsDomain);
    }

    /**
     * Oculta os elementos de domínio customizado, desativando a interação do usuário.
     * Também remove o evento `change` do `<select>` correspondente.
     *
     * @param {Object} instanceParent - Instância do componente pai.
     * @param {JQuery} elementsDomain - Elementos a serem ocultados.
     * @private
     */
    static #hideElementsDomainCustom(instanceParent, elementsDomain) {
        elementsDomain.removeClass('d-inline-flex').css('display', 'none')
            .find('select').attr('disabled', true);

        // Remove evento change ao ocultar os elementos
        this.#removeEventsSelectDomainCustom(instanceParent, elementsDomain);
    }

    /**
     * Adiciona o evento `change` ao `<select>` do domínio customizado, garantindo que
     * a seleção seja validada e salva corretamente.
     *
     * @param {Object} instanceParent - Instância do componente pai.
     * @param {JQuery} elementsDomain - Elementos que contêm o `<select>`.
     * @private
     */
    static #addEventsSelectDomainCustom(instanceParent, elementsDomain) {
        this.#startResetDomainCustomDomainIDVariable(instanceParent);

        const select = elementsDomain.find(`#domain_id${instanceParent.getSufixo}`);
        if (!select.length) return;

        select.off('change').on('change', () => {
            try {
                const instance = this.getInstanceTenantTypeDomainCustom;
                if (!instance) {
                    throw new Error('Instância de TenantTypeDomainCustomHelper não encontrada.');
                }

                const selected = Number(select.val());
                const verify = this.getDomainNameById(selected);

                if (!verify) {
                    throw new Error('Unidade não encontrada.');
                }

                instanceParent._objConfigs.domainCustom.domain_id = selected;
            } catch (error) {
                commonFunctions.generateNotification(error.message, 'error');
            }
        });
    }

    /**
     * Remove o evento `change` do `<select>` ao ocultar os elementos de domínio.
     *
     * @param {Object} instanceParent - Instância do componente pai.
     * @param {JQuery} elementsDomain - Elementos que contêm o `<select>`.
     * @private
     */
    static #removeEventsSelectDomainCustom(instanceParent, elementsDomain) {

        this.#startResetDomainCustomDomainIDVariable(instanceParent);
        const select = elementsDomain.find(`#domain_id${instanceParent.getSufixo}`);
        if (!select.length) return;

        select.off('change'); // Remove qualquer evento `change` associado ao select
    }

    /**
     * Inicializa a variável `domain_id` para undefined.
     *
     * @param {Object} instanceParent - Instância do componente pai.
     */
    static #startResetDomainCustomDomainIDVariable(instanceParent) {
        instanceParent._objConfigs.domainCustom.domain_id ??= undefined;
        instanceParent._objConfigs.domainCustom.domain_id = undefined;
    }

    //#endregion

    static #updateHTMLDisplayCustomDomain() {
        const instance = this.getInstanceTenantTypeDomainCustom;
        if (instance) {
            let name = '';
            let selectedValue = instance.getSelectedValue;
            if (!Number.isNaN(selectedValue) && selectedValue > 0) {
                const domainName = this.getDomainNameById(selectedValue);
                domainName ? name = ` • ${domainName.name}` : 'N/C';
            }

            const elementsUpdateNameDomainCustom = $(`.name-domain-custom`);
            let baseValue = elementsUpdateNameDomainCustom.data('base-value') ?? '';

            elementsUpdateNameDomainCustom.html(`${baseValue}${name}`);
        }
    }
}
