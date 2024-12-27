import { commonFunctions } from "../commons/commonFunctions";

export class ServicoParticipacaoHelpers {

    static htmlRenderParticipantesEIntegrantes(participantes) {
        const arrayParticipantes = [];
        const arrayIntegrantes = [];

        for (const participante of participantes) {
            let nomeParticipante = '';
            let valor = commonFunctions.formatWithCurrencyCommasOrFraction(participante.valor);
            let participacao = participante.participacao_tipo.nome;

            switch (participante.valor_tipo) {
                case 'porcentagem':
                    valor = `${valor}%`;
                    break;
                case 'valor_fixo':
                    valor = `R$ ${valor}`;
                    break;
            }

            switch (participante.participacao_registro_tipo_id) {
                case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:

                    switch (participante.referencia.pessoa.pessoa_dados_type) {
                        case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                            nomeParticipante = `<b>${participante.referencia.perfil_tipo.nome}</b> - ${participante.referencia.pessoa.pessoa_dados.nome}`;
                            break;
                        case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                            nomeParticipante = `<b>${participante.referencia.perfil_tipo.nome}</b> - ${participante.referencia.pessoa.pessoa_dados.nome_fantasia}`;
                            break;

                        default:
                            commonFunctions.generateNotification(`O tipo de pessoa <b>${participante.referencia.pessoa.pessoa_dados_type}</b> ainda não foi implementado.`, 'error');
                            console.error('O tipo de pessoa ainda nao foi implementado.', participante);
                            return false;
                    }
                    break;

                case window.Enums.ParticipacaoRegistroTipoEnum.GRUPO:
                    nomeParticipante = `<b>Grupo</b> - ${participante.nome_grupo}</b>`;
                    for (const integrante of participante.integrantes) {
                        let nomeIntegrante = '';
                        switch (integrante.participacao_registro_tipo_id) {
                            case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:

                                switch (integrante.referencia.pessoa.pessoa_dados_type) {
                                    case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                                        nomeIntegrante = integrante.referencia.pessoa.pessoa_dados.nome;
                                        break;
                                    case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                                        nomeIntegrante = integrante.referencia.pessoa.pessoa_dados.nome_fantasia;
                                        break;

                                    default:
                                        commonFunctions.generateNotification(`O tipo de pessoa <b>${integrante.referencia.pessoa.pessoa_dados_type}</b> ainda não foi implementado.`, 'error');
                                        console.error('O tipo de pessoa ainda nao foi implementado.', integrante);
                                        return false;
                                }
                                break;
                        }

                        arrayIntegrantes.push(`<b>${participante.nome_grupo}</b> - ${nomeIntegrante}`);
                    }
                    break;
            }
            nomeParticipante += ` > <b>${participacao}</b> - <b>${valor}</b>`;

            arrayParticipantes.push(`${nomeParticipante}`);
        }

        if (!arrayParticipantes.length) arrayParticipantes.push('Não há nada para ver aqui');
        if (!arrayIntegrantes.length) arrayIntegrantes.push('Não há nada para ver aqui');
        return { arrayParticipantes: arrayParticipantes, arrayIntegrantes: arrayIntegrantes };
    }

    static htmlRenderParticipantesMovimentacaoContaParticipante(participantes) {
        const arrayParticipantes = [];

        for (const participante of participantes) {
            let nomeParticipante = '';
            let valor = `R$ ${commonFunctions.formatWithCurrencyCommasOrFraction(participante.valor_participante)}`;
            let descricao_automatica = participante.descricao_automatica;

            switch (participante.participacao_registro_tipo_id) {
                case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:

                    switch (participante.referencia.pessoa.pessoa_dados_type) {
                        case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                            nomeParticipante = `<b>${participante.referencia.perfil_tipo.nome}</b> - ${participante.referencia.pessoa.pessoa_dados.nome}`;
                            break;
                        case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                            nomeParticipante = `<b>${participante.referencia.perfil_tipo.nome}</b> - ${participante.referencia.pessoa.pessoa_dados.nome_fantasia}`;
                            break;

                        default:
                            commonFunctions.generateNotification(`O tipo de pessoa <b>${participante.referencia.pessoa.pessoa_dados_type}</b> ainda não foi implementado.`, 'error');
                            console.error('O tipo de pessoa ainda nao foi implementado.', participante);
                            return false;
                    }
                    break;
            }
            nomeParticipante += ` > <b>${descricao_automatica}</b> - ${valor}`;

            arrayParticipantes.push(`${nomeParticipante}`);
        }

        if (!arrayParticipantes.length) arrayParticipantes.push('Não há nada para ver aqui');
        return { arrayParticipantes: arrayParticipantes };
    }

}