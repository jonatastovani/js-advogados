import { CommonFunctions } from "../commons/CommonFunctions";

export class functionsQueryCriteria {

    parentId;
    parentInstance;

    constructor(parentInstance, parentId) {
        this.parentInstance = parentInstance;
        this.parentId = parentId;
        this.#criteriaEventsDefault();
    }

    #criteriaEventsDefault() {
        const self = this;
        const parent = $(self.parentId);

        const colCriterios = parent.find('.containerCriterios').find('.colCriterio');
        const dataCriterio = {
            valor_atribuido: false,
            select_tratamento_valor_padrao: '',
            select_forma_busca_padrao: '',
        }
        for (let i = 0; i < colCriterios.length; i++) {
            const criterio = $(colCriterios[i]);
            const ckb = criterio.find('.ckbCriterio');
            const campoCriterio = criterio.find('.campoCriterio');
            ckb.on('change', function () {
                if (ckb.is(':checked')) {
                    campoCriterio.attr('disabled', false);
                } else {
                    campoCriterio.attr('disabled', true);
                }
            });
            if (!dataCriterio.valor_atribuido) {
                dataCriterio.select_tratamento_valor_padrao = criterio.find('.selectTratamentoCriterio').val();
                dataCriterio.select_forma_busca_padrao = criterio.find('.selectFormaBuscaCriterio').val();
                dataCriterio.valor_atribuido = true;
            }
        };

        parent.find('.btnDesmarcarCriterios').on('click', function () {
            for (let i = 0; i < colCriterios.length; i++) {
                const criterio = $(colCriterios[i]);
                const ckb = criterio.find('.ckbCriterio');
                ckb.prop('checked', false).trigger('change');
            };
        });

        parent.find('.btnLimparCriterios').on('click', function () {
            for (let i = 0; i < colCriterios.length; i++) {
                const criterio = $(colCriterios[i]);
                const ckb = criterio.find('.ckbCriterio');
                ckb.prop('checked', false).trigger('change');
                criterio.find('.textoCriterio').val('');
                criterio.find('.selectTratamentoCriterio').val(dataCriterio.select_tratamento_valor_padrao);
                criterio.find('.selectFormaBuscaCriterio').val(dataCriterio.select_forma_busca_padrao);
            };
        });
    }

    async generateQueryFiltersCriteria() {

        const self = this;
        const parent = $(self.parentId);
        let arrayMensagens = [];

        let data = {
            criterios: [],
            ordenacao: [{
                campo: 'nome',
                direcao: 'asc',
            },],
            page: 1
        };

        const containerCriterios = parent.find('.containerCriterios');
        const colCriterios = containerCriterios.find('.colCriterio');

        for (let i = 0; i < colCriterios.length; i++) {
            const criterio = $(colCriterios[i]);
            const ckb = criterio.find('.ckbCriterio');
            if (ckb.is(':checked')) {
                const get = CommonFunctions.getInputsValues(criterio);
                const texto = get.texto;
                if (texto == '') {
                    arrayMensagens.push(`O critério do campo <b>${get.nomeCriterio}</b> deve ser preenchido.`);
                } else {
                    data.criterios.push({
                        campo: get.campo,
                        texto: texto,
                        texto_tratamento: {
                            tratamento: get.tratamento,
                        },
                        parametros_like: this.parentInstance._returnQueryParameters(get.formaBusca),
                    });
                }
            }
        }

        if (!data.criterios.length) {
            arrayMensagens.push(`Utilize pelo menos um critério de busca.`);
        }

        if (arrayMensagens.length > 0) {
            CommonFunctions.generateNotification("Não foi possivel realizar a busca. Verifique as seguintes recomendações:", 'info', { itemsArray: arrayMensagens });
            return false;
        }

        return data;
    }

}
