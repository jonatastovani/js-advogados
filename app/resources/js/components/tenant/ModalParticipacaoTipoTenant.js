import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalSearchAndFormRegistration } from "../../commons/modal/ModalSearchAndFormRegistration";

export class ModalParticipacaoTipoTenant extends ModalSearchAndFormRegistration {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseParticipacaoTipoTenant,
                urlSearch: `${window.apiRoutes.baseParticipacaoTipoTenant}/consulta-filtros`,
            }
        },
        sufixo: 'ModalParticipacaoTipoTenant',
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        selecteds: [],
    };

    constructor() {
        super({
            idModal: "#ModalParticipacaoTipoTenant",
        });

        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = CommonFunctions.deepMergeObject(this._promisseReturnValue, this.#promisseReturnValue);
        this.setReadyQueueOpen();
    }

    async modalOpen() {
        const self = this;
        if (!self.#verificarConfiguracaoTipo()) {
            return self._returnPromisseResolve();
        }
        await self._modalHideShow();
        self.#addEventosPadrao();
        return await self._modalOpen();
    }

    #verificarConfiguracaoTipo() {
        const self = this;
        if (!self._dataEnvModal.configuracao_tipo) {
            CommonFunctions.generateNotification('Configuração de tipo de participação não informada.', 'error');
            console.error('Configuração de tipo de participação não informada.', self._dataEnvModal);
            return false;
        } else {
            switch (self._dataEnvModal.configuracao_tipo) {
                case window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_GERAL:
                    self._updateModalTitle('Tipo de Participação Geral');
                    break;
                case window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_SERVICO:
                    self._updateModalTitle('Tipo de Participação em Serviços');
                    break;
                case window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_RESSARCIMENTO:
                    self._updateModalTitle('Tipo de Participação em Ressarcimentos');
                    break;
                default:
                    CommonFunctions.generateNotification(`Configuração de tipo de participação <b>${self._dataEnvModal.configuracao_tipo}</b> ainda nao foi implementado.`, 'error');
                    console.error(`Configuração de tipo de participação <b>${self._dataEnvModal.configuracao.tipo}</b> ainda nao foi implementado.`, self._dataEnvModal);
                    return false;
            }
            return true;
        }
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self.getIdModal);

        modal.find('.btn-new-register').on('click', async () => {
            self._updateTitleRegistration('Novo Tipo de Participação');
        });

        self._executarBusca();
        const queueCheck = self._queueCheckDomainCustom;
        if (this._objConfigs?.formRegister && queueCheck) {
            queueCheck.setReady();
        }
    }

    async _executarBusca() {
        const self = this;

        const getAppendDataQuery = () => {
            let appendData = {
                configuracao_tipo: self._dataEnvModal.configuracao_tipo
            };
            return { appendData: appendData };
        }

        self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
        await self._generateQueryFilters(getAppendDataQuery());
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;


        let btns = `
            <li><button type="button" class="dropdown-item fs-6 btn-edit" title="Editar registro ${item.nome}">Editar</button></li>
            <li><button type="button" class="dropdown-item fs-6 btn-delete" title="Excluir registro ${item.nome}">Excluir</button></li>`;

        let btnSelect = '';
        if (self._dataEnvModal?.attributes?.select) {
            btnSelect = `<button type="button" class="btn btn-outline-success btn-sm btn-select" title="Selecionar registro"><i class="bi bi-check-lg"></i></button>`
        }

        let btnsDropDown = `
            <div class="btn-group">
                ${btnSelect}
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle ${btnSelect ? 'rounded-start-0 border' : ''}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu">
                        ${btns}
                    </ul>
                </div>
            </div>`;

        $(tbody).append(`
            <tr id="${item.idTr}" data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${btnsDropDown}
                    </div>
                </td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.nome}">${item.nome}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        return true;
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;

        $(`#${item.idTr}`).find(`.btn-edit`).on('click', async function () {
            CommonFunctions.simulateLoading($(this));
            try {
                self._clearForm();
                self._idRegister = item.id
                const response = await self._getRecurse();
                if (response?.data) {
                    self._action = EnumAction.PUT;
                    const responseData = response.data;
                    self._updateTitleRegistration(`Alterar: <b>${responseData.nome}</b>`);
                    const form = $(self.getIdModal).find('.formRegistration');
                    form.find('input[name="nome"]').val(responseData.nome);
                    form.find('textarea[name="descricao"]').val(responseData.descricao);
                    self._actionsHideShowRegistrationFields(true);
                    self._executeFocusElementOnModal(form.find('input[name="nome"]'));
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading($(this), false);
            }
        });

        $(`#${item.idTr}`).find(`.btn-select`).on('click', async function () {

            if (self._dataEnvModal?.attributes?.select) {
                const select = self._dataEnvModal.attributes.select;
                const promisseReturnValue = self._promisseReturnValue;

                const pushSelected = (item) => {

                    if (select?.quantity && select.quantity == 1) {
                        promisseReturnValue.selected = item;
                    } else {
                        promisseReturnValue.selecteds.push(item);
                    }
                    promisseReturnValue.refresh = true;

                    if (select?.autoReturn && select.autoReturn &&
                        (select?.quantity && promisseReturnValue.selecteds.length == select.quantity ||
                            (select?.quantity && select.quantity == 1 && promisseReturnValue.selected))
                    ) {
                        self._setEndTimer = true;
                    }
                }
                pushSelected(item);
            }
        });

        $(`#${item.idTr}`).find(`.btn-delete`).click(async function () {
            const response = await self._delButtonAction(item.id, item.nome, {
                title: `Exclusão de Tipo de Participação`,
                message: `Confirma a exclusão do Tipo de Participação <b>${item.nome}</b>?`,
                success: `Tipo de Participação excluído com sucesso!`,
                button: this,
                urlApi: self._objConfigs.querys.consultaFiltros.url,
            });
        });
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
        data.configuracao = {
            tipo: self._dataEnvModal.configuracao_tipo
        }

        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.querys.consultaFiltros.url);
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = CommonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: 'O nome do Tipo de Participação deve ser informado.', setFocus: true });
        return blnSave;
    }
}
