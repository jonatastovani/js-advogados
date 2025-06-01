import { CommonFunctions } from "../commons/CommonFunctions";
import { BootstrapFunctionsHelper } from "./BootstrapFunctionsHelper";
import { PessoaNomeHelper } from "./PessoaNomeHelper";

export class ParticipacaoHelpers {

    /**
     * Gera um valor formatado de acordo com o tipo (percentual ou fixo).
     * @private
     * @param {string} valor - Valor original.
     * @param {string} valor_tipo - Tipo do valor ('porcentagem', 'valor_fixo').
     * @returns {string} Valor formatado.
     */
    static #formatarValor(valor, valor_tipo) {
        const valorFormatado = CommonFunctions.formatWithCurrencyCommasOrFraction(valor);
        return valor_tipo === 'porcentagem' ? `${valorFormatado}%` : `R$ ${valorFormatado}`;
    }

    /**
     * Gera um array com os participantes e integrantes formatados para exibição.
     * @param {Array} participantes - Lista de participantes.
     * @returns {Object} Objeto contendo os arrays 'arrayParticipantes' e 'arrayIntegrantes'.
     */
    static htmlRenderArrayParticipantesEIntegrantes(participantes) {
        const arrayParticipantes = [];
        const arrayIntegrantes = [];

        for (const participante of participantes) {
            const valor = this.#formatarValor(participante.valor, participante.valor_tipo);
            const participacao = participante.participacao_tipo.nome;
            let dadosParticipante = '';

            if (participante.participacao_registro_tipo_id === window.Enums.ParticipacaoRegistroTipoEnum.PERFIL) {
                const nome = PessoaNomeHelper.extrairNome(participante.referencia).nome_completo;
                dadosParticipante = `<b>${participante.referencia.perfil_tipo.nome}</b> - ${nome} > <b>${participacao}</b> - <b>${valor}</b>`;
                arrayParticipantes.push(dadosParticipante);
            } else if (participante.participacao_registro_tipo_id === window.Enums.ParticipacaoRegistroTipoEnum.GRUPO) {
                dadosParticipante = `<b>Grupo</b> - ${participante.nome_grupo}</b> > <b>${participacao}</b> - <b>${valor}</b>`;
                arrayParticipantes.push(dadosParticipante);
                for (const integrante of participante.integrantes) {
                    const nomeIntegrante = PessoaNomeHelper.extrairNome(integrante.referencia).nome_completo;
                    arrayIntegrantes.push(`<b>${participante.nome_grupo}</b> - ${nomeIntegrante}`);
                }
            }
        }

        if (!arrayParticipantes.length) arrayParticipantes.push('Não há nada para ver aqui');
        if (!arrayIntegrantes.length) arrayIntegrantes.push('Não há nada para ver aqui');
        return { arrayParticipantes, arrayIntegrantes };
    }

    /**
     * Gera botões com popovers para exibir participantes e integrantes.
     * @param {Array} participantes - Lista de participantes.
     * @param {Object} options - Opções para personalizar o botão e o título.
     * @returns {Object} Objeto contendo os botões 'btnParticipantes' e 'btnIntegrantes'.
     */
    static htmlRenderBtnsVerMaisParticipantesEIntegrantes(participantes, options = {}) {
        const arrays = this.htmlRenderArrayParticipantesEIntegrantes(participantes);
        const {
            titleParticipantes = 'Participante(s) do Lançamento',
            htmlBtnParticipante = 'Ver mais',
            htmlBtnIntegrantes = 'Ver mais',
        } = options;

        return {
            btnParticipantes: `
                <button type="button" class="btn btn-sm btn-outline-info border-0 text-nowrap"
                    data-bs-toggle="popover"
                    data-bs-title="${titleParticipantes}"
                    data-bs-html="true"
                    data-bs-content="${BootstrapFunctionsHelper.createScrollableContent(arrays.arrayParticipantes.join("<hr class='my-1'>"))}">
                    ${htmlBtnParticipante}
                </button>`,
            btnIntegrantes: `
                <button type="button" class="btn btn-sm btn-outline-info border-0 text-nowrap"
                    data-bs-toggle="popover"
                    data-bs-title="Integrantes de Grupos"
                    data-bs-html="true"
                    data-bs-content="${BootstrapFunctionsHelper.createScrollableContent(arrays.arrayIntegrantes.join("<hr class='my-1'>"))}">
                    ${htmlBtnIntegrantes}
                </button>`,
        };
    }

    /**
     * Gera um array com os participantes da movimentação de conta, formatados para exibição.
     * @param {Array} participantes - Lista de participantes.
     * @returns {Object} Objeto contendo o array 'arrayParticipantes'.
     */
    static htmlRenderArrayParticipantesMovimentacaoContaParticipante(participantes) {
        const arrayParticipantes = [];

        for (const participante of participantes) {
            const valor = `R$ ${CommonFunctions.formatWithCurrencyCommasOrFraction(participante.valor_participante)}`;
            const descricaoAutomatica = participante.descricao_automatica;
            const nomeParticipante = `${PessoaNomeHelper.extrairNome(participante.referencia).nome_completo} > <b>${descricaoAutomatica}</b> - ${valor}`;

            arrayParticipantes.push(nomeParticipante);
        }

        if (!arrayParticipantes.length) arrayParticipantes.push('Não há nada para ver aqui');
        return { arrayParticipantes };
    }

    /**
     * Gera botões com popovers para exibir participantes e integrantes.
     * @param {Array} participantes - Lista de participantes.
     * @param {Object} options - Opções para personalizar o botão e o título.
     * @returns {Object} Objeto contendo o botão 'btnParticipantes'.
     */
    static htmlRenderBtnVerMaisParticipantesMovimentacaoContaParticipante(participantes, options = {}) {
        const arrays = this.htmlRenderArrayParticipantesMovimentacaoContaParticipante(participantes);
        const {
            titleParticipantes = 'Participante(s)',
            htmlBtnParticipante = 'Ver mais',
        } = options;

        return {
            btnParticipantes: `
                <button type="button" class="btn btn-sm btn-outline-info border-0 text-nowrap"
                    data-bs-toggle="popover"
                    data-bs-title="${titleParticipantes}"
                    data-bs-html="true"
                    data-bs-content="${BootstrapFunctionsHelper.createScrollableContent(arrays.arrayParticipantes.join("<hr class='my-1'>"))}">
                    ${htmlBtnParticipante}
                </button>`,
        };
    }

}
