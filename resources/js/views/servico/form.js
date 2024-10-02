import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import { modalAreaJuridica } from "../../components/referencias/modalAreaJuridica";
import { URLHelper } from "../../helpers/URLHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";

class PageServicoForm {

    #sufixo = 'PageServicoForm';
    #objConfigs = {
        url: {
            base: window.apiRoutes.baseServico,
            baseAreaJuridica: window.apiRoutes.baseAreaJuridica,
        },
        data: {
            pessoasEnvolvidasNaTela: []
        }
    };
    #action;
    #idRegister;

    constructor() {
        this.initEvents();
    }

    initEvents() {
        const self = this;
        self.#addEventosBotoes();
        commonFunctions.addEventsSelect2Api($(`#area_juridica_id${self.#sufixo}`), `${self.#objConfigs.url.baseAreaJuridica}/select2`);
        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self.#idRegister = uuid;
            this.#action = enumAction.PUT;
            // self.#buscarDados();
        } else {
            this.#action = enumAction.POST;
        }
    }

    #addEventosBotoes() {
        const self = this;

        // $('#btnAdicionarEnvolvidos').on('click', async function () {
        //     const btn = $(this);
        //     commonFunctions.simulateLoading(btn);
        //     try {
        //         const objModal = new modalBuscaPessoas();
        //         const response = await objModal.modalOpen();
        //         if (response.refresh && response.selecteds.length > 0) {
        //             console.log(response);
        //             for (let pessoa of response.selecteds) {
        //                 self.#inserirPessoasEnvolvidas(pessoa);
        //             }
        //         }
        //     } catch (error) {
        //         commonFunctions.generateNotificationErrorCatch(error);
        //     } finally {
        //         commonFunctions.simulateLoading(btn, false);
        //     }
        // });

        $('#btnOpenAreaJuridica').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalAreaJuridica();
                objModal.setDataEnvModal = {
                    attributes: {
                        select: {
                            quantity: 1,
                            autoReturn: true,
                        }
                    }
                }
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    if (response.selecteds.length > 0) {
                        const item = response.selecteds[0];
                        commonFunctions.updateSelect2Value($(`#area_juridica_id${self.#sufixo}`), item.nome, item.id);
                    } else {
                        const area_juridica_id = $(`#area_juridica_id${self.#sufixo}`).val();
                        if (area_juridica_id) {
                            const update = await commonFunctions.getRecurseWithTrashed(`${self.#objConfigs.url.baseInfoSubjCategorias}/${area_juridica_id}`);
                            if (update?.data) {
                                commonFunctions.updateSelect2Value($(`#area_juridica_id${self.#sufixo}`), update.data.nome, update.data.id);
                            }
                        }
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnSave${self.#sufixo}`).on('click', async function (e) {
            e.preventDefault();
            self.#saveButtonAction();
        });

        // const openModalTest = () => {
        //     const objCode = new modalBuscaPessoas();
        //     objCode.modalOpen();
        // }
        // // openModalTest();
    }

    // async  #inserirPessoasEnvolvidas(pessoa) {
    //     const self = this;
    //     const divEnvolvidos = $(`#divEnvolvidos${self.#sufixo}Ligacoes`);

    //     const verifica = self.#verificaPessoaEnvolvidaNaTela(pessoa);
    //     if (verifica) {
    //         commonFunctions.generateNotification(`A pessoa <b>${pessoa.nome}</b> já consta como envolvida.`, 'warning');
    //         $(`#${verifica.idCol}`).focus();
    //         return
    //     }

    //     pessoa.idCol = UUIDHelper.generateUUID();
    //     let campoEspecifico = '';
    //     switch (pessoa.pessoa_tipo_tabela_id) {
    //         case 1:
    //             let matricula = pessoa.matricula ? funcoesPresos.retornaMatriculaFormatada(`${pessoa.matricula}${funcoesPresos.retornaDigitoMatricula(pessoa.matricula)}`, 1) : 'Sem Matrícula';
    //             campoEspecifico = `<b>ID Preso:</b> ${pessoa.referencia_id}<br>`;
    //             campoEspecifico += `<b>Matricula:</b> ${matricula}`;
    //             break;

    //         case 2:
    //             campoEspecifico = `<b>ID Pessoa:</b> ${pessoa.referencia_id}<br>`;
    //             campoEspecifico += `<b>CPF: </b> ${commonFunctions.formatCPF(pessoa.cpf)}`;
    //             break;

    //         case 3:
    //             campoEspecifico = `<b>ID Funcionário RH:</b> ${pessoa.referencia_id}<br>`;
    //             campoEspecifico += `<b>CPF:</b> ${commonFunctions.formatCPF(pessoa.cpf)}<br>`;
    //             campoEspecifico += `<b>RS:</b> ${pessoa.rs}`;
    //             break;

    //         default:
    //             commonFunctions.generateNotification(`Tipo de pessoa não reconhecido.`, 'error', { itemsArray: [`Tipo informado: ${pessoa.pessoa.tipo_tabela_id}.`] });
    //             return false;
    //     }

    //     const cpf = pessoa.cpf ? commonFunctions.formatCPF(pessoa.cpf) : '';
    //     let removerPessoa = {};
    //     // Verifica se a pessoa foi criada há menos de 1 hora
    //     if (DateTimeHelper.ehMenosDeXHoras(pessoa.created_at, 1)) {
    //         removerPessoa.btn = `
    //     <button class="btn btn-outline-danger btnRemoverEnvolvido">
    //         <i class="bi bi-trash"></i>
    //         Remover
    //     </button>`;
    //         const novaData = DateTimeHelper.retornaSomaDataEHora(pessoa.created_at, 1, 4);
    //         removerPessoa.message = `<div class="form-text fst-italic mb-0">A associação desta pessoa pode ser removida até ${DateTimeHelper.retornaDadosDataHora(novaData, 12)}.</div>`;
    //     }

    //     let nomeSocial = pessoa.nome_social ? `<b>Nome social:</b> ${pessoa.nome_social}` : '';
    //     let strCard = `
    //         <div id="${pessoa.idCol}" class="col">
    //             <div class="card h-100" style="max-width: 20rem;">
    //                 <div class="card-img-top d-flex justify-content-center p-3 divFotoFrontal">
    //                     <i class="bi bi-image-fill" style="font-size: 12rem; line-height: 2rem"></i>
    //                 </div>
    //                 <div class="card-body d-flex flex-column">
    //                     <h6 class="card-title">${campoEspecifico}</h6>
    //                     <h5 class="card-title">${pessoa.nome}</h5>
    //                     ${nomeSocial}
    //                     <div class="accordion mt-2" id="accordion${pessoa.idCol}">
    //                         <div class="accordion-item">
    //                             <div class="accordion-header">
    //                                 <button class="accordion-button py-1 collapsed" type="button" data-bs-toggle="collapse"
    //                                     data-bs-target="#collapseOne${pessoa.idCol}" aria-expanded="false"
    //                                     aria-controls="collapseOne${pessoa.idCol}">
    //                                     Dados da pessoa
    //                                 </button>
    //                             </div>
    //                             <div id="collapseOne${pessoa.idCol}" class="accordion-collapse collapse"
    //                                 data-bs-parent="#accordion${pessoa.idCol}">
    //                                 <div class="accordion-body">
    //                                     <p class="card-text"><b>Perfil(is): </b>${pessoa.perfis ?? ''}</p>
    //                                     <p class="card-text"><b>Data nasc.: </b>${pessoa.data_nascimento ?? ''}</p>
    //                                     <p class="card-text"><b>Mãe: </b>${pessoa.mae ?? ''}</p>
    //                                     <p class="card-text"><b>Pai: </b>${pessoa.pai ?? ''}</p>
    //                                     <p class="card-text"><b>RG: </b>${pessoa.rg ?? ''}</p>
    //                                     <p class="card-text"><b>CPF: </b>${cpf}</p>
    //                                 </div>
    //                             </div>
    //                         </div>
    //                     </div>
    //                     <div class="row flex-fill">
    //                         <div class="col mt-2 justify-content-end d-flex gap-2">
    //                             <div class="dropdown">
    //                                 <button class="btn btn-secondary dropdown-toggle" type="button"
    //                                     data-bs-toggle="dropdown" aria-expanded="false">
    //                                     Sobre o preso
    //                                 </button>
    //                                 <ul class="dropdown-menu">
    //                                     <li>
    //                                         <a class="dropdown-item"
    //                                             href="http://10.14.5.121/gpu/web/qualificativa/pdf/imprimirQualificativaCompletaPDF.php?idPreso=1191138"
    //                                             target="_blank" rel="noopener noreferrer">
    //                                             Qualificativa Completa
    //                                         </a>
    //                                     </li>
    //                                     <li>
    //                                         <a class="dropdown-item"
    //                                             href="http://10.14.5.121/gpu/web/qualificativa/pdf/imprimirQualificativaPDF.php?idPreso=1191138"
    //                                             target="_blank" rel="noopener noreferrer">
    //                                             Qualificativa Simples
    //                                         </a>
    //                                     </li>
    //                                     <li><a class="dropdown-item" href="#">Something else here</a></li>
    //                                 </ul>
    //                             </div>
    //                             <div>${removerPessoa.btn ?? ''}</div>
    //                         </div>
    //                     </div>
    //                     ${removerPessoa.message ?? ''}
    //                 </div>
    //             </div>
    //         </div>
    //     `;

    //     if (self.#inserirDadosPessoaEnvolvidaNaTela(pessoa)) {
    //         divEnvolvidos.append(strCard);
    //         self.#buscaFotoPessoa(pessoa);
    //         return true;
    //     }
    //     return false;
    // }

    // async #buscaFotoPessoa(pessoa) {
    //     const self = this;

    //     try {
    //         let objDataBusca = {};
    //         switch (pessoa.pessoa_tipo_tabela_id) {
    //             case 1:
    //                 objDataBusca.endpoint = `foto-preso/id/${pessoa.referencia_id}`;
    //                 objDataBusca.alt = "Foto preso";

    //                 break;
    //             case 2:
    //                 objDataBusca.endpoint = `foto-pessoa/id/${pessoa.referencia_id}`;
    //                 objDataBusca.alt = "Foto pessoa";
    //                 // commonFunctions.generateNotification('Rota de busca de foto de pessoas em geral em desenvolvimento', 'warning');
    //                 return false;
    //                 break;
    //             case 3:
    //                 objDataBusca.endpoint = `foto-funcionario/id/${pessoa.referencia_id}`;
    //                 objDataBusca.alt = "Foto funcionário";
    //                 // commonFunctions.generateNotification('Rota de busca de foto de funcionário em desenvolvimento', 'warning');
    //                 return false;
    //             default:
    //                 console.error('Tipo tabela pessoa inválido.', pessoa);
    //                 return false;
    //                 break;
    //         }

    //         const objConnFoto = new connectAjax(`${self.#objConfigs.url.foto}/${objDataBusca.endpoint}`);
    //         const response = await objConnFoto.getRequest();
    //         let strFoto = '';
    //         if (response?.data?.caminhoFoto?.length > 0) {
    //             strFoto = `<img src="${response.data.caminhoFoto[0]}" class="img-thumbnail" alt="${objDataBusca.alt} ${pessoa.nome}" style="max-height: 15rem;">`;
    //             $(`#${pessoa.idCol}`).find('.divFotoFrontal').html(strFoto);
    //         }
    //     } catch (error) {
    //         commonFunctions.generateNotificationErrorCatch(error);
    //     }

    // }

    // /**
    //  * Função para inserir uma pessoa na lista de pessoas envolvidas.
    //  * 
    //  * @param {Object} pessoa - Objeto contendo os dados da pessoa.
    //  */
    // #inserirDadosPessoaEnvolvidaNaTela(pessoa) {
    //     const self = this;

    //     // Insere a pessoa no array `pessoasEnvolvidasNaTela`
    //     self.#objConfigs.data.pessoasEnvolvidasNaTela.push({
    //         id: pessoa.id ?? null,
    //         pessoa_tipo_tabela_id: pessoa.pessoa_tipo_tabela_id,
    //         nome: pessoa.nome,
    //         referencia_id: pessoa.referencia_id,
    //         idCol: pessoa.idCol
    //     });

    //     return true;
    // }

    // /**
    //  * Verifica se a pessoa envolvida já está na tela.
    //  * 
    //  * @param {Object} item - O objeto da pessoa que será verificado.
    //  * @returns {Object|null} - Retorna o elemento correspondente se a pessoa já estiver envolvida, ou null caso contrário.
    //  */
    // #verificaPessoaEnvolvidaNaTela(item) {
    //     const self = this;

    //     for (const element of self.#objConfigs.data.pessoasEnvolvidasNaTela) {
    //         if (element.pessoa_tipo_tabela_id == item.pessoa_tipo_tabela_id && element.referencia_id == item.referencia_id) {
    //             return element; // Pessoa já está envolvida
    //         }
    //     }

    //     return null; // Pessoa não encontrada
    // }

    #saveButtonAction() {
        const self = this;
        const formRegistration = $(`#formServico${self.#sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        // data.pessoas_envolvidas = self.#objConfigs.data.pessoasEnvolvidasNaTela;

        if (self.#saveVerifications(data, formRegistration)) {
            self.#save(data, self.#objConfigs.url.base);
        }
        return false;
    }

    #saveVerifications(data, formRegistration) {
        console.log(data, formRegistration);
        const self = this;
        if (self.#action == enumAction.POST) {
            let blnSave = commonFunctions.verificationData(data.titulo, { field: formRegistration.find('input[name="titulo"]'), messageInvalid: 'O título deve ser informado.', setFocus: true });
            blnSave = commonFunctions.verificationData(data.area_juridica_id, { field: formRegistration.find('select[name="area_juridica_id"]'), messageInvalid: 'A Área Jurídica deve ser selecionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            blnSave = commonFunctions.verificationData(data.descricao, { field: formRegistration.find('textarea[name="descricao"]'), messageInvalid: 'A descrição deve ser preenchida.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            return blnSave;
        }
        return true;
    }

    async #save(data, urlApi, options = {}) {
        const self = this;
        const {
            btnSave = $(`#btnSave${self.#sufixo}`),
        } = options;

        try {
            commonFunctions.simulateLoading(btnSave);
            const obj = new connectAjax(urlApi);
            obj.setAction(self.#action);
            obj.setData(data);
            if (self.#action === enumAction.PUT) {
                obj.setParam(self.#idRegister);
            }
            const response = await obj.envRequest();
            console.log(response);

            if (response) {
                // RedirectHelper.redirectWithUUIDMessage(window.frontRoutes.frontRedirect, 'Dados enviados com sucesso!', 'success');
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            commonFunctions.simulateLoading(btnSave, false);
        };
    }

    // async #buscarDados() {
    //     const self = this;

    //     try {
    //         await commonFunctions.loadingModalDisplay();
    //         const response = await self.#getRecurse();
    //         if (response?.data) {
    //             const responseData = response.data;
    //             const form = $(`#formServico${self.#sufixo}`);
    //             form.find('input[name="titulo"]').val(responseData.titulo).attr('readonly', true);
    //             commonFunctions.updateSelect2Value($(`#area_juridica_id${self.#sufixo}`), responseData.categoria.nome, responseData.area_juridica_id);
    //             $(`#area_juridica_id${self.#sufixo}`).attr('disabled', true);
    //             form.find('textarea[name="descricao"]').val(responseData.descricao).attr('readonly', true);

    //             responseData.pessoas_envolvidas.forEach(envolvido => {
    //                 self.#inserirPessoasEnvolvidas({
    //                     id: envolvido.id,
    //                     referencia_id: envolvido.referencia_id,
    //                     pessoa_tipo_tabela_id: envolvido.pessoa_tipo_tabela_id,
    //                     nome: envolvido.pessoa.nome,
    //                     pai: envolvido.pessoa.pai ?? null,
    //                     mae: envolvido.pessoa.mae ?? null,
    //                     nome_social: envolvido.pessoa.nome_social ?? null,
    //                     matricula: envolvido.pessoa.matricula ?? null,
    //                     cpf: envolvido.pessoa.cpf ?? null,
    //                     rs: envolvido.pessoa.rs ?? null,
    //                     created_at: envolvido.created_at,
    //                 });
    //             });
    //         }
    //     } catch (error) {
    //         commonFunctions.generateNotificationErrorCatch(error);
    //     } finally {
    //         await commonFunctions.loadingModalDisplay(false);
    //     }
    // }

    async #getRecurse(options = {}) {
        const self = this;
        const { idRegister = self.#idRegister,
            urlApi = self.#objConfigs.url.base,
        } = options;

        try {
            const obj = new connectAjax(urlApi);
            obj.setParam(idRegister);
            return await obj.getRequest();
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }
}

$(function () {
    new PageServicoForm();
});