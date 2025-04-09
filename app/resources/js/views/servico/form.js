import { CommonFunctions } from "../../commons/CommonFunctions";
import { ConnectAjax } from "../../commons/ConnectAjax";
import { EnumAction } from "../../commons/EnumAction";
import { TemplateForm } from "../../commons/templates/TemplateForm";
import { ModalMessage } from "../../components/comum/ModalMessage";
import { ModalParticipacao } from "../../components/comum/ModalParticipacao";
import { ModalLancamentoServicoMovimentar } from "../../components/financeiro/ModalLancamentoServicoMovimentar";
import { ModalPessoa } from "../../components/pessoas/ModalPessoa";
import { ModalLancamentoReagendar } from "../../components/servico/ModalLancamentoReagendar";
import { ModalSelecionarPagamentoTipo } from "../../components/servico/ModalSelecionarPagamentoTipo";
import { ModalServicoPagamento } from "../../components/servico/ModalServicoPagamento";
import { ModalAnotacaoLembreteTenant } from "../../components/tenant/ModalAnotacaoLembreteTenant";
import { ModalAreaJuridicaTenant } from "../../components/tenant/ModalAreaJuridicaTenant";
import { ModalDocumentoModeloTenant } from "../../components/tenant/ModalDocumentoModeloTenant";
import { BootstrapFunctionsHelper } from "../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { ParticipacaoHelpers } from "../../helpers/ParticipacaoHelpers";
import SimpleBarHelper from "../../helpers/SimpleBarHelper";
import TenantTypeDomainCustomHelper from "../../helpers/TenantTypeDomainCustomHelper";
import { URLHelper } from "../../helpers/URLHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";
import { ParticipacaoModule } from "../../modules/ParticipacaoModule";
import { QuillEditorModule } from "../../modules/QuillEditorModule";
import { QueueManager } from "../../utils/QueueManager";

class PageServicoForm extends TemplateForm {

