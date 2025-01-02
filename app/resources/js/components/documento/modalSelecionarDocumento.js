import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { RedirectHelper } from "../../helpers/RedirectHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";

export class modalSelecionarDocumento extends modalRegistrationAndEditing {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: undefined,
            baseMovimentacaoConta: window.apiRoutes.baseMovimentacaoConta,
            baseFrontDocumentoGeradoImpressao: window.frontRoutes.baseFrontDocumentoGeradoImpressao,
        },
        sufixo: 'ModalSelecionarDocumento',
    };

    #dataEnvModal = {
        idRegister: undefined,
    }

    #promisseReturnValue = {
        selected: undefined,
    }

    constructor() {
        super({
            idModal: "#modalSelecionarDocumento",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
    }

    async modalOpen() {
        const self = this;

        if (!self._dataEnvModal.idRegister) {
            commonFunctions.generateNotification('ID da movimentação não informado.', 'error');
            return await self._returnPromisseResolve();
        }

        self._objConfigs.url.base = `${self._objConfigs.url.baseMovimentacaoConta}/${self._dataEnvModal.idRegister}/documento-gerado`;

        if (!await self.#buscarDados()) {
            return await self._returnPromisseResolve();
        }
        await self._modalHideShow();
        return await self._modalOpen();
    }

    _modalReset() {
        const self = this;
        const modal = $(self.getIdModal);
        const formRegistration = modal.find('.formRegistration');
        formRegistration.find('.rowButtons').html('');
        formRegistration[0].reset();
    }

    async #buscarDados() {
        const self = this;

        await commonFunctions.loadingModalDisplay();
        try {
            self._modalReset();
            const objConn = new connectAjax(self._objConfigs.url.base);
            const response = await objConn.getRequest();
            if (response?.data) {
                if (!response.data.length) {
                    commonFunctions.generateNotification('Nenhum documento encontrado.', 'info');
                    return false;
                }
                response.data.map(documento => {
                    self.#inserirOpcao(documento);
                });

                return true;
            }
            return false;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }

    async #inserirOpcao(documento) {
        const self = this;
        const rowButtons = $(self.getIdModal).find('.rowButtons');

        let dadosEspecifico = ''
        const pessoa = documento.dados.dados_participacao[0].referencia.pessoa;
        switch (documento.documento_gerado_tipo_id) {

            case window.Enums.DocumentoGeradoTipoEnum.REPASSE_PARCEIRO:

                // Nome do parceiro
                switch (pessoa.pessoa_dados_type) {

                    case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                        dadosEspecifico = pessoa.pessoa_dados.nome;
                        break;

                    case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                        dadosEspecifico = pessoa.pessoa_dados.nome_fantasia;
                        break;

                    default:
                        break;
                }

                // Valor repasse
                if (documento.dados.movimentacao_repasse.length) {
                    const somaValorMovimentado = documento.dados.movimentacao_repasse.reduce((total, item) => {
                        return total + item.valor_movimentado;
                    }, 0);

                    dadosEspecifico += ` - Total: ${commonFunctions.formatNumberToCurrency(somaValorMovimentado)}`;
                }


                break;

            default:
                throw new Error("Tipo de documento não configurado");
        }

        documento.idButton = UUIDHelper.generateUUID();
        const strButton = `
            <div id="${documento.idButton}" class="col">
                <button type="button" class="btn btn-dark w-100 border-0">
                    <h5 class="card-title">${documento.documento_gerado_tipo.nome}</h5>
                    <p class="card-text">${dadosEspecifico}</p>
                </button>
            </div>`;

        rowButtons.append(strButton);

        self.#addEventosBotoes(documento);
        return documento;
    }

    #addEventosBotoes(documento) {
        const self = this;
        const modal = $(self.getIdModal);

        modal.find(`#${documento.idButton}`).on('click', async function () {
            RedirectHelper.openURLWithParams(`${self._objConfigs.url.baseFrontDocumentoGeradoImpressao}/${documento.id}`);
            // delete documento.idButton;
            // self._promisseReturnValue.selected = documento;
            // self._promisseReturnValue.refresh = true;
            // self._endTimer = true;
        });
    }

}