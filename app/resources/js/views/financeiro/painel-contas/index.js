import { commonFunctions } from "../../../commons/commonFunctions";
import { modalContaTenant } from "../../../components/tenant/modalContaTenant";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { RequestsHelpers } from "../../../helpers/RequestsHelpers";

class PagePainelContaIndex {

    #objConfigs = {
        url: {
            base: window.apiRoutes.baseContas,
            baseMovimentacaoContaFront: window.frontRoutes.baseMovimentacaoContaFront,
        },
        data: {},
        sufixo: 'PagePainelContaIndex',
    };

    constructor() {
        this.initEvents();
    }

    initEvents() {
        const self = this;
        self.#addEventosBotoes();
        self.#buscarDados();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#openModalConta${self.#objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalContaTenant();
                // objModal.setDataEnvModal = {
                //     attributes: {
                //         select: {
                //             quantity: 1,
                //             autoReturn: true,
                //         }
                //     }
                // }

                const response = await objModal.modalOpen();
                if (response.refresh) {
                    await self.#buscarDados();
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#atualizarDados${self.#objConfigs.sufixo}`).on('click', async function () {
            await self.#buscarDados();
            // commonFunctions.generateNotification('Dados atualizados com sucesso.', 'success');
        });

    }

    async #buscarDados() {
        const self = this;

        try {
            await commonFunctions.loadingModalDisplay();

            const response = await RequestsHelpers.get({ urlApi: `${self.#objConfigs.url.base}/painel-conta` });
            const form = $(`#formServico${self.#objConfigs.sufixo}`);

            if (response?.data) {
                $(`#divContas${self.#objConfigs.sufixo}`).html('');
                response.data.map(item => {
                    self.#inserirConta(item);
                })
            }

        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }

    #inserirConta(item) {

        const self = this;
        const nome = item.nome;
        const descricao = item.descricao;
        const banco = item.banco;
        const status = item.conta_status.nome
        const subtipo = item.conta_subtipo.nome
        let saldo = item?.ultima_movimentacao?.saldo_atualizado ? item.ultima_movimentacao.saldo_atualizado : 0;
        saldo = commonFunctions.formatNumberToCurrency(saldo);
        let dataHoraUltimaAtualizacao = item?.ultima_movimentacao?.created_at ? item.ultima_movimentacao.created_at : null;
        dataHoraUltimaAtualizacao = dataHoraUltimaAtualizacao ? DateTimeHelper.retornaDadosDataHora(item.ultima_movimentacao.created_at, 12) : '<span class="fst-italic">Nenhuma movimentação registrada</span>';

        const htmlConta = `
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">${nome}</h5>
                        <p class="card-text mb-0">Descrição: <span class="lblDescricao">${descricao}</span></p>
                        <p class="card-text mb-0">Banco: <span class="lblBanco">${banco}</span></p>
                        <div class="row row-cols-2 row-cols-sm-3">
                            <div class="col mt-2">
                                <div class="form-text">Saldo</div>
                                <label class="form-label spSaldo">${saldo}</label>
                            </div>
                            <div class="col mt-2">
                                <div class="form-text">Subtipo</div>
                                <label class="form-label lblSubtipo">${subtipo}</label>
                            </div>
                            <div class="col mt-2">
                                <div class="form-text">Status</div>
                                <label class="form-label lblStatus">${status}</label>
                            </div>
                        </div>
                        <a href="${self.#objConfigs.url.baseMovimentacaoContaFront}?conta_id=${item.id}" target="_blank" class="btn btn-outline-primary border-0">Ver movimentações</a>
                    </div>
                    <div class="card-footer text-body-secondary">
                        Última movimentação da conta: <span class="spUltimaMovimentacao">${dataHoraUltimaAtualizacao}</span>
                    </div>
                </div>
            </div>
        `;

        $(`#divContas${self.#objConfigs.sufixo}`).append(htmlConta);
    }
}

$(function () {
    new PagePainelContaIndex();
});