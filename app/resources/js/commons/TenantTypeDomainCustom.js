import { QueueManager } from "../utils/QueueManager";
import { commonFunctions } from "./commonFunctions";
import instanceManager from "./instanceManager";

/**
 * Classe responsável por gerenciar configurações personalizadas de domínio para tipos de tenants.
 * Permite armazenar e recuperar seleções de domínio no `localStorage`, além de gerenciar
 * uma fila de execução para alterações no domínio.
 */
export class TenantTypeDomainCustom {

    /**
     * Objeto de configuração interna para gerenciamento de domínio.
     * @private
     * @type {Object}
     */
    _objConfigs = {
        domainCustom: {
            nameAttributeKey: undefined,
            headerAttributeKey: undefined,
            arrayDomains: [],
        },
        selectedValueDefaultAllDomains: 0, // Valor padrão para seleção de "Todos os domínios"
    };

    /**
     * Flag que indica se a funcionalidade de domínio personalizado está ativa.
     * @private
     * @type {boolean}
     */
    _blnDomainCustom = false;

    /**
     * Construtor da classe `TenantTypeDomainCustom`.
     * Inicializa a fila de execução para controle de alterações no domínio.
     */
    constructor() {
        this.setSelectedValue = 0;

        /** 
         * Gerenciador de fila para ações relacionadas à troca de domínio.
         * @private
         * @type {QueueManager} 
         */
        this._queueToogleDomainCustom = instanceManager.setInstance('QueueToogleDomainCustom', new QueueManager());
        this._queueToogleDomainCustom.setDeduplicationMode = "last"; // Garante que a última ação seja a mantida
        this._queueToogleDomainCustom.setPreserveQueue = true; // Mantém os itens na fila após execução
    }

    /**
     * Define os valores personalizados de domínio.
     * @param {Object} domainCustom - Configuração do domínio customizado.
     */
    set setDomainCustom(domainCustom) {
        this._blnDomainCustom = true;
        this._objConfigs.domainCustom = JSON.parse(JSON.stringify(domainCustom)); // Clona para evitar referência direta
    }

    /**
     * Obtém o status da ativação do domínio personalizado.
     * @returns {boolean} `true` se estiver ativo, `false` caso contrário.
     */
    get getStatusBlnCustom() {
        return this._blnDomainCustom;
    }

    /**
     * Define o valor do domínio selecionado e o armazena no `localStorage`.
     * @param {number|string} selectedValue - Valor do domínio selecionado.
     */
    set setSelectedValue(selectedValue) {
        commonFunctions.setItemLocalStorage(this.getNameAttributeKey, selectedValue);
    }

    /**
     * Obtém o valor do domínio selecionado no `localStorage`. Se não houver, define um valor padrão.
     * @returns {number} Valor numérico do domínio selecionado.
     */
    get getSelectedValue() {
        let selectedValue = commonFunctions.getItemLocalStorage(this.getNameAttributeKey);
        if (selectedValue == null) {
            selectedValue = this._objConfigs.selectedValueDefaultAllDomains;
            this.setSelectedValue = selectedValue;
        }
        return Number(selectedValue);
    }

    /**
     * Obtém a chave do atributo de nome do domínio.
     * @returns {string|undefined} Nome do atributo do domínio, se definido.
     */
    get getNameAttributeKey() {
        return this._objConfigs.domainCustom.nameAttributeKey;
    }

    /**
     * Obtém a chave do atributo de cabeçalho do domínio.
     * @returns {string|undefined} Chave do cabeçalho do domínio, se definido.
     */
    get getHeaderAttributeKey() {
        return this._objConfigs.domainCustom.headerAttributeKey;
    }

    get getArrayDomains() {
        return this._objConfigs.domainCustom.arrayDomains;
    }

    get getDataCurrentDomain() {
        const selectedValue = this.getSelectedValue;
        const arrayDomains = this.getArrayDomains;
        return arrayDomains.find(domain => domain.id == selectedValue);
    }

    //#region Funções relacionadas à fila de execução quando houver alteração do domínio

    /**
     * Define que a fila de execução está pronta para processar as ações enfileiradas.
     */
    setEnqueueReady() {
        this._queueToogleDomainCustom.setReady();
    }

    /**
     * Define que a fila de execução não está pronta, pausando as ações enfileiradas.
     */
    setEnqueueNoReady() {
        this._queueToogleDomainCustom.setNoReady();
    }

    /**
     * Adiciona uma nova ação à fila de execução de alterações de domínio.
     * @param {Function} action - Função a ser executada quando a fila for processada.
     */
    setEnqueueAction(action) {
        this._queueToogleDomainCustom.enqueue(action);
    }

    //#endregion
}
