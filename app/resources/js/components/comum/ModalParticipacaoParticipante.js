import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";
import { ModalParticipacaoTipoTenant } from "../tenant/ModalParticipacaoTipoTenant";

export class ModalParticipacaoParticipante extends ModalRegistrationAndEditing {

    #dataEnvModal = {
        idRegister: undefined,
        pagamento_tipo_tenant_id: undefined
    }

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            baseParticipacaoTipo: window.apiRoutes.baseParticipacaoTipoTenant,
        },
        sufixo: 'ModalParticipacaoParticipante',
        data: {
            porcentagem_livre: 0,
        },
        valor_tipo_permitido: ['porcentagem', 'valor_fixo'],
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        refresh: false,
    };

    constructor() {
        super({
            idModal: "#ModalParticipacaoParticipante",
        });

        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = CommonFunctions.deepMergeObject(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = CommonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        this._action = EnumAction.POST;
    }

    set setValorTipoPermitido(tipo) {
        this._objConfigs.valor_tipo_permitido = tipo;
    }

    get getValorTipoPermitido() {
        return this._objConfigs.valor_tipo_permitido;
    }

    async modalOpen() {
        const self = this;

        try {
            await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando informações da participação...' });

            self.#addEventosPadrao();

            if (!self._dataEnvModal.dados_participacao.participacao_registro_tipo_id) {
                throw new Error('Tipo de registro de participação não informado.', 'error');
            }

            await self.#buscarTipoParticipacaoTenant();
            await self.#preencherDados();

        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return await self._returnPromisseResolve();
        } finally {
            await CommonFunctions.loadingModalDisplay(false);
        }

        await self._modalHideShow();
        self._executeFocusElementOnModal($(self.getIdModal).find('select[name="participacao_tipo_id"]'));
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        self.#configuraValorTipoPermitido();

        modal.find('.btnOpenModalTipoParticipacao').on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalParticipacaoTipoTenant();
                objModal.setDataEnvModal = {
                    attributes: {
                        select: {
                            quantity: 1,
                            autoReturn: true,
                        }
                    },
                    configuracao_tipo: self._dataEnvModal.configuracao_tipo,
                }
                await self._modalHideShow(false);
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    if (response.selected) {
                        self.#buscarTipoParticipacaoTenant(response.selected.id);
                    } else {
                        self.#buscarTipoParticipacaoTenant();
                    }
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
                await self._modalHideShow();
            }
        });

        const valor = modal.find('input[name="valor"]');
        modal.find('.btnAplicarRestante').on('click', async function () {
            const porcentagem_livre = self._objConfigs.data.porcentagem_livre;
            if (porcentagem_livre == 100) {
                valor.val(porcentagem_livre);
            } else if (porcentagem_livre > 0) {
                valor.val(CommonFunctions.formatWithCurrencyCommasOrFraction(porcentagem_livre));
            }
        });
    }

    /**
     * Configura os tipos de valor permitidos nos elementos de rádio.
     * - Se apenas um tipo for permitido, desabilita o outro.
     * - Se ambos forem permitidos, mantém ambos habilitados e seleciona "porcentagem".
     */
    #configuraValorTipoPermitido() {
        const self = this;
        const modal = $(self._idModal);
        const tiposPermitidos = self.getValorTipoPermitido;

        // Valida se há tipos permitidos
        if (tiposPermitidos === undefined || !tiposPermitidos.length) {
            throw new Error('Nenhum tipo de valor permitido foi informado para a participação.');
        }

        // Seletores para os elementos derádio
        const $radioPorcentagem = $(`#rbPorcentagem${self.getSufixo}`);
        const $radioValorFixo = $(`#rbValorFixo${self.getSufixo}`);

        // Garante que os elementos existem antes de manipular
        if (!$radioPorcentagem.length || !$radioValorFixo.length) {
            throw new Error('Os elementos de rádio não foram encontrados.');
        }

        const aplicarMascaraPorcentagem = (campo) => {
            const regexPorcentagem = /^(100|[0-9]{1,2}(,\d{0,2})?)$/;
            campo.on('input', function () {
                if (!regexPorcentagem.test($(this).val())) {
                    $(this).val('');
                }
            });
        }

        const visibilidadeDadosPorcentagem = (status = true) => {
            if (status == true) {
                modal.find('.divTextPorcentagemLivre, .btnAplicarRestante').show('fast');
            } else {
                modal.find('.divTextPorcentagemLivre, .btnAplicarRestante').hide('fast');
            }
        }

        const valor = modal.find('input[name="valor"]');

        const executarAcoesPorcentagem = () => {
            aplicarMascaraPorcentagem(valor);
            visibilidadeDadosPorcentagem(true);
            self._objConfigs.data.valor_tipo = 'porcentagem';
        }

        const executarAcoesValorFixo = () => {
            CommonFunctions.applyCustomNumberMask(valor, { format: '#.##0,00', reverse: true });
            visibilidadeDadosPorcentagem(false);
            self._objConfigs.data.valor_tipo = 'valor_fixo';
        }

        if (tiposPermitidos.length > 1) {
            modal.find('input[name="valor_tipo"]').on('click', async function () {
                valor.off('input');
                valor.val('');
                valor.unmask();

                if ($(this).val() == 'porcentagem') {
                    executarAcoesPorcentagem();
                } else {
                    executarAcoesValorFixo();
                }
            });
        }

        // Configura os estados dos elementos com base nos tipos permitidos
        if (tiposPermitidos.includes('porcentagem') || (tiposPermitidos.includes('porcentagem') && tiposPermitidos.includes('valor_fixo'))) {
            // Ambos permitidos: habilita ambos e seleciona "porcentagem"
            // Ou Apenas "porcentagem" permitido
            $radioPorcentagem.prop('disabled', false).prop('checked', true);
            $radioValorFixo.prop('disabled', false);
            executarAcoesPorcentagem();
        } else if (tiposPermitidos.includes('valor_fixo')) {
            // Apenas "valor_fixo" permitido
            $radioPorcentagem.prop('disabled', true);
            $radioValorFixo.prop('disabled', false).prop('checked', true);
            executarAcoesValorFixo();
        }
    }

    async #preencherDados() {
        const self = this;

        const modal = $(self.getIdModal);
        const dados = self._dataEnvModal.dados_participacao;
        let nome = '';

        switch (dados.participacao_registro_tipo_id) {
            case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:
                modal.find('.lblTipoParticipante').html('Pessoa');

                switch (dados.referencia.pessoa.pessoa_dados_type) {
                    case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                        nome = dados.referencia.pessoa.pessoa_dados.nome;
                        break;
                    case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                        nome = dados.referencia.pessoa.pessoa_dados.nome_fantasia;
                        break;

                    default:
                        break;
                }
                break;

            case window.Enums.ParticipacaoRegistroTipoEnum.GRUPO:
                modal.find('.lblTipoParticipante').html('Grupo');
                nome = dados.nome_grupo;
                break;
        }

        const ocupada = self._dataEnvModal.porcentagem_ocupada ?? 0;
        const livre = (100 - ocupada);
        let valor = dados.valor ?? 0;

        if (dados.valor_tipo == 'valor_fixo' || dados.valor_tipo == 'porcentagem' && dados.valor < 100) {
            valor = CommonFunctions.formatWithCurrencyCommasOrFraction(dados.valor ?? 0);
        }

        if (!livre) {
            modal.find('.btnAplicarRestante').attr('disabled', true);
        } else {
            modal.find('.btnAplicarRestante').removeAttr('disabled');
            self._objConfigs.data.porcentagem_livre = livre;
        }

        modal.find('.lblNome').html(nome);
        modal.find('.lblPorcentagemLivre').html(CommonFunctions.formatWithCurrencyCommasOrFraction(livre));
        if (dados.valor_tipo) {
            modal.find(`input[name="valor_tipo"][value="${dados.valor_tipo}"]`).prop('checked', true).trigger('click');
        }
        modal.find('input[name="valor"]').val(valor).trigger('input');
        modal.find('input[name="observacao"]').val(dados.observacao ?? '');
        modal.find('select[name="participacao_tipo_id"]').val(dados.participacao_tipo_id ?? 0);
    }

    async #buscarTipoParticipacaoTenant(selected_id = null) {
        const self = this;
        let options = {
            typeRequest: EnumAction.POST,
            envData: {
                configuracao_tipo: self._dataEnvModal.configuracao_tipo,
            },
            outInstanceParentBln: true
        }
        selected_id ? options.selectedIdOption = selected_id : null;
        const select = $(self.getIdModal).find('select[name="participacao_tipo_id"]');
        await CommonFunctions.fillSelect(select, `${self._objConfigs.url.baseParticipacaoTipo}/index-configuracao-tipo`, options);
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
        data.valor_tipo = self._objConfigs.data.valor_tipo;

        if (self.#saveVerifications(data)) {
            self._promisseReturnValue.register = data;
            self._promisseReturnValue.refresh = true;
            self._endTimer = true;
        }
    }

    #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');

        let blnSave = CommonFunctions.verificationData(data.participacao_tipo_id, { field: formRegistration.find('select[name="participacao_tipo_id"]'), messageInvalid: 'O <b>tipo de participação</b> deve ser informado.', setFocus: true });

        data.valor = CommonFunctions.removeCommasFromCurrencyOrFraction(data.valor);

        if (data.valor > 0) {
            if (data.valor_tipo == 'porcentagem' && data.valor > self._objConfigs.data.porcentagem_livre) {
                CommonFunctions.generateNotification('O <b>valor da participação</b> ultrapassa o valor da porcentagem livre.', 'warning');
                if (blnSave === true) {
                    self._executeFocusElementOnModal(formRegistration.find('input[name="valor"]'));
                }
                blnSave = false;
            }
        } else {
            CommonFunctions.generateNotification('O <b>valor da participação</b> deve ser informado.', 'warning');
            if (blnSave === true) {
                self._executeFocusElementOnModal(formRegistration.find('input[name="valor"]'));
            }
            blnSave = false;
        }

        return blnSave;
    }

}
