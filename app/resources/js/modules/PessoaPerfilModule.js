import { CommonFunctions } from "../commons/CommonFunctions";
import { ModalMessage } from "../components/comum/ModalMessage";
import { ModalSelecionarPessoaPerfilTipo } from "../components/pessoas/ModalSelecionarPessoaPerfilTipo";
import { UUIDHelper } from "../helpers/UUIDHelper";

export class PessoaPerfilModule {

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

        $(`#btnAdicionarPerfil${self._objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalSelecionarPessoaPerfilTipo();
                objModal.setDataEnvModal = {
                    pessoa_tipo_aplicavel: self._objConfigs.data.pessoa_tipo_aplicavel,
                };
                // Se for o form do cadastro da empresa, passa a informação para o modal personalizar a lista de perfis
                self._objConfigs?.formEmpresa ? objModal.setDataEnvModal.formEmpresa = self._objConfigs.formEmpresa : null;

                objModal.setFocusElementWhenClosingModal = btn;
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    await self._inserirPerfil(response.register, true);
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });
    }

    #verificaPerfilAdicionado(perfilAInserir) {
        const self = this;
        const perfisNaTela = self._objConfigs.data.perfisNaTela;

        // Filtra os perfis na tela que possuem o mesmo perfil_tipo_id
        const perfisComMesmoTipo = perfisNaTela.filter(
            perf => perf.perfil_tipo_id == perfilAInserir.perfil_tipo_id
        );

        // Verifica se ultrapassou o limite permitido
        if (perfisComMesmoTipo.length) {
            CommonFunctions.generateNotification(`Este perfil já foi adicionado.`, 'warning');
            return false;
        }
        return true;
    }

    async _inserirPerfil(item, validarLimite = false, perfilObrigatorio = false) {
        const self = this;
        const divPerfil = $(`#divPerfil${self._objConfigs.sufixo}`);

        const nomePerfil = item.perfil_tipo.nome;

        if (!item?.idCol) {
            if (validarLimite) {
                if (!self.#verificaPerfilAdicionado(item)) {
                    return false;
                }
            }

            item.idCol = UUIDHelper.generateUUID();

            const rotaEdit = self.#pesquisarRotaEditPerfil(item);
            const perfilVigente = item.perfil_tipo_id == self._objConfigs.data.pessoa_perfil_tipo_id;

            let strCard = `
            <div id="${item.idCol}" class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center justify-content-between mb-0">
                            <span class="text-truncate spanTitle" title="${item.perfil_tipo.descricao}">${nomePerfil}</span>
                            <div>
                                ${!perfilVigente ? `<a href="${item.id ? `${rotaEdit}/${item.id}` : '#'}" class="btn btn-outline-primary border-0 btn-sm ${!item.id ? 'disabled' : ''}" ${item.id ? `target="_blank"` : ''}>Editar</a>` : ''}
                                ${!item.id && !perfilObrigatorio ? `<button type="button" class="btn btn-outline-danger border-0 btn-sm btn-delete" title="Excluir perfil ${nomePerfil}">Remover</button>` : ''}
                            </div>
                        </h5>
                        ${!item.id ? '<div class="form-text fst-italic">Perfil ainda não cadastrado</div>' : ''}
                    </div>
                </div>
            </div>`;

            divPerfil.append(strCard);
            self.#addEventosPerfil(item);
            self._objConfigs.data.perfisNaTela.push(item);

        } else {
            $(`#${item.idCol}`).find('.spanTitle').html(nomePerfil);

            const indexDoc = self.#pesquisaIndexPerfilNaTela(item);
            if (indexDoc != -1) {
                self._objConfigs.data.perfisNaTela[indexDoc] = item;
            }
        }

        return true;
    }

    #pesquisarRotaEditPerfil(item) {
        const rotas = window.Statics.PessoaPerfilTipoRotasPessoaPerfilFormFront;

        // Encontra o objeto com o perfil_tipo correspondente
        const rota = rotas.find(itemEnum => itemEnum.perfil_tipo == item.perfil_tipo_id);

        // Retorna somente a rota ou null caso não encontre
        return rota ? rota.rota : null;
    }

    #pesquisaIndexPerfilNaTela(item, prop = 'idCol') {
        const self = this;
        return self._objConfigs.data.perfisNaTela.findIndex(doc => doc[prop] === item[prop]);
    }

    async #addEventosPerfil(item) {
        const self = this;

        $(`#${item.idCol}`).find(`.btn-delete`).click(async function () {
            try {
                const perfNaTela = self._objConfigs.data.perfisNaTela;
                const indexPerf = self.#pesquisaIndexPerfilNaTela(item);
                if (indexPerf != -1) {
                    const perf = perfNaTela[indexPerf] = item;

                    const objMessage = new ModalMessage();
                    objMessage.setDataEnvModal = {
                        title: `Remoção de Perfil`,
                        message: `Confirma a remoção do perfil <b>${perf.perfil_tipo.nome}</b>?`,
                    };
                    objMessage.setFocusElementWhenClosingModal = $(this);
                    const result = await objMessage.modalOpen();
                    if (result.confirmResult) {
                        perfNaTela.splice(indexPerf, 1);
                        $(`#${perf.idCol}`).remove();
                    }
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        });
    }

    _retornaPerfilsNaTelaSaveButonAction() {
        const self = this;
        return self._objConfigs.data.perfisNaTela.map(item => {
            return {
                id: item.id,
                perfil_tipo_id: item.perfil_tipo_id,
            }
        });
    }

    _inserirPerfilObrigatorio() {
        const self = this;
        const novoPerfil = {
            perfil_tipo_id: self._objConfigs.data.pessoa_perfil_tipo_id,
            perfil_tipo: window.Details.PessoaPerfilTipoEnum.filter(item =>
                item.id == self._objConfigs.data.pessoa_perfil_tipo_id)[0]
        }
        self._inserirPerfil(novoPerfil, false, true);
    }
}