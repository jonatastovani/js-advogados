import { commonFunctions } from "../../../commons/commonFunctions";
import { TemplateForm } from "../../../commons/templates/TemplateForm";
import { modalNome } from "../../../components/comum/modalNome";
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
            },
        };

        super({
            objConfigs: objConfigs
        });
        this.initEvents();
    }

    async initEvents() {
        const self = this;
        self._idRegister = 'current'

        await self._buscarDados();

        self.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        // $(`#btnOpenEstadoCivilTenant${self._objConfigs.sufixo}`).on('click', async function () {
        //     const btn = $(this);
        //     commonFunctions.simulateLoading(btn);
        //     try {
        //         const objModal = new modalEstadoCivilTenant();
        //         objModal.setDataEnvModal = {
        //             attributes: {
        //                 select: {
        //                     quantity: 1,
        //                     autoReturn: true,
        //                 }
        //             }
        //         }
        //         const response = await objModal.modalOpen();
        //         if (response.refresh) {
        //             if (response.selected) {
        //                 self.#buscarEstadoCivil(response.selected.id);
        //             } else {
        //                 self.#buscarEstadoCivil();
        //             }
        //         }
        //     } catch (error) {
        //         commonFunctions.generateNotificationErrorCatch(error);
        //     } finally {
        //         commonFunctions.simulateLoading(btn, false);
        //     }
        // });

        // $(`#btnOpenEscolaridadeTenant${self._objConfigs.sufixo}`).on('click', async function () {
        //     const btn = $(this);
        //     commonFunctions.simulateLoading(btn);
        //     try {
        //         const objModal = new modalEscolaridadeTenant();
        //         objModal.setDataEnvModal = {
        //             attributes: {
        //                 select: {
        //                     quantity: 1,
        //                     autoReturn: true,
        //                 }
        //             }
        //         }
        //         const response = await objModal.modalOpen();
        //         if (response.refresh) {
        //             if (response.selected) {
        //                 self.#buscarEscolaridade(response.selected.id);
        //             } else {
        //                 self.#buscarEscolaridade();
        //             }
        //         }
        //     } catch (error) {
        //         commonFunctions.generateNotificationErrorCatch(error);
        //     } finally {
        //         commonFunctions.simulateLoading(btn, false);
        //     }
        // });

        // $(`#btnOpenSexoTenant${self._objConfigs.sufixo}`).on('click', async function () {
        //     const btn = $(this);
        //     commonFunctions.simulateLoading(btn);
        //     try {
        //         const objModal = new modalSexoTenant();
        //         objModal.setDataEnvModal = {
        //             attributes: {
        //                 select: {
        //                     quantity: 1,
        //                     autoReturn: true,
        //                 }
        //             }
        //         }
        //         const response = await objModal.modalOpen();
        //         if (response.refresh) {
        //             if (response.selected) {
        //                 self.#buscarSexo(response.selected.id);
        //             } else {
        //                 self.#buscarSexo();
        //             }
        //         }
        //     } catch (error) {
        //         commonFunctions.generateNotificationErrorCatch(error);
        //     } finally {
        //         commonFunctions.simulateLoading(btn, false);
        //     }
        // });
    }

    async addEventosBotoesEspecificoPerfilTipo() {
        const self = this;

        // $(`#openModalConta${self._objConfigs.sufixo}`).on('click', async function () {
        //     const btn = $(this);
        //     commonFunctions.simulateLoading(btn);
        //     try {
        //         const objModal = new modalContaTenant();
        //         objModal.setDataEnvModal = {
        //             attributes: {
        //                 select: {
        //                     quantity: 1,
        //                     autoReturn: true,
        //                 }
        //             }
        //         }
        //         await self._modalHideShow(false);
        //         const response = await objModal.modalOpen();
        //         if (response.refresh) {
        //             if (response.selecteds.length > 0) {
        //                 const item = response.selecteds[0];
        //                 self.#buscarContas(item.id);
        //             } else {
        //                 self.#buscarContas();
        //             }
        //         }
        //     } catch (error) {
        //         commonFunctions.generateNotificationErrorCatch(error);
        //     } finally {
        //         commonFunctions.simulateLoading(btn, false);
        //         await self._modalHideShow();
        //     }
        // });
    }

    async preenchimentoDados(response, options = {}) {
        const self = this;
        const form = $(options.form);

        const responseData = response.data;
        const domains = responseData.domains;

        form.find('input[name="name"]').val(responseData.name);

        if (domains.length) domains.map(domain => { self._inserirDominio(domain); });
    }

    async _inserirDominio(domain) {
        const self = this;
        const divDominio = $(`#divDominio${self._objConfigs.sufixo}`);

        const urlDomain = URLHelper.formatUrlHttp(domain.domain);

        domain.idCol = UUIDHelper.generateUUID();
        const dominioVigente = window.location.hostname == domain.domain;

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
                            ${!dominioVigente ? `<a href="${domain.id ? `${urlDomain}` : '#'}" class="btn btn-outline-primary border-0 btn-sm ${!domain.id ? 'disabled' : ''}" ${domain.id ? `target="_blank"` : ''}>Ir para o domínio</a>` : ''}
                        </div>
                    </div>
                </div>`;

        divDominio.append(strCard);
        self.#addEventosDominio(domain);
        self._objConfigs.data.domainsNaTela.push(domain);

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
            commonFunctions.simulateLoading(btn);
            try {
                const objModalNome = new modalNome();
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
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });
    }


    saveButtonAction() {
        const self = this;
        const formRegistration = $(`#form${self._objConfigs.sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.domains = self._objConfigs.data.domainsNaTela;

        if (self._saveVerifications(data, formRegistration)) {
            self._save(data, `${self._objConfigs.url.base}/update-cliente`, {
                // idRegister: 'current',
            });
        }
        return false;
    }

    _saveVerifications(data, formRegistration) {
        const self = this;
        let blnSave = commonFunctions.verificationData(data.name, { field: formRegistration.find('input[name="name"]'), messageInvalid: 'O campo <b>Nome da Empresa</b> deve ser informado.', setFocus: true });

        return blnSave;
    }
}

$(function () {
    new PageSistemaFormConfiguracoes();
});