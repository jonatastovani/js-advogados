import { CommonFunctions } from "../../../commons/CommonFunctions";
import { EnumAction } from "../../../commons/EnumAction";
import { TemplateForm } from "../../../commons/templates/TemplateForm";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";
import { DocumentoModeloQuillEditorModule } from "../../../modules/DocumentoModeloQuillEditorModule";
import { QueueManager } from "../../../utils/QueueManager";

class PageDocumentoModeloForm extends TemplateForm {

    #quillQueueManager;

    constructor() {

        const objConfigs = {
            url: {
                base: window.apiRoutes.baseDocumentoModeloTenant,
                baseDocumentoModeloTipo: window.apiRoutes.baseDocumentoModeloTipo,
                baseDocumentoModeloTenantHelper: window.apiRoutes.baseDocumentoModeloTenantHelper,
            },
            sufixo: 'PageDocumentoModeloForm',
            data: {},
        };

        super({
            objConfigs: objConfigs
        });

        this.#quillQueueManager = new QueueManager();  // Cria a fila

        this.initEvents();
    }

    async initEvents() {
        const self = this;

        await self.#addEventosBotoes();

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self._idRegister = uuid;
            this._action = EnumAction.PUT;
            await self._buscarDados();
        } else {
            this._action = EnumAction.POST;
        }
    }

    async #addEventosBotoes() {
        const self = this;

        self._classQuillEditor = new DocumentoModeloQuillEditorModule(self, {
            quillEditor: {
                selector: `#conteudo${self.getSufixo}`,
                options: { exclude: ['image', 'scriptSub', 'scriptSuper', 'code', 'link'] }
            },
            objConfigs: self._objConfigs
        });
        self.#quillQueueManager.setReady();  // Informa que o quill está pronto

        const modeloId = URLHelper.getURLSegment({ afterSegment: 'modelo' });
        await self._classQuillEditor.addObjetosModelo(modeloId);

        // Captura qualquer alteração no texto do editor
        self._classQuillEditor.getQuill.on('text-change', async function (delta, oldDelta, source) {
            // Se foi uma entrada manual do usuário (não via API)
            if (source === 'user') {
                await self._classQuillEditor._verificarInconsistenciasObjetos();
            }
        });

        // Captura mudanças na seleção do cursor
        self._classQuillEditor.getQuill.on('selection-change', async function (range, oldRange, source) {
            await self._classQuillEditor._verificarInconsistenciasObjetos();
        });
    }

    async preenchimentoDados(response, options) {
        const self = this;

        const responseData = response.data;
        $(`#nome${self.getSufixo}`).val(responseData.nome);
        $(`#descricao${self.getSufixo}`).val(responseData.descricao);

        self.#quillQueueManager.enqueue(() => {
            responseData.objetos.map(item => {
                let newObjeto = self._classQuillEditor._getObjetoBase(item.identificador);
                if (!newObjeto) {
                    CommonFunctions.generateNotification(`Objeto ${item.identificador} não encontrado como objeto base.`, 'warning');
                    return;
                }
                self._classQuillEditor._inserirObjetoNaTela(CommonFunctions.deepMergeObject(newObjeto, item));
            })

            self._classQuillEditor.getQuill.setContents(responseData.conteudo);
            self._classQuillEditor._verificarInconsistenciasObjetos();
        });
    }

    async saveButtonAction() {
        const self = this;
        let data = {
            nome: $(`#nome${self.getSufixo}`).val(),
            descricao: $(`#descricao${self.getSufixo}`).val(),
            conteudo: JSON.stringify(self._classQuillEditor.getQuill.getContents()),
            objetos: self._classQuillEditor._getObjetosNaTela(),
            documento_modelo_tipo_id: self._classQuillEditor.getDocumentoModeloTipoId,
        }

        try {
            CommonFunctions.simulateLoading($(`#btnSave${this._objConfigs.sufixo}`));

            if (await self.#saveVerifications(data)) {
                await self._save(data, self._objConfigs.url.base, {
                    success: 'Modelo cadastrado com sucesso!',
                });
            }

        } finally {
            CommonFunctions.simulateLoading($(`#btnSave${this._objConfigs.sufixo}`), false);
        }

        return false;
    }

    async #saveVerifications(data) {
        const self = this;
        let blnSave = true;

        const resultado = await self._classQuillEditor._verificarInconsistenciasObjetos();
        if (!resultado) {

            CommonFunctions.generateNotification('A verificação de inconsistência falhou. Tente novamente e se o problema persistir, contate o suporte.', 'warning');
            blnSave = false;
        } else {

            if (resultado.objetos_nao_utilizados.length > 0 || resultado.marcacoes_sem_referencia.length > 0) {
                CommonFunctions.generateNotification('Há marcações sem referência ou objetos não utilizados no modelo. Verifique na aba de revisão.', 'warning');
                blnSave = false;
            }
        }

        blnSave = CommonFunctions.verificationData(data.nome, { field: $(`#nome${self.getSufixo}`), messageInvalid: 'O nome do modelo deve ser informado.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });

        return blnSave;

    }


}

$(function () {
    new PageDocumentoModeloForm();
});