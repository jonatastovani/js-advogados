import { CommonFunctions } from "../../../commons/CommonFunctions";
import { EnumAction } from "../../../commons/EnumAction";
import { TemplateForm } from "../../../commons/templates/TemplateForm";
import { ModalNome } from "../../../components/comum/ModalNome";
import { ModalOrdemLancamentoStatusTipoTenant } from "../../../components/tenant/ModalOrdemLancamentoServicosTenant";
import { TenantDataHelper } from "../../../helpers/TenantDataHelper";
import TenantTypeDomainCustomHelper from "../../../helpers/TenantTypeDomainCustomHelper";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class PageSistemaFormConfiguracoes extends TemplateForm {

    constructor() {
        const objConfigs = {
            url: {
                base: window.apiRoutes.baseTenant,
            },
            sufixo: 'PageSistemaFormConfiguracoes',
            data: {
                domainsNaTela: [],
                order_by_servicos_lancamentos: [],
            },
        };

        super({
            objConfigs: objConfigs
        });
        this.initEvents();
    }

    async initEvents() {
        const self = this;
        self._idRegister = 'current';
        self._action = EnumAction.PUT;

        self.#addEventosPadrao();
        await self._buscarDados();
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        $(`#btn-ordem-lancamentos-status-servico`).on('click', async function () {
            try {
                const objModal = new ModalOrdemLancamentoStatusTipoTenant();
                if (self._objConfigs.data?.order_by_servicos_lancamentos?.length) {
                    objModal.setDataEnvModal = {
                        ordem_custom_array: self._objConfigs.data.order_by_servicos_lancamentos,
                    }
                    console.warn('Tem algo para enviar');
                } else {
                    console.warn('Não tem nada para enviar');
                }
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    self._objConfigs.data.order_by_servicos_lancamentos = response.ordem_custom_array;
                }
            } finally {
                CommonFunctions.simulateLoading($(this), false);
            }
        }).trigger('click');
    }

    async preenchimentoDados(response, options = {}) {
        const self = this;
        const form = $(options.form);

        const responseData = response.data;
        const domains = responseData.domains;

        form.find('input[name="name"]').val(responseData.name);
        form.find('input[name="sigla"]').val(responseData.sigla ?? '');
        form.find('input[name="lancamento_liquidado_migracao_sistema_bln"]').prop('checked', responseData.lancamento_liquidado_migracao_sistema_bln);
        form.find('input[name="cancelar_liquidado_migracao_sistema_automatico_bln"]').prop('checked', responseData.cancelar_liquidado_migracao_sistema_automatico_bln);
        if (domains.length) domains.map(domain => { self._inserirDominio(domain); });

        const tenantData = TenantDataHelper.getTenantData();
        self._objConfigs.data.order_by_servicos_lancamentos = tenantData.order_by_servicos_lancamentos ?? [];
    }

    async _inserirDominio(domain) {
        const self = this;
        const divDominio = $(`#divDominio${self._objConfigs.sufixo}`);

        const urlDomain = URLHelper.formatUrlHttp(domain.domain);

        domain.idCol = UUIDHelper.generateUUID();
        const dominioVigente = window.location.hostname == domain.domain;
        const customDomain = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;

        let strCard = `
            <div id="${domain.idCol}" class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center justify-content-between mb-0">
                            <span class="text-truncate spanTitle">${domain.name}</span>
                            <div>
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><button type="button" class="dropdown-item fs-6 btn-edit-name" title="Editar nome ${domain.name}">Editar nome</button></li>
                                    </ul>
                                </div>
                            </div>
                        </h5>
                        <div class="card-text">
                            <p class="mb-1">Domínio: <span class="fst-italic">${domain.domain}</span></p>
                        </div>
                        ${!dominioVigente && !customDomain ? `<a href="${domain.id ? `${urlDomain}` : '#'}" class="btn btn-outline-primary border-0 btn-sm ${!domain.id ? 'disabled' : ''}" ${domain.id ? `target="_blank"` : ''}>Ir para o domínio</a>` : ''}
                    </div>
                </div>
            </div>`;

        divDominio.append(strCard);
        self.#addEventosDominio(domain);
        self._objConfigs.data.domainsNaTela.push({
            idCol: domain.idCol,
            id: domain.id,
            name: domain.name,
        });

        return true;
    }

    async #addEventosDominio(dominio) {
        const self = this;

        $(`#${dominio.idCol} .btn-edit-name`).on('click', async function () {

            let registro = undefined;
            for (const element of self._objConfigs.data.domainsNaTela) {
                if (element.idCol == dominio.idCol) {
                    registro = element;
                    break;
                }
            }

            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModalNome = new ModalNome();
                objModalNome.setDataEnvModal = {
                    title: 'Nome domínio',
                    mensagem: 'Informe o nome do domínio',
                    nome: registro.name,
                }
                const response = await objModalNome.modalOpen();
                if (response.refresh) {
                    registro.name = response.name;
                    $(`#${dominio.idCol} .spanTitle`).html(registro.name);
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });
    }

    saveButtonAction() {
        const self = this;
        const formData = $(`#form${self._objConfigs.sufixo}`);
        let data = CommonFunctions.getInputsValues(formData[0]);

        data.domains = self._objConfigs.data.domainsNaTela;

        if (self._saveVerifications(data, formData)) {
            self._save(data, `${self._objConfigs.url.base}/update-cliente`);
        }
        return false;
    }

    _saveVerifications(data, formData) {
        const self = this;

        let blnSave = CommonFunctions.verificationData(data.name, { field: formData.find('input[name="name"]'), messageInvalid: 'O campo <b>Nome da Empresa</b> deve ser informado.', setFocus: true });

        blnSave = CommonFunctions.verificationData(data.sigla, { field: formData.find('input[name="sigla"]'), messageInvalid: 'O campo <b>Sigla</b> deve ser informado.', setFocus: true });

        return blnSave;
    }
}

$(function () {
    new PageSistemaFormConfiguracoes();
});