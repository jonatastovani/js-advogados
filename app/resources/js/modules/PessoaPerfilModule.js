import { commonFunctions } from "../commons/commonFunctions";
import { modalPessoaDocumento } from "../components/pessoas/modalPessoaDocumento";
import { modalSelecionarPessoaPerfilTipo } from "../components/pessoas/modalSelecionarPessoaPerfilTipo";
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

        console.log('Vai adicionar Ação')
        $(`#btnAdicionarPerfil${self._objConfigs.sufixo}`).on('click', async function () {
            console.log('Clicando')
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalSelecionarPessoaPerfilTipo();
                objModal.setDataEnvModal = {
                    pessoa_tipo_aplicavel: self._objConfigs.data.pessoa_tipo_aplicavel,
                };
                objModal.setFocusElementWhenClosingModal = btn;
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    await self._inserirPerfil(response.register, true);
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
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
            commonFunctions.generateNotification(`Este perfil já foi adicionado.`, 'warning');
            return false;
        }
        return true;
    }

    async _inserirPerfil(item, validarLimite = false) {
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

            let strCard = `
            <div id="${item.idCol}" class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center justify-content-between mb-0">
                            <span class="text-truncate spanTitle">${nomePerfil}</span>
                            <div>
                                <a href="${item.id ? `${rotaEdit}/${item.id}` : '#'}" class="btn btn-outline-primary border-0 btn-sm ${!item.id ? 'disabled' : ''}">Editar</a>
                            </div>
                        </h5>
                        ${!item.id ? '<div class="form-text fst-italic">Perfil ainda não cadastrado</div>' : ''}
                    </div>
                </div>
            </div>`;

            divPerfil.append(strCard);
            // self.#addEventosPerfil(item);
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

        $(`#${item.idCol}`).find('.btn-edit').on('click', async function () {
            const docNaTela = self._objConfigs.data.perfisNaTela;
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const indexDoc = self.#pesquisaIndexPerfilNaTela(item);
                if (indexDoc != -1) {
                    const doc = docNaTela[indexDoc];

                    const objModal = new modalPessoaDocumento();
                    objModal.setDataEnvModal = {
                        register: doc,
                    };
                    const response = await objModal.modalOpen();
                    if (response.refresh && response.register) {
                        await self._inserirPerfil(response.register);
                    }
                } else {
                    console.error('Documento na tela não encontrado. Docs:', docNaTela);
                    console.error('Item buscado:', item);
                    commonFunctions.generateNotification('Documento na tela não encontrado.', 'error');
                    return false;
                }

            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        // $(`#${item.idCol}`).find(`.btn-delete`).click(async function () {
        //     try {
        //         const docNaTela = self._objConfigs.data.perfisNaTela;
        //         const indexDoc = self.#pesquisaIndexPerfilNaTela(item);
        //         if (indexDoc != -1) {
        //             const doc = docNaTela[indexDoc] = item;

        //             const objMessage = new modalMessage();
        //             objMessage.setDataEnvModal = {
        //                 title: `Exclusão de Documento`,
        //                 message: `Confirma a exclusão do documento <b>${doc.perfil_tipo_tenant.nome}</b>?`,
        //             };
        //             objMessage.setFocusElementWhenClosingModal = $(this);
        //             const result = await objMessage.modalOpen();
        //             if (result.confirmResult) {
        //                 docNaTela.splice(indexDoc, 1);
        //                 $(`#${doc.idCol}`).remove();
        //             }
        //         }
        //     } catch (error) {
        //         commonFunctions.generateNotificationErrorCatch(error);
        //     }
        // });
    }

    _retornaDocumentosNaTelaSaveButonAction() {
        const self = this;
        return self._objConfigs.data.perfisNaTela.map(item => {
            return {
                id: item.id,
                perfil_tipo: item.perfil_tipo,
                numero: item.numero,
                // campos_adicionais: item.campos_adicionais,
            }
        });
    }

    _tratarValoresNulos(data) {
        return Object.fromEntries(
            Object.entries(data).map(([key, value]) => {
                if (value === "null") {
                    value = null;
                }
                return [key, value];
            })
        );
    }
}