    #objConfigs = {
        url: {
            base: window.apiRoutes.baseServico,
            baseAnotacao: undefined,
            baseCliente: undefined,
            baseDocumento: undefined,
            basePagamentos: undefined,
            baseParticipacao: undefined,
            baseValores: undefined,
            baseAreaJuridicaTenant: window.apiRoutes.baseAreaJuridicaTenant,
            baseParticipacaoPreset: window.apiRoutes.baseParticipacaoPreset,
            baseParticipacaoTipo: window.apiRoutes.baseParticipacaoTipoTenant,
            baseMovimentacaoContaLancamentoServico: window.apiRoutes.baseMovimentacaoContaLancamentoServico,
            baseLancamento: window.apiRoutes.baseLancamento,
        },
        data: {
            porcentagemOcupada: 0,
            participantesNaTela: [],
            clientesNaTela: [],
            participacao_tipo_tenant: {
            },
            configAcoes: {
                AGUARDANDO_PAGAMENTO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                    cor: 'text-bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                AGUARDANDO_PAGAMENTO: {
                    id: window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                    cor: null,
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                LIQUIDADO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                    cor: 'text-success bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                LIQUIDADO: {
                    id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                    cor: 'text-success',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                LIQUIDADO_PARCIALMENTE_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                    cor: 'text-success-emphasis bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                LIQUIDADO_PARCIALMENTE: {
                    id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                    cor: 'text-success-emphasis',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                REAGENDADO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                    cor: 'fst-italic text-bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                REAGENDADO: {
                    id: window.Enums.LancamentoStatusTipoEnum.REAGENDADO,
                    cor: 'fst-italic text-info-emphasis text-decoration-line-through',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                CANCELADO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                    cor: 'fst-italic text-secondary text-decoration-line-through bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                CANCELADO: {
                    id: window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                    cor: 'fst-italic text-secondary-emphasis text-decoration-line-through',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                INADIMPLENTE_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                    cor: 'text-danger bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                INADIMPLENTE: {
                    id: window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                    cor: 'text-danger-emphasis',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                LIQUIDADO_MIGRACAO_SISTEMA: {
                    id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                    cor: null,
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                EM_ATRASO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                    cor: 'text-danger bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                EM_ATRASO: {
                    id: window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    cor: 'text-danger-emphasis',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                    ]
                },
                PAGAMENTO_CANCELADO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.PAGAMENTO_CANCELADO_EM_ANALISE,
                    cor: 'fst-italic text-danger-emphasis text-decoration-line-through',
                },
                PAGAMENTO_CANCELADO: {
                    id: window.Enums.LancamentoStatusTipoEnum.PAGAMENTO_CANCELADO,
                    cor: 'fst-italic text-danger-emphasis text-decoration-line-through',
                },
            },
        },
        dados_tenant: undefined,
        participacao: {
            // perfis_busca: window.Statics.PerfisPermitidoParticipacaoRessarcimento,
            participacao_tipo_tenant: {
                configuracao_tipo: window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_SERVICO,
            },
        },
        domainCustom: {
            applyBln: true,
        },
    };

    #functionsParticipacao;
    #quillQueueManager;

    constructor() {
        const sufixo = "PageServicoForm";
        super({ sufixo });

        CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        CommonFunctions.deepMergeObject(this._objConfigs, { sufixo });

        const objData = {
            objConfigs: this._objConfigs,
            extraConfigs: {
                modeParent: 'searchAndUse',
            }
        }
        this.#functionsParticipacao = new ParticipacaoModule(this, objData);
        this.#quillQueueManager = new QueueManager();  // Cria a fila

        this.initEvents();
    }

    async initEvents() {
        const self = this;
        let buscaDadosBln = true;

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self._idRegister = uuid;
            const url = `${self._objConfigs.url.base}/${self._idRegister}`;
            self._objConfigs.url.baseAnotacao = `${url}/anotacao`;
            self._objConfigs.url.baseCliente = `${url}/cliente`;
            self._objConfigs.url.baseDocumento = `${url}/documento`;
            self._objConfigs.url.basePagamentos = `${url}/pagamentos`;
            self._objConfigs.url.baseParticipacao = `${url}/participacao`;
            self._objConfigs.url.baseValores = `${url}/relatorio/valores`;
            this._action = EnumAction.PUT;
            buscaDadosBln = await self._buscarDados();
        } else {
            this.#buscarAreasJuridicas();
            this._action = EnumAction.POST;
        }

        if (buscaDadosBln) {
            self._queueCheckDomainCustom.setReady();
            await self.#addEventosBotoes();
        }
    }

    async #addEventosBotoes() {
        const self = this;

        if (self._queueSelectDomainCustom) {
            self._queueSelectDomainCustom.enqueue(() =>
                self.#functionsParticipacao._buscarPresetParticipacaoTenant()
            )
        } else {
            self.#functionsParticipacao._buscarPresetParticipacaoTenant();
        }

        self._classQuillEditor = new QuillEditorModule(`#descricao${self.getSufixo}`, { exclude: ['image', 'scriptSub', 'scriptSuper'] });
        self.#quillQueueManager.setReady();  // Informa que o quill está pronto

        CommonFunctions.handleModal(self, $(`#btnOpenAreaJuridicaTenant${self.getSufixo}`), ModalAreaJuridicaTenant, self.#buscarAreasJuridicas.bind(self));

        $(`#btnSaveParticipantes${self.getSufixo} `).on('click', async function (e) {
            e.preventDefault();
            await self.#saveButtonActionParticipacao();
        });

        $(`#btnAdicionarCliente${self.getSufixo} `).on('click', async function () {

            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalPessoa();
                objModal.setDataEnvModal = {
                    perfis_busca: window.Statics.PerfisPermitidoClienteServico,
                };
                const response = await objModal.modalOpen();
                if (response.refresh && response.selecteds) {
                    response.selecteds.map(item => {
                        self.#inserirCliente({
                            perfil_id: item.id,
                            perfil: item
                        });
                    })
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#atualizarClientes${self.getSufixo} `).on('click', async function () {
            await self.#buscarClientes();
        });

        $(`#btnSaveClientes${self.getSufixo} `).on('click', async function (e) {
            e.preventDefault();
            self.#saveButtonActionCliente();
        });

        // $(`#btnAdicionarDocumento${self.getSufixo} `).on('click', async function () {
        //     const btn = $(this);
        //     CommonFunctions.simulateLoading(btn);
        //     try {
        //         const objModal = new ModalSelecionarDocumentoModeloTenant();
        //         objModal.setDataEnvModal = {
        //             documento_modelo_tipo_id: window.Enums.DocumentoModeloTipoEnum.SERVICO,
        //         };
        //         const response = await objModal.modalOpen();

        //         if (response.refresh && response.register) {
        //             try {
        //                 const objModal = new ModalDocumentoModeloTenant(self._objConfigs.url.baseDocumento);
        //                 objModal._dataEnvModal = {
        //                     documento_modelo_tenant: response.register,
        //                     objetos: await self.#getObjetosDocumentoModeloTenantRender(),
        //                 }
        //                 console.log(await objModal.modalOpen());
        //             } catch (error) {
        //                 CommonFunctions.generateNotificationErrorCatch(error);
        //             }
        //         }
        //     } catch (error) {
        //         CommonFunctions.generateNotificationErrorCatch(error);
        //     } finally {
        //         CommonFunctions.simulateLoading(btn, false);
        //     }
        // });

        // $(`#atualizarDocumentos${self.getSufixo} `).on('click', async function () {
        //     await self.#buscarDocumentos();
        // });

        $(`#btnAdicionarAnotacao${self.getSufixo} `).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalAnotacaoLembreteTenant(self._objConfigs.url.baseAnotacao);
                objModal.setFocusElementWhenClosingModal = btn;
                const dataEnvModal = self._checkDomainCustomInheritDataEnvModal();
                if (dataEnvModal?.inherit_domain_id) {
                    objModal.setDataEnvModal = dataEnvModal;
                }
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    self.#inserirAnotacao(response.register);
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#atualizarAnotacao${self.getSufixo} `).on('click', async function () {
            await self.#buscarAnotacoes();
        });

        $(`#btnInserirPagamento${self.getSufixo} `).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalSelecionarPagamentoTipo(`${self._objConfigs.url.base}/${self._idRegister}`);
                const dataEnvModal = self._checkDomainCustomInheritDataEnvModal();
                if (dataEnvModal?.inherit_domain_id) {
                    objModal.setDataEnvModal = dataEnvModal;
                }
                objModal.setFocusElementWhenClosingModal = btn;
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    await self.#buscarPagamentos();
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnExcluirParticipante${self.getSufixo}`).on('click', async function () {
            const response = await self._delButtonAction(`${self._idRegister}/participacao`, null, {
                title: `Exclusão de Participantes`,
                message: `Confirma a exclusão do(s) participante(s) deste serviço?`,
                success: `Participantes excluídos com sucesso!`,
                button: this,
                url: `${self._objConfigs.url.base}`,
            });

            if (response) {
                self.#functionsParticipacao._buscarParticipantes();
            }
        });

        $(`#btnExcluirCliente${self.getSufixo}`).on('click', async function () {
            const response = await self._delButtonAction(`${self._idRegister}/cliente`, null, {
                title: `Exclusão de Clientes`,
                message: `Confirma a exclusão do(s) cliente(s) deste serviço?`,
                success: `Clientes excluídos com sucesso!`,
                button: this,
                url: `${self._objConfigs.url.base}`,
            });

            if (response) {
                self.#buscarClientes();
            }
        });

        $(`#atualizarPagamentos${self.getSufixo}`).on('click', async function () {
            await self.#buscarPagamentos();
        });

        // self.#openModal();
    }

    async #openModal() {
        const self = this;
        try {
            const objModal = new ModalDocumentoModeloTenant(self._objConfigs.url.baseDocumento);
            objModal._dataEnvModal = {
                documento_modelo_tenant: {
                    "id": "9e58f171-b6ce-4e9d-9e4a-9e71ed0bcf7a",
                    "nome": "PROCURAÇÃO AD JUDICIA ET EXTRA - 2 Clientes"
                },
                objetos: await self.#getObjetosDocumentoModeloTenantRender(),
            }
            console.log(await objModal.modalOpen());
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        }
    }

    async #getObjetosDocumentoModeloTenantRender(options = {}) {
        const self = this;
        const { blnBuscarClientes = false } = options;

        if (blnBuscarClientes) {
            await self.#buscarClientes();
        }

        if (self._objConfigs.data.clientesNaTela.length === 0) {
            return [];
        }
        return self._objConfigs.data.clientesNaTela.map(cliente => {
            let obj = {};

            // Encontrar o tipo de pessoa no array de Details
            const pessoaTipo = window.Details.PessoaTipoEnum.find(
                pessoa => pessoa.pessoa_dados_type === cliente.perfil.pessoa.pessoa_dados_type
            );

            if (pessoaTipo) {
                // Encontrar o tipo de vinculo baseado no DocumentoModeloTipoEnum
                const vinculoDocumentoModelo = pessoaTipo.documento_modelo_tenant.find(
                    doc => doc.documento_modelo_tipo_id === window.Enums.DocumentoModeloTipoEnum.SERVICO
                );
                // Encontrar o identificador correto baseado no tipo de perfil
                const identificadorPorPerfil = vinculoDocumentoModelo.objetos.find(
                    doc => doc.perfil_tipo_id === cliente.perfil.perfil_tipo_id
                );

                if (identificadorPorPerfil) {
                    CommonFunctions.deepMergeObject(obj, {
                        identificador: identificadorPorPerfil.identificador,
                        id: cliente.perfil_id,
                    });
                } else {
                    CommonFunctions.generateNotification(`O vínculo do tipo de pessoa <b>${cliente.perfil.pessoa.pessoa_dados_type}</b> não foi configurado.`, 'error');
                }
            } else {
                CommonFunctions.generateNotification(`Tipo de pessoa <b>${cliente.perfil.pessoa.pessoa_dados_type}</b> não foi encontrado.`, 'error');
            }

            return obj;
        });
    }

    async preenchimentoDados(response, options) {
        const self = this;
        const form = $(options.form);

        // Busca os dados pois os lançamentos utilizam dado do tenant
        await self._buscaDadosTenant();

        const responseData = response.data;
        form.find('input[name="titulo"]').val(responseData.titulo);
        self.#buscarAreasJuridicas(responseData.area_juridica_id);
        self.#quillQueueManager.enqueue(() => {
            self._classQuillEditor.getQuill.setContents(responseData.descricao);
        })

        $(`#divAnotacao${self.getSufixo}`).html('');
        responseData.anotacao.forEach(item => {
            self.#inserirAnotacao(item);
        });

        $(`#divPagamento${self.getSufixo}`).html('');
        responseData.pagamento.forEach(item => {
            self.#inserirPagamento(item);
        });

        self.#limparClientes();
        responseData.cliente.forEach(item => {
            self.#inserirCliente(item);
        });

        // self.#limparDocumentos();
        // responseData.documentos.forEach(item => {
        //     self.#inserirDocumento(item);
        // });

        self.#functionsParticipacao._inserirParticipantesEIntegrantes(responseData.participantes);

        self.#atualizaTodosValores(response.data);
    }

    async #inserirDocumento(item) {
        const self = this;
        const divDocumentos = $(`#divDocumentos${self.getSufixo}`);
        item.idCard = UUIDHelper.generateUUID();

        const strCard = `
            <div id="${item.idCard}" class="card card-documento">
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center justify-content-between">
                        <span class="spanNome">${item.nome}</span>
                        <div>
                            <div class="dropdown dropdown-acoes-documento">
                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><button type="button" class="dropdown-item fs-6 btn-delete">Excluir</button></li>
                                </ul>
                            </div>
                        </div>
                    </h5>
                </div>
            </div>`;

        divDocumentos.append(strCard);

        await self.#addEventoDocumento(item);
        return item;
    }

    async #addEventoDocumento(item) {
        const self = this;

        $(`#${item.idCard} .btn-delete`).on('click', async function () {
            const response = await self._delButtonAction(item.id, item.nome, {
                title: `Exclusão de Documento`,
                message: `Confirma a exclusão do documento <b>${item.nome}</b>?`,
                success: `Documento excluído com sucesso!`,
                button: this,
                url: self._objConfigs.url.baseDocumento,
            });

            if (response) {
                $(`#${item.idCard}`).remove();
            }
        });
    }

    #limparDocumentos() {
        const self = this;
        $(`#divDocumentos${self.getSufixo}`).html('');
    }

    async #buscarDocumentos(options = {}) {
        const self = this;
        const { blnLoadingDisplay = true } = options;

        try {
            blnLoadingDisplay ? await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando documentos...' }) : null;

            const response = await self._get({ urlApi: self._objConfigs.url.baseDocumento, checkForcedBefore: true });
            if (response.data) {
                self.#limparDocumentos();
                response.data.map(item => {
                    self.#inserirDocumento(item);
                })
                blnLoadingDisplay ? CommonFunctions.generateNotification('Documentos atualizados com sucesso.', 'success') : null;
            }
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        } finally {
            blnLoadingDisplay ? await CommonFunctions.loadingModalDisplay(false) : null;
        }
    }

    async #inserirCliente(item) {
        const self = this;
        const divClientes = $(`#divClientes${self.getSufixo}`);
        item.idCard = UUIDHelper.generateUUID();

        let nome = '';

        const naTela = self.#verificaClienteNaTela(item);

        switch (item.perfil.pessoa.pessoa_dados_type) {
            case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                nome = item.perfil.pessoa.pessoa_dados.nome;
                break;
            case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                nome = item.perfil.pessoa.pessoa_dados.nome_fantasia;
                break;

            default:
                CommonFunctions.generateNotification(`O tipo de pessoa <b>${item.perfil.pessoa.pessoa_dados_type}</b> ainda não foi implementado.`, 'warning');
                console.error('O tipo de pessoa ainda nao foi implementado.', item);
                return false;
        }

        if (naTela) {
            CommonFunctions.generateNotification(`Cliente <b>${nome}</b> já foi inserido(a) para este tipo de participação.`, 'error');
            return false;
        }

        const strCard = `
            <div id="${item.idCard}" class="card card-cliente">
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center justify-content-between">
                        <span class="spanNome">${nome}</span>
                        <div>
                            <div class="dropdown dropdown-acoes-cliente">
                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><button type="button" class="dropdown-item fs-6 btn-delete">Excluir</button></li>
                                </ul>
                            </div>
                        </div>
                    </h5>
                </div>
            </div>`;

        self.inserirClienteNaTela(item);
        divClientes.append(strCard);

        await self.#addEventoCliente(item);
        return item;
    }

    async #addEventoCliente(item) {
        const self = this;

        $(`#${item.idCard} .btn-delete`).on('click', async function () {
            try {
                // const obj = new ModalMessage();
                // obj.setDataEnvModal = {
                //     title: 'Remoção de Cliente',
                //     message: 'Tem certeza que deseja remover este cliente?',
                // };
                // obj.setFocusElementWhenClosingModal = $(this);
                // const result = await obj.modalOpen();
                // if (result.confirmResult) {

                $(`#${item.idCard}`).remove();
                const clientes = self._objConfigs.data.clientesNaTela;
                const indexPart = clientes.findIndex(cliente => cliente.idCard === item.idCard);

                if (indexPart > -1) {
                    clientes.splice(indexPart, 1);
                }
                // }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        });
    }

    #verificaClienteNaTela(item) {
        const self = this;
        for (const element of self._objConfigs.data.clientesNaTela) {
            if (element.perfil_id == item.perfil_id) {
                return element;
            }
        }
        return null;
    }

    inserirClienteNaTela(item) {
        const self = this;
        if (!self._objConfigs.data?.clientesNaTela) {
            self._objConfigs.data.clientesNaTela = []
        }
        self._objConfigs.data.clientesNaTela.push(item);
    }

    #limparClientes() {
        const self = this;
        self._objConfigs.data.clientesNaTela = [];
        $(`#divClientes${self.getSufixo}`).html('');
        $(`#btnExcluirCliente${self.getSufixo}`).hide('fast')
    }

    async #buscarClientes(options = {}) {
        const self = this;
        const { blnLoadingDisplay = true } = options;

        try {
            blnLoadingDisplay ? await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando clientes...' }) : null;

            const response = await self._get({ urlApi: self._objConfigs.url.baseCliente, checkForcedBefore: true });
            if (response.data) {
                self.#limparClientes();
                response.data.map(item => {
                    self.#inserirCliente(item);
                })

                // Somente páginas tem esse botão, nos modais não há
                !response.data.length ? $(`#btnExcluirCliente${self.getSufixo}`).hide('fast') :
                    $(`#btnExcluirCliente${self.getSufixo}`).show('fast');

                blnLoadingDisplay ? CommonFunctions.generateNotification('Clientes atualizados com sucesso.', 'success') : null;
            }
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        } finally {
            blnLoadingDisplay ? await CommonFunctions.loadingModalDisplay(false) : null;
        }
    }

    async #inserirAnotacao(item) {
        const self = this;
        const divAnotacao = $(`#divAnotacao${self.getSufixo}`);

        item.idCol = UUIDHelper.generateUUID();
        let created_at = '';
        if (item.created_at) {
            created_at = `<span class="text-body-secondary d-block">Cadastrado em ${DateTimeHelper.retornaDadosDataHora(item.created_at, 12)}</span>`;
            item.statusSalvo = true;
        } else {
            item.statusSalvo = false;
        }

        const strToHtml = CommonFunctions.formatStringToHTML(item.descricao);
        let strCard = `
            <div id="${item.idCol}" class="col">
                <div class="card">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title d-flex align-items-center justify-content-between">
                                <span class="text-truncate spanTitle">${item.titulo}</span>
                                <div>
                                    <div class="dropdown">
                                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><button type="button" class="dropdown-item fs-6 btn-edit" title="Editar anotação ${item.titulo}">Editar</button></li>
                                            <li><button type="button" class="dropdown-item fs-6 btn-delete" title="Excluir anotação ${item.titulo}">Excluir</button></li>
                                        </ul>
                                    </div>
                                </div>
                            </h5>
                            <div class="card-text overflow-auto scrollbar text-start" style="max-height: 10rem;">
                                <p class="my-0 pText">${strToHtml}</p>
                            </div>
                        </div>
                        <div class="card-footer text-body-secondary">
                            ${created_at}
                        </div>
                    </div>
                </div>
            </div>`;

        divAnotacao.append(strCard);
        self.#addEventosAnotacao(item);
        SimpleBarHelper.apply();
        return true;
    }

    async #addEventosAnotacao(item) {
        const self = this;

        $(`#${item.idCol}`).find('.btn-edit').on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalAnotacaoLembreteTenant(self._objConfigs.url.baseAnotacao);
                objModal.setDataEnvModal = {
                    idRegister: item.id,
                };
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    $(`#${item.idCol}`).find('.spanTitle').html(response.register.titulo);
                    $(`#${item.idCol}`).find('.pText').html(CommonFunctions.formatStringToHTML(response.register.descricao));
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#${item.idCol}`).find(`.btn-delete`).click(async function () {
            const response = await self._delButtonAction(item.id, item.titulo, {
                title: `Exclusão de Anotação`,
                message: `Confirma a exclusão da anotação <b>${item.titulo}</b>?`,
                success: `Anotação excluída com sucesso!`,
                button: this,
                url: self._objConfigs.url.baseAnotacao,
            });

            if (response) {
                $(`#${item.idCol}`).remove();
            }
        });
    }

    #limparAnotacoes() {
        const self = this;
        $(`#divAnotacao${self.getSufixo}`).html('');
    }

    async #buscarAnotacoes(options = {}) {
        const self = this;
        const { blnLoadingDisplay = true } = options;

        try {
            blnLoadingDisplay ? await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando anotações...' }) : null;

            const response = await self._get({ urlApi: self._objConfigs.url.baseAnotacao, checkForcedBefore: true });
            if (response.data) {
                self.#limparAnotacoes();
                response.data.map(item => {
                    self.#inserirAnotacao(item);
                })
                blnLoadingDisplay ? CommonFunctions.generateNotification('Anotações atualizadas com sucesso.', 'success') : null;
            }
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        } finally {
            blnLoadingDisplay ? await CommonFunctions.loadingModalDisplay(false) : null;
        }
    }

    async #inserirPagamento(item) {
        const self = this;
        const divPagamento = $(`#divPagamento${self.getSufixo}`);

        item.idCard = `${UUIDHelper.generateUUID()}${self.getSufixo}`;
        const created_at = `<span class="text-body-secondary d-block">Pagamento cadastrado em ${DateTimeHelper.retornaDadosDataHora(item.created_at, 12)}</span>`;

        let htmlColsEspecifico = self.#htmlColsEspecificosPagamento(item);
        let htmlAppend = self.#htmlParticipantes(item, 'pagamento', item.status_id);
        htmlAppend += self.#htmlAppendPagamento(item);
        let htmlLancamentos = self.#htmlLancamentos(item);
        if (htmlLancamentos) {
            htmlLancamentos = `
                <div class="accordion mt-2" id="accordionPagamento${item.id}">
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <button class="accordion-button py-1 collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseOne${item.id}" aria-expanded="false"
                                aria-controls="collapseOne${item.id}">
                                Lançamentos
                            </button>
                        </div>
                        <div id="collapseOne${item.id}" class="accordion-collapse collapse"
                            data-bs-parent="#accordionPagamento${item.id}">
                            <div class="accordion-body d-flex flex-column gap-2">
                                ${htmlLancamentos}
                            </div>
                        </div>
                    </div>
                </div>`;
        }
        const pagamentoAtivo = item.status_id == window.Enums.PagamentoStatusTipoEnum.ATIVO ? true : false;
        const tachado = (window.Statics.StatusPagamentoTachado.findIndex(x => x == item.status_id) != -1);

        let strCard = `
            <div id="${item.idCard}" class="card p-0">
                <div class="card-body">
                    <div class="row ${tachado ? 'fst-italic text-secondary-emphasis text-decoration-line-through' : ''}">
                        <h5 class="card-title d-flex align-items-center justify-content-between">
                            <span class="text-truncate">
                                <span title="Número do pagamento">N.P.</span> ${item.numero_pagamento} - ${item.pagamento_tipo_tenant.nome}</span>
                            <div>
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><button type="button" class="dropdown-item fs-6 btn-participacao-pagamento ${pagamentoAtivo ? '' : 'disabled'}" title="Inserir/Editar Participação ${item.pagamento_tipo_tenant.nome}">Participação</button></li>
                                        <li><button type="button" class="dropdown-item fs-6 btn-edit" title="Editar pagamento">Editar</button></li>
                                        <li><button type="button" class="dropdown-item fs-6 btn-delete" title="Excluir pagamento ${item.pagamento_tipo_tenant.nome}">Excluir</button></li>
                                    </ul>
                                </div>
                            </div>
                        </h5>
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 align-items-end">
                            ${htmlColsEspecifico}
                        </div>
                        ${htmlAppend}
                    </div>
                    ${htmlLancamentos}
                    <div class="form-text mt-2">${created_at}</div>
                </div>
            </div>`;

        divPagamento.append(strCard);
        BootstrapFunctionsHelper.addEventPopover();
        self.#addEventosPagamento(item);
        return true;
    }

    #htmlColsEspecificosPagamento(item) {

        let htmlColsEspecifico = '';
        if (item?.status) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Status</div>
                    <p class="text-truncate" title="${item.status?.descricao ?? ''}">${item.status.nome}</p>
                </div>`;
        }

        if (item?.forma_pagamento) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Forma de Pagamento Padrão</div>
                    <p class="text-truncate">${item.forma_pagamento.nome}</p>
                </div>`;
        }

        if (item.valor_total) {
            const valorTotal = CommonFunctions.formatWithCurrencyCommasOrFraction(item.valor_total);
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Valor Total</div>
                    <p class="">${valorTotal}</p>
                </div>`;
        }

        if (item.entrada_valor) {
            const valorEntrada = CommonFunctions.formatWithCurrencyCommasOrFraction(item.entrada_valor);
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Valor Entrada</div>
                    <p class="">${valorEntrada}</p>
                </div>`;
        }

        if (item.entrada_data) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Data Entrada</div>
                    <p class="">${DateTimeHelper.retornaDadosDataHora(item.entrada_data, 2)}</p>
                </div>`;
        }

        if (item.parcela_data_inicio) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Primeira Parcela</div>
                    <p class="">${DateTimeHelper.retornaDadosDataHora(item.parcela_data_inicio, 2)}</p>
                </div>`;
        }

        if (item.parcela_valor) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Valor Parcela</div>
                    <p class="">${CommonFunctions.formatWithCurrencyCommasOrFraction(item.parcela_valor)}</p>
                </div>`;
        }

        if (item.parcela_vencimento_dia) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Dia Vencimento</div>
                    <p class="">${item.parcela_vencimento_dia}</p>
                </div>`;
        }

        if (item.parcela_quantidade) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Qtd Parcelas</div>
                    <p class="">${item.parcela_quantidade}</p>
                </div>`;
        }
        if (!item.descricao_condicionado) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Total Aguardando</div>
                    <p class="">${CommonFunctions.formatWithCurrencyCommasOrFraction(item.total_aguardando ?? 0)}</p>
                </div>`;

            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Total Liquidado</div>
                    <p class="">${CommonFunctions.formatWithCurrencyCommasOrFraction(item.total_liquidado ?? 0)}</p>
                </div>`;

            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Total Inadimplente</div>
                    <p class="">${CommonFunctions.formatWithCurrencyCommasOrFraction(item.total_inadimplente ?? 0)}</p>
                </div>`;
        }
        return htmlColsEspecifico;
    }

    #htmlAppendPagamento(item) {
        let htmlAppend = '';

        if (item.descricao_condicionado) {
            htmlAppend += `
            <p class="mb-0 text-truncate" title="${item.descricao_condicionado}">
               <b>Descrição condicionado:</b> ${item.descricao_condicionado}
            </p>`;
        }

        if (item.observacao) {
            htmlAppend += `
            <p class="mb-0 text-truncate" title="${item.observacao}">
               <b>Observação:</b> ${item.observacao}
            </p>`;
        }

        return htmlAppend;
    }

    async #addEventosPagamento(item) {
        const self = this;

        $(`#${item.idCard}`).find('.btn-edit').on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalServicoPagamento({ urlApi: self._objConfigs.url.basePagamentos });
                objModal.setDataEnvModal = self._checkDomainCustomInheritDataEnvModal({
                    idRegister: item.id,
                });
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    self.#buscarPagamentos();
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#${item.idCard}`).find(`.btn-delete`).on('click', async function () {
            const response = await self._delButtonAction(item.id, item.pagamento_tipo_tenant.nome, {
                title: `Exclusão de Pagamento`,
                message: `Confirma a exclusão do pagamento <b>${item.pagamento_tipo_tenant.nome}</b>?`,
                success: `Pagamento excluído com sucesso!`,
                button: this,
                url: self._objConfigs.url.basePagamentos,
            });

            if (response) {
                self.#buscarPagamentos();
            }
        });

        $(`#${item.idCard}`).find('.btn-participacao-pagamento').on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalParticipacao({
                    urlApi: `${self._objConfigs.url.basePagamentos}/${item.id}/participacao`
                });
                objModal.setDataEnvModal = self._checkDomainCustomInheritDataEnvModal();
                const response = await objModal.modalOpen();
                if (response.refresh && response.registers) {
                    self.#buscarPagamentos();
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#${item.idCard}`).find(`.btn-delete-participante-pagamento`).on('click', async function () {

            const response = await self._delButtonAction(`${item.id}/participacao`, item.pagamento_tipo_tenant.nome, {
                title: `Exclusão de Participantes`,
                message: `Confirma a exclusão do(s) participante(s) personalizado(s) do pagamento <b>${item.pagamento_tipo_tenant.nome}</b>?`,
                success: `Participantes excluídos com sucesso!`,
                button: this,
                url: `${self._objConfigs.url.basePagamentos}`,
            });

            if (response) {
                self.#buscarPagamentos();
            }
        });

        await self.#addEventosLancamento(item);
    }

    #htmlLancamentos(item) {
        const self = this;

        let htmlLancamentos = '';
        const pagamento = JSON.parse(JSON.stringify(item));
        delete pagamento.lancamentos;

        for (const lancamento of item.lancamentos) {
            lancamento.pagamento = pagamento;

            const pagamentoAtivo = item.status_id == window.Enums.PagamentoStatusTipoEnum.ATIVO ? true : false;

            let editParticipante = true;
            if (window.Statics.StatusImpossibilitaEdicaoLancamentoServico.findIndex(x => x == item.status_id) != -1) {
                editParticipante = false;
            }

            const tachado = (window.Statics.StatusLancamentoTachado.findIndex(x => x == lancamento.status_id) != -1);
            lancamento.idCard = `${UUIDHelper.generateUUID()}${self.getSufixo}`;

            let htmlAppend = '';

            if (lancamento.observacao) {
                htmlAppend += `
                <div class="row">
                    <div class="col">
                        <div class="form-text mt-0">Observação</div>
                        <p class="text-truncate text-wrap" title="${lancamento.observacao}">${lancamento.observacao}</p>
                    </div>
                </div>`;
            }

            if (!tachado) htmlAppend += self.#htmlParticipantes(lancamento, 'lancamento', item.status_id);

            const htmlBtns = self.#htmlBtnsLancamentos(lancamento);
            const htmlColsLancamento = self.#htmlColsLancamento(lancamento);

            htmlLancamentos += `
                <div id="${lancamento.idCard}" class="card p-0 ${tachado ? 'fst-italic text-secondary-emphasis text-decoration-line-through' : ''}">
                    <div class="card-header d-flex align-items-center justify-content-between py-1">
                        <span>${lancamento.descricao_automatica}</span>
                        <div>
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button type="button" class="dropdown-item fs-6 btn-participacao-lancamento ${pagamentoAtivo && editParticipante && !tachado ? '' : 'disabled'}" title="Inserir/Editar Participação ${lancamento.descricao_automatica}">
                                            Participação
                                        </button>
                                    </li>
                                    ${htmlBtns}
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 align-items-end">
                            ${htmlColsLancamento}
                        </div>
                        ${htmlAppend}
                    </div>
                </div>`
        }

        return htmlLancamentos;
    }

    #htmlColsLancamento(lancamento) {

        let htmlColsLancamento = `
            <div class="col">
                <div class="form-text mt-0">Status</div>
                <p>${lancamento.status.nome}</p>
            </div>`;

        const title_forma_pagamento = lancamento.forma_pagamento?.nome ?? 'Forma de Pagamento Padrão do Pagamento';
        const nome_forma_pagamento = lancamento.forma_pagamento?.nome ?? `<i>${title_forma_pagamento}</i>`;
        htmlColsLancamento += `
            <div class="col">
                <div class="form-text mt-0">Forma de Pagamento</div>
                <p class="text-truncate" title="${title_forma_pagamento}">
                    ${nome_forma_pagamento}
                </p>
            </div>`;

        const valor_esperado = CommonFunctions.formatWithCurrencyCommasOrFraction(lancamento.valor_esperado); htmlColsLancamento += `
            <div class="col">
                <div class="form-text mt-0">Valor Esperado</div>
                <p>${valor_esperado}</p>
            </div>`;

        const data_vencimento = DateTimeHelper.retornaDadosDataHora(lancamento.data_vencimento, 2);
        htmlColsLancamento += `
            <div class="col">
                <div class="form-text mt-0">Data de vencimento</div>
                <p>${data_vencimento}</p>
            </div>`;

        if (lancamento.valor_recebido) {
            const valor_recebido = CommonFunctions.formatWithCurrencyCommasOrFraction(lancamento.valor_recebido);
            htmlColsLancamento += `
                <div class="col">
                    <div class="form-text mt-0">Valor Recebido</div>
                    <p>${valor_recebido}</p>
                </div> `;
        }

        if (lancamento.data_recebimento) {
            const data_recebimento = DateTimeHelper.retornaDadosDataHora(lancamento.data_recebimento, 2);
            htmlColsLancamento += `
                <div class="col">
                    <div class="form-text mt-0">Data recebimento</div>
                    <p>${data_recebimento}</p>
                </div> `;
        }

        return htmlColsLancamento;
    }

    #htmlParticipantes(item, tipo, pagamentoStatusId) {
        let html = '';

        let title = ''
        let empty = '';
        let btnDel = '';

        const pagamentoAtivo = pagamentoStatusId == window.Enums.PagamentoStatusTipoEnum.ATIVO ? true : false;

        let editParticipante = true;
        if (window.Statics.StatusImpossibilitaEdicaoLancamentoServico.findIndex(x => x == item.status_id) != -1) {
            editParticipante = false;
        }

        switch (tipo) {
            case 'pagamento':
                title = `Participantes do pagamento ${item.pagamento_tipo_tenant.nome} `;
                empty = 'Participante(s) herdado do serviço';
                btnDel = 'btn-delete-participante-pagamento';
                break;

            case 'lancamento':
                title = `Participantes do lançamento ${item.descricao_automatica} `;
                empty = 'Participante(s) herdado do pagamento';
                btnDel = 'btn-delete-participante-lancamento';
                break;
        }

        if (editParticipante && pagamentoAtivo) {
            btnDel = `<button type = "button" class="btn btn-sm btn-outline-danger border-0 ${btnDel}" > Excluir</button>`;
        } else {
            btnDel = '';
        }

        if (item?.participantes && item.participantes.length > 0) {
            const arrays = ParticipacaoHelpers.htmlRenderParticipantesEIntegrantes(item.participantes);
            html = `
                <p class="mb-0">
                    Participação personalizada:
                    ${btnDel}
                    <button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="${title}" data-bs-html="true" data-bs-content="${arrays.arrayParticipantes.join("<hr class='my-1'>")}">
                        Ver
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Integrantes de Grupos" data-bs-html="true" data-bs-content="${arrays.arrayIntegrantes.join("<hr class='my-1'>")}">
                        Ver integrantes dos grupos
                    </button>
                </p>`;
        } else {
            html = `<p class="mb-0 fst-italic">${empty}</p>`;
        }
        return html;
    }

    #htmlBtnsLancamentos(lancamento) {
        const self = this;
        const configAcoes = self._objConfigs.data.configAcoes;
        let strBtns = '';
        const enumPag = window.Enums.PagamentoStatusTipoEnum;
        const pagamentoAtivo = lancamento.pagamento.status_id == enumPag.ATIVO ? true : false;

        if (pagamentoAtivo) {
            const botoes = self.#getConfigBotoesStatus(lancamento);

            botoes.forEach(botao => {
                const podeExibir =
                    configAcoes[botao.chave]?.opcao_nos_status?.includes(lancamento.status_id) &&
                    botao.condicao();

                if (podeExibir) {
                    strBtns += `
                    <li>
                        <button type="button" class="dropdown-item fs-6 ${botao.cor} ${botao.classe}"
                            title="Alterar status para ${botao.texto} para o lançamento ${lancamento.descricao_automatica}.">
                            ${botao.icon} ${botao.texto}
                        </button>
                    </li>`;
                }
            });
        }

        return strBtns;
    }

    #getConfigBotoesStatus(lancamento) {
        const self = this;
        const lancamentoDiluido = lancamento.parent_id ? true : false;

        return [
            {
                chave: 'AGUARDANDO_PAGAMENTO_EM_ANALISE',
                classe: 'btn-aguardando-pagamento-analise',
                texto: 'Aguardando Pagamento (em Análise)',
                icon: '<i class="bi bi-hourglass-top"></i>',
                cor: 'text-primary',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'AGUARDANDO_PAGAMENTO',
                classe: 'btn-aguardando-pagamento',
                texto: 'Aguardando Pagamento',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-primary',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'LIQUIDADO_EM_ANALISE',
                classe: 'btn-liquidado-analise',
                texto: 'Liquidado (em Análise)',
                icon: '<i class="bi bi-check2"></i>',
                cor: 'text-success',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'LIQUIDADO',
                classe: 'btn-liquidado',
                texto: 'Liquidado',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-success',
                tipoAcao: 'movimentar',
                condicao: () => true
            },
            {
                chave: 'LIQUIDADO_PARCIALMENTE_EM_ANALISE',
                classe: 'btn-liquidado-parcialmente-analise',
                texto: 'Liquidado Parcialmente (em Análise)',
                icon: '<i class="bi bi-exclamation-lg"></i>',
                cor: 'text-success-emphasis',
                tipoAcao: 'alterar_status',
                condicao: () => !lancamentoDiluido
            },
            {
                chave: 'LIQUIDADO_PARCIALMENTE',
                classe: 'btn-liquidado-parcialmente',
                texto: 'Liquidado Parcialmente',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-success-emphasis',
                tipoAcao: 'movimentar',
                condicao: () => !lancamentoDiluido
            },
            {
                chave: 'REAGENDADO_EM_ANALISE',
                classe: 'btn-reagendado-analise',
                texto: 'Reagendado (em Análise)',
                icon: '<i class="bi bi-calendar-event"></i>',
                cor: 'text-warning',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'REAGENDADO',
                classe: 'btn-reagendado',
                texto: 'Reagendado',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-warning',
                tipoAcao: 'reagendar',
                condicao: () => true
            },
            {
                chave: 'EM_ATRASO_EM_ANALISE',
                classe: 'btn-em-atraso-analise',
                texto: 'Em atraso (em Análise)',
                icon: '<i class="bi bi-stopwatch"></i>',
                cor: 'text-danger',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'EM_ATRASO',
                classe: 'btn-em-atraso',
                texto: 'Em atraso',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-danger',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'CANCELADO_EM_ANALISE',
                classe: 'btn-cancelado-analise',
                texto: 'Cancelado (em Análise)',
                icon: '<i class="bi bi-dash-circle"></i>',
                cor: 'text-danger',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'CANCELADO',
                classe: 'btn-cancelado',
                texto: 'Cancelado',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-danger',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'INADIMPLENTE_EM_ANALISE',
                classe: 'btn-inadimplente-analise',
                texto: 'Inadimplente (em Análise)',
                icon: '<i class="bi bi-dash-circle"></i>',
                cor: 'text-danger',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'INADIMPLENTE',
                classe: 'btn-inadimplente',
                texto: 'Inadimplente',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-danger',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'LIQUIDADO_MIGRACAO_SISTEMA',
                classe: 'btn-liquidado-migracao',
                texto: 'Liquidado (Migração Sistema)',
                icon: '<i class="bi bi-journal-check"></i>',
                cor: '',
                tipoAcao: 'alterar_status',
                condicao: () => self._objConfigs.dados_tenant?.lancamento_liquidado_migracao_sistema_bln
            }
        ];
    }

    #addEventosLancamento(pagamento) {
        const self = this;
        const accordionBody = $(`#accordionPagamento${pagamento.id} .accordion-body`);
        const urlLancamentos = `${self._objConfigs.url.basePagamentos} /${pagamento.id}/lancamentos`;

        const enumLanc = window.Enums.LancamentoStatusTipoEnum;
        const configAcoes = self._objConfigs.data.configAcoes;
        const botoes = self.#getConfigBotoesStatus(pagamento);

        const atualizaLancamentos = async () => {
            try {
                const response = await self._getRecurse({ idRegister: pagamento.id, urlApi: self._objConfigs.url.basePagamentos });
                const htmlLancamentos = self.#htmlLancamentos(response.data);
                accordionBody.html(htmlLancamentos);
                BootstrapFunctionsHelper.addEventPopover();
                await self.#addEventosLancamento(response.data);
                CommonFunctions.generateNotification('Lançamento atualizado com sucesso.', 'success');
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        }

        const pagamentoAtivo = pagamento.status_id == window.Enums.PagamentoStatusTipoEnum.ATIVO ? true : false;
        if (pagamentoAtivo) {
            pagamento.lancamentos.map((lancamento) => {

                const openMovimentar = async function (status_id) {
                    try {
                        const objModal = new ModalLancamentoServicoMovimentar();
                        objModal.setDataEnvModal = {
                            idRegister: lancamento.id,
                            pagamento_id: lancamento.pagamento_id,
                            status_id: status_id
                        }
                        const response = await objModal.modalOpen();
                        if (response.refresh) {
                            await atualizaLancamentos();
                        }
                    } catch (error) {
                        CommonFunctions.generateNotificationErrorCatch(error);
                    }
                }

                /**
                 * Abre um modal para confirmar a alteração de status de um lançamento.
                 * @param {object} [dados={}] - Dados para o modal.
                 * @param {string} [dados.descricao_automatica] - Descrição automática do lançamento.
                 * @param {string} [dados.status_html] - Status HTML do lançamento.
                 * @param {number} [dados.status_id] - ID do status do lançamento.
                 */
                const openAlterarStatus = async function (dados = {}) {
                    const descricao_automatica = dados.descricao_automatica ?? lancamento.descricao_automatica;
                    const status_html = dados.status_html;
                    const status_id = dados.status_id;

                    try {
                        const obj = new ModalMessage();
                        obj.setDataEnvModal = {
                            title: 'Alterar Status',
                            message: `Confirma a alteração de status do lancamento <b> ${descricao_automatica}</b> para <b class="fst-italic"> ${status_html}</b>? `,
                        };
                        obj.setFocusElementWhenClosingModal = this;
                        const result = await obj.modalOpen();
                        if (result.confirmResult) {
                            const objConn = new ConnectAjax(`${self._objConfigs.url.baseMovimentacaoContaLancamentoServico}/status-alterar`);

                            const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
                            if (instance) {
                                if (!lancamento.domain_id) {
                                    console.error(lancamento);
                                    throw new Error("Unidade de domínio do registro não encontrada. Contate o suporte.");
                                }
                                objConn.setForcedDomainCustomId = lancamento.domain_id;
                            }

                            objConn.setAction(EnumAction.POST);
                            objConn.setData({
                                lancamento_id: lancamento.id,
                                status_id: status_id,
                            });
                            const response = await objConn.envRequest();
                            if (response.data) {
                                await atualizaLancamentos();
                            }
                        }
                    } catch (error) {
                        CommonFunctions.generateNotificationErrorCatch(error);
                    }
                }

                $(`#${lancamento.idCard}`).find('.btn-participacao-lancamento').on('click', async function () {
                    const btn = $(this);
                    CommonFunctions.simulateLoading(btn);
                    try {
                        const objModal = new ModalParticipacao({
                            urlApi: `${urlLancamentos}/${lancamento.id}/participacao`
                        });
                        objModal.setDataEnvModal = self._checkDomainCustomInheritDataEnvModal();
                        const response = await objModal.modalOpen();
                        if (response.refresh && response.registers) {
                            atualizaLancamentos();
                        }
                    } catch (error) {
                        CommonFunctions.generateNotificationErrorCatch(error);
                    } finally {
                        CommonFunctions.simulateLoading(btn, false);
                    }
                });

                $(`#${lancamento.idCard}`).find(`.btn-delete-participante-lancamento`).on('click', async function () {

                    const response = await self._delButtonAction(`${lancamento.id}/participacao`, lancamento.descricao_automatica, {
                        title: `Exclusão de Participantes`,
                        message: `Confirma a exclusão do(s) participante(s) personalizado(s) do lançamento <b>${lancamento.descricao_automatica}</b>?`,
                        success: `Participantes excluídos com sucesso!`,
                        button: this,
                        url: urlLancamentos
                    });
                    if (response) {
                        atualizaLancamentos();
                    }
                });

                botoes.forEach(config => {
                    const btn = $(`#${lancamento.idCard}`).find(`.${config.classe}`);
                    const pode = configAcoes[config.chave]?.opcao_nos_status?.includes(lancamento.status_id);

                    if (btn.length && pode && config.condicao()) {
                        btn.on('click', async function () {
                            const status_id = enumLanc[config.chave];

                            switch (config.tipoAcao) {
                                case 'alterar_status':
                                    await openAlterarStatus({ status_html: config.texto, status_id });
                                    break;

                                case 'movimentar':
                                    await openMovimentar(status_id);
                                    break;

                                case 'reagendar':
                                    const modal = new ModalLancamentoReagendar({
                                        urlApi: `${self._objConfigs.url.baseLancamento}/servicos/reagendar`
                                    });
                                    modal.setDataEnvModal = self._checkDomainCustomInheritDataEnvModalForObjData(lancamento, {
                                        idRegister: lancamento.id,
                                        status_id,
                                        data_atual: lancamento.data_vencimento
                                    });
                                    const response = await modal.modalOpen();
                                    if (response.refresh) await atualizaLancamentos();
                                    break;
                            }
                        });
                    }
                });

            });
        }
    }

    #atualizaTodosValores(data) {
        const self = this;
        self.#atualizarValorFinal(data.valor_final);
        self.#atualizarValorServico(data.valor_servico);
        self.#atualizarTotalAguardando(data.total_aguardando);
        self.#atualizarTotalCancelado(data.total_cancelado);
        self.#atualizarTotalEmAnalise(data.total_analise);
        self.#atualizarTotalLiquidado(data.total_liquidado);
        self.#atualizarTotalInadimplente(data.total_inadimplente);
    }

    #atualizarValorServico(valor) {
        const self = this;
        $(`#valorServico${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    #atualizarValorFinal(valor) {
        const self = this;
        $(`#valorFinal${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    #atualizarTotalAguardando(valor) {
        const self = this;
        $(`#totalAguardando${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    #atualizarTotalCancelado(valor) {
        const self = this;
        $(`#totalCancelado${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    #atualizarTotalEmAnalise(valor) {
        const self = this;
        $(`#totalEmAnalise${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    #atualizarTotalLiquidado(valor) {
        const self = this;
        $(`#totalLiquidado${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    #atualizarTotalInadimplente(valor) {
        const self = this;
        $(`#totalInadimplente${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    async #buscarAreasJuridicas(selected_id = null) {
        try {
            const self = this;
            let options = { outInstanceParentBln: true };
            selected_id ? options.selectedIdOption = selected_id : null;
            const selector = $(`#area_juridica_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(selector, self._objConfigs.url.baseAreaJuridicaTenant, options);
            return true
        } catch (error) {
            return false;
        }
    }

    async #buscarPagamentos(options = {}) {
        const self = this;
        const { blnLoadingDisplay = true } = options;
        BootstrapFunctionsHelper.removeEventPopover();

        try {
            blnLoadingDisplay ? await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando pagamentos...' }) : null;

            const response = await self._get({ urlApi: self._objConfigs.url.basePagamentos, checkForcedBefore: true });

            if (response.data) {
                $(`#divPagamento${self.getSufixo}`).html('');
                for (const item of response.data) {
                    self.#inserirPagamento(item);
                }
                await self.#buscarValores();
            }
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        } finally {
            blnLoadingDisplay ? await CommonFunctions.loadingModalDisplay(false) : null;
        }

    }

    async #buscarValores() {
        const self = this;
        try {
            const response = await self._get({ urlApi: self._objConfigs.url.baseValores, checkForcedBefore: true });
            if (response.data) {
                self.#atualizaTodosValores(response.data);
            }
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        }
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(`#form${self.getSufixo}`);
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
        const descricaoDelta = self._classQuillEditor.getQuill.getContents();
        data.descricao = descricaoDelta;

        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.url.base, {
                redirectWithIdBln: true,
            });
        }
        return false;
    }

    #saveVerifications(data, formRegistration) {
        const self = this;
        if (self._action == EnumAction.POST) {
            let blnSave = CommonFunctions.verificationData(data.titulo, { field: formRegistration.find('input[name="titulo"]'), messageInvalid: 'O título deve ser informado.', setFocus: true });
            blnSave = CommonFunctions.verificationData(data.area_juridica_id, { field: formRegistration.find('select[name="area_juridica_id"]'), messageInvalid: 'A Área Jurídica deve ser selecionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            // blnSave = CommonFunctions.verificationData(data.descricao, { field: formRegistration.find('textarea[name="descricao"]'), messageInvalid: 'A descrição deve ser preenchida.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            return blnSave;
        }
        return true;
    }

    async #saveButtonActionCliente() {
        const self = this;
        let data = {
            clientes: self._objConfigs.data.clientesNaTela.map(item => {
                let obj = { perfil_id: item.perfil_id };
                item.id ? obj.id = item.id : null;
                return obj;
            }),
        }

        if (data.clientes.length) {
            const response = await self._save(data, self._objConfigs.url.baseCliente, {
                action: EnumAction.POST,
                btnSave: $(`#btnSaveClientes${self.getSufixo}`),
                redirectBln: false,
                returnObjectSuccess: true,
            });
            if (response) {
                self.#limparClientes();
                response.data.map(item => { self.#inserirCliente(item); });
            }
        }
    }

    async #saveButtonActionParticipacao() {
        const self = this;
        let data = {
            participantes: self._objConfigs.data.participantesNaTela,
        }

        if (self.#functionsParticipacao._saveVerificationsParticipantes(data)) {
            const response = await self._save(data, self._objConfigs.url.baseParticipacao, {
                action: EnumAction.POST,
                btnSave: $(`#btnSaveParticipantes${self.getSufixo}`),
                redirectBln: false,
                returnObjectSuccess: true,
            });
            if (response) {
                self.#functionsParticipacao._inserirParticipantesEIntegrantes(response.data);
            }
        }
    }
}

$(function () {
    new PageServicoForm();
});