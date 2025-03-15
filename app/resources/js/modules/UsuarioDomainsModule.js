import { CommonFunctions } from "../commons/CommonFunctions";
import { ModalMessage } from "../components/comum/ModalMessage";
import { ModalSelecionarUsuarioDomains } from "../components/pessoas/ModalSelecionarUsuarioDomains";
import { UUIDHelper } from "../helpers/UUIDHelper";

export class UsuarioDomainsModule {

    _objConfigs;
    _parentInstance;
    _extraConfigs;

    constructor(parentInstance, objData) {
        this._objConfigs = objData.objConfigs;
        this._parentInstance = parentInstance;
        this._extraConfigs = objData.extraConfigs;
        this.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#btnAdicionarDominio${self._objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalSelecionarUsuarioDomains();
                objModal.setFocusElementWhenClosingModal = btn;
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    await self._inserirDominio(response.register, true);
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });
    }

    #verificaDominioAdicionado(dominioAInserir) {
        const self = this;
        const dominiosNaTela = self._objConfigs.data.dominiosNaTela;

        // Filtra os dominios na tela que possuem o mesmo id
        const dominiosComMesmoId = dominiosNaTela.filter(
            dominio => dominio.domain_id == dominioAInserir.domain_id
        );

        // Verifica se ultrapassou o limite permitido
        if (dominiosComMesmoId.length) {
            CommonFunctions.generateNotification(`Este domínio já foi adicionado.`, 'warning');
            return false;
        }
        return true;
    }

    async _inserirDominio(item, validarLimite = false, dominioObrigatorio = false) {
        const self = this;
        const divDominio = $(`#divDominio${self._objConfigs.sufixo}`);

        const nomeDominio = item.domain.name;
        const urlDominio = item.domain.domain;

        if (validarLimite) {
            if (!self.#verificaDominioAdicionado(item)) {
                return false;
            }
        }

        item.idCol = UUIDHelper.generateUUID();
        const dominioVigente = window.location.hostname == urlDominio;

        let strCard = `
            <div id="${item.idCol}" class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center justify-content-between mb-0">
                            <span class="text-truncate spanTitle">${nomeDominio}</span>
                            <div>
                                ${!dominioObrigatorio ? `<button type="button" class="btn btn-outline-danger border-0 btn-sm btn-delete" title="Remover acesso ao domínio ${urlDominio}">Remover</button>` : ''}
                            </div>
                        </h5>
                        <p class="text-truncate mb-0 spanDominio">${urlDominio}</p>
                        ${!item.id ? '<div class="form-text fst-italic">Acesso ainda não cadastrado</div>' : `${!dominioVigente ? `<a href="${item.id ? `http://${urlDominio}` : '#'}" class="btn btn-outline-primary border-0 btn-sm ${!item.id ? 'disabled' : ''}" ${item.id ? `target="_blank"` : ''}>Ir para o domínio</a>` : ''}`}
                    </div>
                </div>
            </div>`;

        divDominio.append(strCard);
        self.#addEventosDominio(item);
        self._objConfigs.data.dominiosNaTela.push(item);

        return true;
    }

    #pesquisaIndexDominioNaTela(item, prop = 'idCol') {
        const self = this;
        return self._objConfigs.data.dominiosNaTela.findIndex(doc => doc[prop] === item[prop]);
    }

    async #addEventosDominio(item) {
        const self = this;

        $(`#${item.idCol}`).find(`.btn-delete`).click(async function () {
            try {
                const dominioNaTela = self._objConfigs.data.dominiosNaTela;
                const indexDominio = self.#pesquisaIndexDominioNaTela(item);
                if (indexDominio != -1) {
                    const dominio = dominioNaTela[indexDominio] = item;

                    const objMessage = new ModalMessage();
                    objMessage.setDataEnvModal = {
                        title: `Remoção de Domínio`,
                        message: `Confirma a remoção do acesso ao domínio <b>${dominio.domain.domain}</b>?`,
                    };
                    objMessage.setFocusElementWhenClosingModal = $(this);
                    const result = await objMessage.modalOpen();
                    if (result.confirmResult) {
                        dominioNaTela.splice(indexDominio, 1);
                        $(`#${dominio.idCol}`).remove();
                    }
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        });
    }

    _retornaDominiosNaTelaSaveButonAction() {
        const self = this;
        return self._objConfigs.data.dominiosNaTela.map(item => {
            return {
                id: item.id,
                domain_id: item.domain_id,
            }
        });
    }

    _inserirDominioObrigatorio() {
        const self = this;
        return;
        const novoDominio = {
            domain_id: self._objConfigs.data.pessoa_domain_id,
            domain: window.Details.UsuarioDomainsEnum.filter(item =>
                item.id == self._objConfigs.data.pessoa_domain_id)[0]
        }
        self._inserirDominio(novoDominio, false, true);
    }
}