import { CommonFunctions } from "../commons/CommonFunctions";
import InstanceManager from "../commons/InstanceManager";
import { TenantTypeDomainCustom } from "../commons/TenantTypeDomainCustom";
import { QueueManager } from "../utils/QueueManager";

export default class TenantTypeDomainCustomHelper {

    /**
     * Obtém a instância da classe TenantTypeDomainCustom.
     * @returns {TenantTypeDomainCustom | false} Retorna a instância ou `false` se não estiver disponível.
     */
    static get getInstanceTenantTypeDomainCustom() {
        return InstanceManager.instanceVerification(this.getNameInstanceCustomDomain);
    }

    /**
     * Nome da instância gerenciada no InstanceManager.
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
     * Verifica e aplica a configuração de bloqueio de alterações de domínio customizado.
     * Atualiza o ID do domínio e o nome do domínio na interface, caso as condições sejam atendidas.
     * Lança um erro se a instância ou informações necessárias não forem encontradas.
     *
     * @param {Object} instanceParent - Instância do componente pai.
     * @param {Object} data - Dados contendo o `domain_id` necessário.
     * @param {Object} [options={}] - Opções adicionais.
     * @throws {Error} Se a instância ou informações de domínio não forem encontradas.
     */
    static checkDomainCustomBlockedChangesDomainId(instanceParent, data, options = {}) {

        const instance = this.getInstanceTenantTypeDomainCustom;
        if (!instance) return;

        if (instanceParent._objConfigs?.domainCustom?.applyBln) {

            if (!data.domain_id) {
                throw new Error(`Informação de unidade de domínio não encontrada. Caso erro persista, contate o suporte.`);
            }

            const domainName = this.getDomainNameById(data.domain_id);
            $(`#${instanceParent.getSufixo} .blocked-changes-domain`).html(` • ${domainName}`);

            instanceParent.setForcedDomainIdBlockedChanges = data.domain_id;
        }
    }

    /**
     * Verifica se o domínio customizado tem um ID forçado e o retorna.
     * Se a configuração `applyBln` estiver ativa e o domínio selecionado for diferente de `0`,
     * retorna o ID do domínio forçado. Caso contrário, retorna `false`.
     *
     * @param {Object} instanceParent - Instância do componente pai.
     * @param {Object} [options={}] - Opções adicionais.
     * @returns {number|false} ID do domínio forçado ou `false` se não houver um domínio forçado.
     * @throws {Error} Se a instância ou informações de domínio não forem encontradas.
     */
    static checkDomainCustomForcedDomainId(instanceParent, options = {}) {

        const instance = this.getInstanceTenantTypeDomainCustom;
        if (!instance) return false;

        if (instanceParent._objConfigs?.domainCustom?.applyBln) {

            if (!instanceParent._objConfigs?.domainCustom?.blocked_changes
                && instance.getSelectedValue != 0) return false;

            const domainId = instanceParent._objConfigs?.domainCustom?.domain_id;
            if (!domainId) {
                throw new Error(`Informação de unidade de domínio não encontrada. Caso erro persista, contate o suporte.`);
            }
            return domainId;
        }
        return false;
    }

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

            // Se for herdado, então não não aplica a verificação e atribuição e eventos na seleção
            if (instanceParent._objConfigs?.domainCustom?.inheritedBln) return;

            if (!elementsDomain.length) {
                CommonFunctions.generateNotification('Campos de domínio customizado não encontrados. Contate o suporte.', 'error');
            } else {
                // Se não estiver bloqueado a definição customizada de domínio, então se aplica os eventos;
                if (!instanceParent._objConfigs.domainCustom?.blocked_changes) {
                    // Executa a ação de exibição/ocultação e adiciona na fila para futuras execuções
                    this.toggleElementsDomainCustom(instanceParent, options);
                    instance.setEnqueueAction(() => this.toggleElementsDomainCustom(instanceParent, options));
                } else {
                    // Remove os elementos de seleção de domínio
                    this.#hideElementsDomainCustom(instanceParent, elementsDomain, options);
                }
            }
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
            CommonFunctions.generateNotification('Elementos de domínio customizado não encontrados. Contate o suporte.', 'error');
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
    static #showElementsDomainCustom(instanceParent, elementsDomain, options = {}) {
        elementsDomain.addClass('d-inline-flex').css('display', 'inline-flex')
            .find('select').removeAttr('disabled');

        // Adiciona evento change ao select do domínio
        this.#addEventsSelectDomainCustom(instanceParent, elementsDomain, options);
    }

    /**
     * Oculta os elementos de domínio customizado, desativando a interação do usuário.
     * Também remove o evento `change` do `<select>` correspondente.
     *
     * @param {Object} instanceParent - Instância do componente pai.
     * @param {JQuery} elementsDomain - Elementos a serem ocultados.
     * @private
     */
    static #hideElementsDomainCustom(instanceParent, elementsDomain, options = {}) {
        elementsDomain.removeClass('d-inline-flex').css('display', 'none')
            .find('select').attr('disabled', true);

        // Remove evento change ao ocultar os elementos
        this.#removeEventsSelectDomainCustom(instanceParent, elementsDomain, options);
    }

    /**
     * Adiciona o evento `change` ao `<select>` do domínio customizado, garantindo que
     * a seleção seja validada e salva corretamente.
     *
     * @param {Object} instanceParent - Instância do componente pai.
     * @param {JQuery} elementsDomain - Elementos que contêm o `<select>`.
     * @private
     */
    static #addEventsSelectDomainCustom(instanceParent, elementsDomain, options = {}) {
        this.#startResetDomainCustomDomainIDVariable(instanceParent, options);

        const select = elementsDomain.find(`#domain_id${instanceParent.getSufixo}`);
        if (!select.length) return;

        /** @type {QueueManager} */
        instanceParent._queueSelectDomainCustom = new QueueManager();
        instanceParent._queueSelectDomainCustom.setDeduplicationMode = 'last';
        instanceParent._queueSelectDomainCustom.setPreserveQueue = true;

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
                instanceParent._queueSelectDomainCustom.setReady();
            } catch (error) {
                CommonFunctions.generateNotification(error.message, 'error');
            }
        });
        select.trigger('change');
        // instanceParent._queueSelectDomainCustom.setReady();
    }

    /**
     * Remove o evento `change` do `<select>` ao ocultar os elementos de domínio.
     *
     * @param {Object} instanceParent - Instância do componente pai.
     * @param {JQuery} elementsDomain - Elementos que contêm o `<select>`.
     * @param {Object} [options={}] - Opções adicionais..
     * @param {boolean} [options.stop_variable] - Indica se a variável `domain_id` deve ser zerada.
     * @private
     */
    static #removeEventsSelectDomainCustom(instanceParent, elementsDomain, options = {}) {

        if (!options?.stop_variable) {
            this.#startResetDomainCustomDomainIDVariable(instanceParent);
        }

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

        if (instanceParent._objConfigs.domainCustom.blocked_changes) {
            throw new Error("A definição de domínio customizado foi bloqueada. Se o problema persistir, contate o suporte.");
        }

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
