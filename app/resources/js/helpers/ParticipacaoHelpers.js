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
  * Gera arrays formatados em HTML com os participantes, integrantes e valores reais recebidos.
  * 
  * @param {Array} participantes - Lista de participantes com relacionamentos carregados.
  * @param {Object} options - Opções adicionais.
  * @param {Array} [options.movimentacaoContaParticipante] - Lista de valores reais recebidos por perfil.
  * @returns {{ arrayParticipantes: string[], arrayIntegrantes: string[], arrayParticipantesValorReal: string[] }}
  */
    static htmlRenderArrayParticipantesEIntegrantes(participantes, options = {}) {
        const arrayParticipantes = [];
        const arrayIntegrantes = [];
        const arrayParticipantesValorReal = [];
        const { movimentacaoContaParticipante = [] } = options;

        // Map para facilitar busca por referência + tipo
        const mapaParticipantesPorReferenciaTipo = new Map();

        for (const participante of participantes) {
            const key = `${participante.referencia_id ?? ''}::${participante.participacao_tipo_id}`;
            mapaParticipantesPorReferenciaTipo.set(key, participante);

            // Renderização padrão
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
                    const keyIntegrante = `${integrante.referencia_id}::${participante.participacao_tipo_id}`;
                    mapaParticipantesPorReferenciaTipo.set(keyIntegrante, integrante);
                }
            }
        }

        // Participantes com valor real recebido
        for (const mov of movimentacaoContaParticipante) {
            const key = `${mov.referencia_id}::${mov.participacao_tipo_id}`;
            const participante = mapaParticipantesPorReferenciaTipo.get(key);

            if (!participante) continue;

            const nome = PessoaNomeHelper.extrairNome(participante.referencia).nome_completo;
            const valor = `R$ ${CommonFunctions.formatWithCurrencyCommasOrFraction(mov.valor_participante)}`;
            const descricao = mov.descricao_automatica || 'Recebimento';

            arrayParticipantesValorReal.push(`<b>${nome}</b> > ${descricao} - <b>${valor}</b>`);
        }

        if (!arrayParticipantes.length) arrayParticipantes.push('Não há nada para ver aqui');
        if (!arrayIntegrantes.length) arrayIntegrantes.push('Não há nada para ver aqui');
        // if (!arrayParticipantesValorReal.length) arrayParticipantesValorReal.push('Não há valores recebidos para exibir');

        return {
            arrayParticipantes,
            arrayIntegrantes,
            arrayParticipantesValorReal,
        };
    }

    /**
     * Gera um array com os participantes e integrantes formatados para exibição.
     * @param {Array} participantes - Lista de participantes.
     * @param {Array} [options.movimentacaoContaParticipante] - Lista de valores reais recebidos por perfil.
     * @returns {{ arrayParticipantes: string[], arrayIntegrantes: string[], arrayParticipantesValorReal: string[] }} Arrays com dados renderizados.
  */
    static htmlRenderBtnsVerMaisParticipantesEIntegrantes(participantes, options = {}) {
        const arrays = this.htmlRenderArrayParticipantesEIntegrantes(participantes, options);
        const {
            titleParticipantes = 'Participante(s) do Lançamento',
            htmlBtnParticipante = 'Ver mais',
            htmlBtnIntegrantes = 'Ver mais',
        } = options;

        const renderArrayParticipantes = arrays.arrayParticipantes.join("<hr class='my-1'>");
        const renderArrayIntegrantes = arrays.arrayIntegrantes.join("<hr class='my-1'>");

        let renderArrayParticipantesValorReal = '';
        if (arrays.arrayParticipantesValorReal.length) {
            renderArrayParticipantesValorReal = `<hr class='border border-warning border-2 opacity-50'><p class='text-warning'>Participação Real</p>` + arrays.arrayParticipantesValorReal.join("<hr class='my-1'>");
        }

        return {
            btnParticipantes: `
                <button type="button" class="btn btn-sm btn-outline-info border-0 text-nowrap"
                    data-bs-toggle="popover"
                    data-bs-title="${titleParticipantes}"
                    data-bs-html="true"
                    data-bs-content="${BootstrapFunctionsHelper.createScrollableContent(`${renderArrayParticipantes}${renderArrayParticipantesValorReal}`)}">
                    ${htmlBtnParticipante}
                </button>`,
            btnIntegrantes: `
                <button type="button" class="btn btn-sm btn-outline-info border-0 text-nowrap"
                    data-bs-toggle="popover"
                    data-bs-title="Integrantes de Grupos"
                    data-bs-html="true"
                    data-bs-content="${BootstrapFunctionsHelper.createScrollableContent(renderArrayIntegrantes)}">
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
