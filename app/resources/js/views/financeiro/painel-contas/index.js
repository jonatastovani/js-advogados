import { CommonFunctions } from "../../../commons/CommonFunctions";
import { ConnectAjax } from "../../../commons/ConnectAjax";
import { ModalAjustarSaldo } from "../../../components/financeiro/ModalAjustarSaldo";
import { ModalContaTenant } from "../../../components/tenant/ModalContaTenant";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import TenantTypeDomainCustomHelper from "../../../helpers/TenantTypeDomainCustomHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

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

        const custom = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;

        if (custom.getStatusBlnCustom) {
            custom.setEnqueueAction(self.#buscarDados.bind(self));
        }

    }

    #addEventosBotoes() {
        const self = this;

        $(`#openModalConta${self.#objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalContaTenant();
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    await self.#buscarDados();
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#atualizarDados${self.#objConfigs.sufixo}`).on('click', async function () {
            await self.#buscarDados();
            // CommonFunctions.generateNotification('Dados atualizados com sucesso.', 'success');
        });

    }

    async #buscarDados() {
        const self = this;

        try {
            await CommonFunctions.loadingModalDisplay();

            const objConn = new ConnectAjax(`${self.#objConfigs.url.base}/painel-conta`);
            const response = await objConn.getRequest();

            $(`#divContas${self.#objConfigs.sufixo}`).html('');
            if (response?.data) {
                response.data.map(item => {
                    self.#inserirConta(item);
                })
            }

        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await CommonFunctions.loadingModalDisplay(false);
        }
    }

    #inserirConta(item) {

        const self = this;
        const nome = item.nome;
        const descricao = item.descricao ? `<p class="card-text mb-0">Descrição: <span class="lblDescricao">${item.descricao}</span></p>` : ``;
        const banco = item.banco ? `<p class="card-text mb-0">Banco: <span class="lblBanco">${item.banco}</span></p>` : '';
        const status = item.conta_status.nome
        const subtipo = item?.conta_subtipo?.nome ?? '';
        let saldo = CommonFunctions.formatNumberToCurrency(item.saldo_total);

        let ultimasMovimentacoes = self.#htmlUltimasMovimentacoes(item);

        item.idCol = UUIDHelper.generateUUID();
        const htmlConta = `
            <div div id = "${item.idCol}" class="col" >
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center justify-content-between">
                            <span class="text-truncate">
                                ${nome}
                            </span>
                            <div>
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><button type="button" class="dropdown-item fs-6 btn-ajustar" title="Ajustar saldo da conta ${nome}">Ajustar saldo</button></li>
                                    </ul>
                                </div>
                            </div>
                        </h5>
                        ${descricao}
                        ${banco}
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
                    <ul class="list-group list-group-flush">
                        ${ultimasMovimentacoes}
                    </ul>
                </div>
            </div >
            `;

        $(`#divContas${self.#objConfigs.sufixo} `).append(htmlConta);

        self.#addEventosPagamento(item);
    }

    async #addEventosPagamento(item) {
        const self = this;

        $(`#${item.idCol} .btn-ajustar`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalAjustarSaldo();
                objModal.setDataEnvModal = {
                    idRegister: item.id,
                }
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    self.#buscarDados();
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });
    }

    #htmlUltimasMovimentacoes(item) {

        let htmlSubtotais = '';
        item.ultimas_movimentacoes.map(ultimaMovimentacao => {
            let dataHoraUltimaAtualizacao = DateTimeHelper.retornaDadosDataHora(ultimaMovimentacao.created_at, 12);
            let subtotal = CommonFunctions.formatNumberToCurrency(ultimaMovimentacao.saldo_atualizado);

            htmlSubtotais += `<li class="list-group-item">Unidade: <b>${ultimaMovimentacao.conta_domain.domain.name}</b> <br> Subtotal: <b>${subtotal}</b> | Última atualização: <b>${dataHoraUltimaAtualizacao}</b></li>`;
        })

        return htmlSubtotais;
    }
}

$(function () {
    new PagePainelContaIndex();
});