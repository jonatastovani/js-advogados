import { UUIDHelper } from "./UUIDHelper";

export class ValidatorHelper {

    static validarItem(item, camposValidos) {
        const filtrados = {};
        const errors = [];

        camposValidos.forEach(campo => {
            const valor = item[campo.nome];

            // Se não existir no item, pula (pode ser required depois no backend)
            if (valor === undefined) return;

            const tipo = campo.validacao_front?.tipo || 'texto';
            const acao = campo.validacao_front?.acao_se_invalido || 'ignorar';
            const mensagem = campo.validacao_front?.mensagem || '';

            let valido = true;

            switch (tipo) {
                case 'uuid':
                    valido = UUIDHelper.isValidUUID(valor);
                    break;

                case 'numero':
                    valido = !isNaN(parseFloat(valor)) && isFinite(valor);
                    break;

                case 'data':
                    valido = !isNaN(Date.parse(valor));
                    break;

                case 'texto':
                    valido = typeof valor === 'string';
                    break;

                default:
                    valido = true;
            }

            if (valido) {
                filtrados[campo.nome] = valor;
            } else if (acao === 'alertar' && mensagem) {
                errors.push(mensagem);
            } // Se for "remover", apenas não adiciona o campo.
        });

        return {
            filtrados,
            errors,
        };
    }
}
