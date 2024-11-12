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
                    nomeParticipante = `<b>${participante.referencia.perfil_tipo.nome}</b> - ${participante.referencia.pessoa.pessoa_dados.nome}`;
                    break;

                case window.Enums.ParticipacaoRegistroTipoEnum.GRUPO:
                    nomeParticipante = `<b>Grupo</b> - ${participante.nome_grupo}</b>`;
                    for (const integrante of participante.integrantes) {
                        let nomeIntegrante = '';
                        switch (integrante.participacao_registro_tipo_id) {
                            case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:
                                nomeIntegrante = integrante.referencia.pessoa.pessoa_dados.nome;

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

}