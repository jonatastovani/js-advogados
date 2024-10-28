import { commonFunctions } from "../../../commons/commonFunctions";
import { connectAjax } from "../../../commons/connectAjax";
import { enumAction } from "../../../commons/enumAction";
import { modalMessage } from "../../../components/comum/modalMessage";
import { modalNome } from "../../../components/comum/modalNome";
import { modalPessoa } from "../../../components/pessoas/modalPessoa";
import { modalSelecionarPerfil } from "../../../components/pessoas/modalSelecionarPerfil";
import { modalServicoParticipacao } from "../../../components/servico/modalServicoParticipacao";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class PageServicoParticipacaoPresetForm {

    #sufixo = 'PageServicoParticipacaoPresetForm';
    #objConfigs = {
        url: {
            base: window.apiRoutes.baseParticipacaoPreset,
            baseParticipacaoTipo: window.apiRoutes.baseServicoParticipacaoTipoTenant,
        },
        data: {
            porcentagemOcupada: 0,
            participantesNaTela: []
        }
    };
    #action;
    #idRegister;

    constructor() {
        this.initEvents();
    }

    initEvents() {
        const self = this;

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self.#idRegister = uuid;
            this.#action = enumAction.PUT;
            self.#buscarDados();
        } else {
            this.#action = enumAction.POST;
        }

        self.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        const openModalServicoParticipacao = async (dados_participacao) => {
            const objModal = new modalServicoParticipacao();
            objModal.setDataEnvModal = {
                dados_participacao: dados_participacao,
                porcentagem_ocupada: self.#objConfigs.data.porcentagem_ocupada,
            }
            const response = await objModal.modalOpen();
            if (response.refresh) {
                await self.#inserirParticipanteNaTela(Object.assign(dados_participacao, response.register));
            }
        }

        $(`#btnInserirPessoa${self.#sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const dataEnvModalAppend = {
                    perfis_busca: window.Statics.PerfisPermitidoParticipacaoServico,
                };
                const objModal = new modalPessoa({ dataEnvModal: dataEnvModalAppend });
                const response = await objModal.modalOpen();
                if (response.refresh && response.selected) {
                    await openModalServicoParticipacao({
                        participacao_registro_tipo_id: window.Enums.ParticipacaoRegistroTipoEnum.PERFIL, referencia: response.selected,
                        referencia_id: response.selected.id,
                    });
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnInserirGrupo${self.#sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModalNome = new modalNome();
                objModalNome.setDataEnvModal = {
                    title: 'Novo grupo',
                    mensagem: 'Informe o nome do grupo',
                }
                const response = await objModalNome.modalOpen();
                if (response.refresh) {
                    await openModalServicoParticipacao({
                        participacao_registro_tipo_id: window.Enums.ParticipacaoRegistroTipoEnum.GRUPO,
                        nome_grupo: response.name
                    });
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

        const evento = async () => {
            // self.#inserirParticipanteNaTela(
            //     {
            //         "participacao_registro_tipo_id": 1,
            //         "referencia": {
            //             "id": "9d567bb9-9ca5-43ee-af38-28ed88be646b",
            //             "tenant_id": "jsadvogados",
            //             "pessoa_id": "9d567bb9-9a0e-4e24-8d8b-0c92c801cd9b",
            //             "perfil_tipo_id": 2,
            //             "observacao": null,
            //             "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //             "created_ip": "127.0.0.1",
            //             "created_at": "2024-10-26 10:01:25",
            //             "updated_user_id": null,
            //             "updated_ip": null,
            //             "updated_at": null,
            //             "deleted_user_id": null,
            //             "deleted_ip": null,
            //             "deleted_at": null,
            //             "perfil_tipo": {
            //                 "id": 2,
            //                 "nome": "Parceiro",
            //                 "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
            //                 "tabela_ref": null,
            //                 "tabela_model": null,
            //                 "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                 "created_ip": "127.0.0.1",
            //                 "created_at": "2024-10-26 10:01:22",
            //                 "updated_user_id": null,
            //                 "updated_ip": null,
            //                 "updated_at": null,
            //                 "deleted_user_id": null,
            //                 "deleted_ip": null,
            //                 "deleted_at": null
            //             },
            //             "pessoa": {
            //                 "id": "9d567bb9-9a0e-4e24-8d8b-0c92c801cd9b",
            //                 "tenant_id": "jsadvogados",
            //                 "pessoa_dados_type": "App\\Models\\Pessoa\\PessoaFisica",
            //                 "pessoa_dados_id": "9d567bb9-9733-4c3b-b72c-aff3ab976af0",
            //                 "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                 "created_ip": "127.0.0.1",
            //                 "created_at": "2024-10-26 10:01:25",
            //                 "updated_user_id": null,
            //                 "updated_ip": null,
            //                 "updated_at": null,
            //                 "deleted_user_id": null,
            //                 "deleted_ip": null,
            //                 "deleted_at": null,
            //                 "pessoa_perfil": [
            //                     {
            //                         "id": "9d567bb9-9ca5-43ee-af38-28ed88be646b",
            //                         "tenant_id": "jsadvogados",
            //                         "pessoa_id": "9d567bb9-9a0e-4e24-8d8b-0c92c801cd9b",
            //                         "perfil_tipo_id": 2,
            //                         "observacao": null,
            //                         "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                         "created_ip": "127.0.0.1",
            //                         "created_at": "2024-10-26 10:01:25",
            //                         "updated_user_id": null,
            //                         "updated_ip": null,
            //                         "updated_at": null,
            //                         "deleted_user_id": null,
            //                         "deleted_ip": null,
            //                         "deleted_at": null,
            //                         "perfil_tipo": {
            //                             "id": 2,
            //                             "nome": "Parceiro",
            //                             "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
            //                             "tabela_ref": null,
            //                             "tabela_model": null,
            //                             "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                             "created_ip": "127.0.0.1",
            //                             "created_at": "2024-10-26 10:01:22",
            //                             "updated_user_id": null,
            //                             "updated_ip": null,
            //                             "updated_at": null,
            //                             "deleted_user_id": null,
            //                             "deleted_ip": null,
            //                             "deleted_at": null
            //                         }
            //                     },
            //                     {
            //                         "id": "9d567bb9-9ed6-4725-889d-d585e2913b61",
            //                         "tenant_id": "jsadvogados",
            //                         "pessoa_id": "9d567bb9-9a0e-4e24-8d8b-0c92c801cd9b",
            //                         "perfil_tipo_id": 3,
            //                         "observacao": null,
            //                         "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                         "created_ip": "127.0.0.1",
            //                         "created_at": "2024-10-26 10:01:25",
            //                         "updated_user_id": null,
            //                         "updated_ip": null,
            //                         "updated_at": null,
            //                         "deleted_user_id": null,
            //                         "deleted_ip": null,
            //                         "deleted_at": null,
            //                         "perfil_tipo": {
            //                             "id": 3,
            //                             "nome": "Cliente",
            //                             "descricao": "Perfil para clientes.",
            //                             "tabela_ref": null,
            //                             "tabela_model": null,
            //                             "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                             "created_ip": "127.0.0.1",
            //                             "created_at": "2024-10-26 10:01:22",
            //                             "updated_user_id": null,
            //                             "updated_ip": null,
            //                             "updated_at": null,
            //                             "deleted_user_id": null,
            //                             "deleted_ip": null,
            //                             "deleted_at": null
            //                         }
            //                     }
            //                 ],
            //                 "pessoa_dados": {
            //                     "id": "9d567bb9-9733-4c3b-b72c-aff3ab976af0",
            //                     "tenant_id": "jsadvogados",
            //                     "nome": "Luana Chaves Santos Neto",
            //                     "mae": "Pérola Camacho",
            //                     "pai": "Sra. Vanessa Maldonado",
            //                     "nascimento_data": null,
            //                     "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                     "created_ip": "127.0.0.1",
            //                     "created_at": "2024-10-26 10:01:25",
            //                     "updated_user_id": null,
            //                     "updated_ip": null,
            //                     "updated_at": null,
            //                     "deleted_user_id": null,
            //                     "deleted_ip": null,
            //                     "deleted_at": null
            //                 },
            //                 "idTr": "24b53133-86c7-4d09-841c-fc34d42c83df",
            //                 "idTrSelecionado": "48d31235-37f8-4437-b774-039b2f071349",
            //                 "idsTrs": [
            //                     "24b53133-86c7-4d09-841c-fc34d42c83df"
            //                 ]
            //             }
            //         },
            //         "referencia_id": "9d567bb9-9ca5-43ee-af38-28ed88be646b",
            //         "participacao_tipo_id": "9d567e49-ea88-4c5e-9536-5090c3d1b219",
            //         "valor_tipo": "porcentagem",
            //         "valor": 43.33,
            //         "observacao": "",
            //         "idCard": "662abd88-ca00-4343-a421-e63a1107beaa"
            //     }
            // );

            self.#inserirParticipanteNaTela(
                {
                    "participacao_registro_tipo_id": 1,
                    "referencia": {
                        "id": "9d567bb9-bbe5-44c4-ade5-1e92a8c98982",
                        "tenant_id": "jsadvogados",
                        "pessoa_id": "9d567bb9-b932-4698-b873-00829015d038",
                        "perfil_tipo_id": 2,
                        "observacao": null,
                        "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                        "created_ip": "127.0.0.1",
                        "created_at": "2024-10-26 10:01:25",
                        "updated_user_id": null,
                        "updated_ip": null,
                        "updated_at": null,
                        "deleted_user_id": null,
                        "deleted_ip": null,
                        "deleted_at": null,
                        "perfil_tipo": {
                            "id": 2,
                            "nome": "Parceiro",
                            "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
                            "tabela_ref": null,
                            "tabela_model": null,
                            "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                            "created_ip": "127.0.0.1",
                            "created_at": "2024-10-26 10:01:22",
                            "updated_user_id": null,
                            "updated_ip": null,
                            "updated_at": null,
                            "deleted_user_id": null,
                            "deleted_ip": null,
                            "deleted_at": null
                        },
                        "pessoa": {
                            "id": "9d567bb9-b932-4698-b873-00829015d038",
                            "tenant_id": "jsadvogados",
                            "pessoa_dados_type": "App\\Models\\Pessoa\\PessoaFisica",
                            "pessoa_dados_id": "9d567bb9-b707-43fd-96b1-f7b69e888c90",
                            "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                            "created_ip": "127.0.0.1",
                            "created_at": "2024-10-26 10:01:25",
                            "updated_user_id": null,
                            "updated_ip": null,
                            "updated_at": null,
                            "deleted_user_id": null,
                            "deleted_ip": null,
                            "deleted_at": null,
                            "pessoa_perfil": [
                                {
                                    "id": "9d567bb9-bbe5-44c4-ade5-1e92a8c98982",
                                    "tenant_id": "jsadvogados",
                                    "pessoa_id": "9d567bb9-b932-4698-b873-00829015d038",
                                    "perfil_tipo_id": 2,
                                    "observacao": null,
                                    "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                    "created_ip": "127.0.0.1",
                                    "created_at": "2024-10-26 10:01:25",
                                    "updated_user_id": null,
                                    "updated_ip": null,
                                    "updated_at": null,
                                    "deleted_user_id": null,
                                    "deleted_ip": null,
                                    "deleted_at": null,
                                    "perfil_tipo": {
                                        "id": 2,
                                        "nome": "Parceiro",
                                        "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
                                        "tabela_ref": null,
                                        "tabela_model": null,
                                        "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                        "created_ip": "127.0.0.1",
                                        "created_at": "2024-10-26 10:01:22",
                                        "updated_user_id": null,
                                        "updated_ip": null,
                                        "updated_at": null,
                                        "deleted_user_id": null,
                                        "deleted_ip": null,
                                        "deleted_at": null
                                    }
                                },
                                {
                                    "id": "9d567bb9-bd87-4959-ab68-c31037b1c75e",
                                    "tenant_id": "jsadvogados",
                                    "pessoa_id": "9d567bb9-b932-4698-b873-00829015d038",
                                    "perfil_tipo_id": 3,
                                    "observacao": null,
                                    "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                    "created_ip": "127.0.0.1",
                                    "created_at": "2024-10-26 10:01:25",
                                    "updated_user_id": null,
                                    "updated_ip": null,
                                    "updated_at": null,
                                    "deleted_user_id": null,
                                    "deleted_ip": null,
                                    "deleted_at": null,
                                    "perfil_tipo": {
                                        "id": 3,
                                        "nome": "Cliente",
                                        "descricao": "Perfil para clientes.",
                                        "tabela_ref": null,
                                        "tabela_model": null,
                                        "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                        "created_ip": "127.0.0.1",
                                        "created_at": "2024-10-26 10:01:22",
                                        "updated_user_id": null,
                                        "updated_ip": null,
                                        "updated_at": null,
                                        "deleted_user_id": null,
                                        "deleted_ip": null,
                                        "deleted_at": null
                                    }
                                }
                            ],
                            "pessoa_dados": {
                                "id": "9d567bb9-b707-43fd-96b1-f7b69e888c90",
                                "tenant_id": "jsadvogados",
                                "nome": "Ítalo Sebastião Quintana Filho",
                                "mae": "Richard Amaral Saito",
                                "pai": "Dr. Cristóvão Joaquin das Neves",
                                "nascimento_data": null,
                                "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                "created_ip": "127.0.0.1",
                                "created_at": "2024-10-26 10:01:25",
                                "updated_user_id": null,
                                "updated_ip": null,
                                "updated_at": null,
                                "deleted_user_id": null,
                                "deleted_ip": null,
                                "deleted_at": null
                            },
                            "idTr": "106ad974-59e4-4f7a-ae1d-162cdbccd267",
                            "idTrSelecionado": "ee7a9642-2478-42ac-9b2c-73487bd68be3",
                            "idsTrs": [
                                "106ad974-59e4-4f7a-ae1d-162cdbccd267"
                            ]
                        }
                    },
                    "referencia_id": "9d567bb9-bbe5-44c4-ade5-1e92a8c98982",
                    "participacao_tipo_id": "9d5a6563-f2eb-4969-97b9-16b2d5826099",
                    "valor_tipo": "porcentagem",
                    "valor": 23.34,
                    "observacao": "",
                    "idCard": "e8e57c16-7ea8-488e-a17f-a57069f5a91e"
                }
            );

            // self.#inserirParticipanteNaTela(
            //     {
            //         "participacao_registro_tipo_id": 1,
            //         "referencia": {
            //             "id": "9d567bb9-a718-428a-893a-3d8365954958",
            //             "tenant_id": "jsadvogados",
            //             "pessoa_id": "9d567bb9-a44a-4de9-a60f-0475d902059e",
            //             "perfil_tipo_id": 2,
            //             "observacao": null,
            //             "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //             "created_ip": "127.0.0.1",
            //             "created_at": "2024-10-26 10:01:25",
            //             "updated_user_id": null,
            //             "updated_ip": null,
            //             "updated_at": null,
            //             "deleted_user_id": null,
            //             "deleted_ip": null,
            //             "deleted_at": null,
            //             "perfil_tipo": {
            //                 "id": 2,
            //                 "nome": "Parceiro",
            //                 "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
            //                 "tabela_ref": null,
            //                 "tabela_model": null,
            //                 "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                 "created_ip": "127.0.0.1",
            //                 "created_at": "2024-10-26 10:01:22",
            //                 "updated_user_id": null,
            //                 "updated_ip": null,
            //                 "updated_at": null,
            //                 "deleted_user_id": null,
            //                 "deleted_ip": null,
            //                 "deleted_at": null
            //             },
            //             "pessoa": {
            //                 "id": "9d567bb9-a44a-4de9-a60f-0475d902059e",
            //                 "tenant_id": "jsadvogados",
            //                 "pessoa_dados_type": "App\\Models\\Pessoa\\PessoaFisica",
            //                 "pessoa_dados_id": "9d567bb9-a1f1-4b5f-ad0c-347ad82885d0",
            //                 "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                 "created_ip": "127.0.0.1",
            //                 "created_at": "2024-10-26 10:01:25",
            //                 "updated_user_id": null,
            //                 "updated_ip": null,
            //                 "updated_at": null,
            //                 "deleted_user_id": null,
            //                 "deleted_ip": null,
            //                 "deleted_at": null,
            //                 "pessoa_perfil": [
            //                     {
            //                         "id": "9d567bb9-a718-428a-893a-3d8365954958",
            //                         "tenant_id": "jsadvogados",
            //                         "pessoa_id": "9d567bb9-a44a-4de9-a60f-0475d902059e",
            //                         "perfil_tipo_id": 2,
            //                         "observacao": null,
            //                         "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                         "created_ip": "127.0.0.1",
            //                         "created_at": "2024-10-26 10:01:25",
            //                         "updated_user_id": null,
            //                         "updated_ip": null,
            //                         "updated_at": null,
            //                         "deleted_user_id": null,
            //                         "deleted_ip": null,
            //                         "deleted_at": null,
            //                         "perfil_tipo": {
            //                             "id": 2,
            //                             "nome": "Parceiro",
            //                             "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
            //                             "tabela_ref": null,
            //                             "tabela_model": null,
            //                             "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                             "created_ip": "127.0.0.1",
            //                             "created_at": "2024-10-26 10:01:22",
            //                             "updated_user_id": null,
            //                             "updated_ip": null,
            //                             "updated_at": null,
            //                             "deleted_user_id": null,
            //                             "deleted_ip": null,
            //                             "deleted_at": null
            //                         }
            //                     },
            //                     {
            //                         "id": "9d567bb9-a99f-497a-aa1a-9e20e5dddf36",
            //                         "tenant_id": "jsadvogados",
            //                         "pessoa_id": "9d567bb9-a44a-4de9-a60f-0475d902059e",
            //                         "perfil_tipo_id": 3,
            //                         "observacao": null,
            //                         "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                         "created_ip": "127.0.0.1",
            //                         "created_at": "2024-10-26 10:01:25",
            //                         "updated_user_id": null,
            //                         "updated_ip": null,
            //                         "updated_at": null,
            //                         "deleted_user_id": null,
            //                         "deleted_ip": null,
            //                         "deleted_at": null,
            //                         "perfil_tipo": {
            //                             "id": 3,
            //                             "nome": "Cliente",
            //                             "descricao": "Perfil para clientes.",
            //                             "tabela_ref": null,
            //                             "tabela_model": null,
            //                             "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                             "created_ip": "127.0.0.1",
            //                             "created_at": "2024-10-26 10:01:22",
            //                             "updated_user_id": null,
            //                             "updated_ip": null,
            //                             "updated_at": null,
            //                             "deleted_user_id": null,
            //                             "deleted_ip": null,
            //                             "deleted_at": null
            //                         }
            //                     }
            //                 ],
            //                 "pessoa_dados": {
            //                     "id": "9d567bb9-a1f1-4b5f-ad0c-347ad82885d0",
            //                     "tenant_id": "jsadvogados",
            //                     "nome": "Dr. Natal de Arruda Pereira",
            //                     "mae": "Srta. Camila Sheila Fidalgo Jr.",
            //                     "pai": "Nelson Aranda Valentin Sobrinho",
            //                     "nascimento_data": "1974-08-26",
            //                     "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
            //                     "created_ip": "127.0.0.1",
            //                     "created_at": "2024-10-26 10:01:25",
            //                     "updated_user_id": null,
            //                     "updated_ip": null,
            //                     "updated_at": null,
            //                     "deleted_user_id": null,
            //                     "deleted_ip": null,
            //                     "deleted_at": null
            //                 },
            //                 "idTr": "8b538a99-b783-44d3-bf16-2d04fd6e0200",
            //                 "idTrSelecionado": "09c64650-749a-4f1a-9673-bbd973c43988",
            //                 "idsTrs": [
            //                     "8b538a99-b783-44d3-bf16-2d04fd6e0200"
            //                 ]
            //             }
            //         },
            //         "referencia_id": "9d567bb9-a718-428a-893a-3d8365954958",
            //         "participacao_tipo_id": "9d567e4f-deb1-46c0-9868-5fd3fefc77ca",
            //         "valor_tipo": "porcentagem",
            //         "valor": 10,
            //         "observacao": "",
            //         "idCard": "ea1b6313-970b-4e65-ae8e-7b1cc836d23a"
            //     }
            // );

            let item = await self.#inserirParticipanteNaTela(
                {
                    "participacao_registro_tipo_id": 2,
                    "nome_grupo": "Rachadinha",
                    "participacao_tipo_id": "9d567e57-f34c-4cc3-9a5d-cbb5116b9a30",
                    "valor_tipo": "porcentagem",
                    "valor": 23.33,
                    "observacao": "",
                }
            );

            await self.#inserirIntegrante(item,
                {
                    "participacao_registro_tipo_id": 1,
                    "referencia": {
                        "id": "9d567bb9-9ca5-43ee-af38-28ed88be646b",
                        "tenant_id": "jsadvogados",
                        "pessoa_id": "9d567bb9-9a0e-4e24-8d8b-0c92c801cd9b",
                        "perfil_tipo_id": 2,
                        "observacao": null,
                        "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                        "created_ip": "127.0.0.1",
                        "created_at": "2024-10-26 10:01:25",
                        "updated_user_id": null,
                        "updated_ip": null,
                        "updated_at": null,
                        "deleted_user_id": null,
                        "deleted_ip": null,
                        "deleted_at": null,
                        "perfil_tipo": {
                            "id": 2,
                            "nome": "Parceiro",
                            "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
                            "tabela_ref": null,
                            "tabela_model": null,
                            "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                            "created_ip": "127.0.0.1",
                            "created_at": "2024-10-26 10:01:22",
                            "updated_user_id": null,
                            "updated_ip": null,
                            "updated_at": null,
                            "deleted_user_id": null,
                            "deleted_ip": null,
                            "deleted_at": null
                        },
                        "pessoa": {
                            "id": "9d567bb9-9a0e-4e24-8d8b-0c92c801cd9b",
                            "tenant_id": "jsadvogados",
                            "pessoa_dados_type": "App\\Models\\Pessoa\\PessoaFisica",
                            "pessoa_dados_id": "9d567bb9-9733-4c3b-b72c-aff3ab976af0",
                            "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                            "created_ip": "127.0.0.1",
                            "created_at": "2024-10-26 10:01:25",
                            "updated_user_id": null,
                            "updated_ip": null,
                            "updated_at": null,
                            "deleted_user_id": null,
                            "deleted_ip": null,
                            "deleted_at": null,
                            "pessoa_perfil": [
                                {
                                    "id": "9d567bb9-9ca5-43ee-af38-28ed88be646b",
                                    "tenant_id": "jsadvogados",
                                    "pessoa_id": "9d567bb9-9a0e-4e24-8d8b-0c92c801cd9b",
                                    "perfil_tipo_id": 2,
                                    "observacao": null,
                                    "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                    "created_ip": "127.0.0.1",
                                    "created_at": "2024-10-26 10:01:25",
                                    "updated_user_id": null,
                                    "updated_ip": null,
                                    "updated_at": null,
                                    "deleted_user_id": null,
                                    "deleted_ip": null,
                                    "deleted_at": null,
                                    "perfil_tipo": {
                                        "id": 2,
                                        "nome": "Parceiro",
                                        "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
                                        "tabela_ref": null,
                                        "tabela_model": null,
                                        "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                        "created_ip": "127.0.0.1",
                                        "created_at": "2024-10-26 10:01:22",
                                        "updated_user_id": null,
                                        "updated_ip": null,
                                        "updated_at": null,
                                        "deleted_user_id": null,
                                        "deleted_ip": null,
                                        "deleted_at": null
                                    }
                                },
                                {
                                    "id": "9d567bb9-9ed6-4725-889d-d585e2913b61",
                                    "tenant_id": "jsadvogados",
                                    "pessoa_id": "9d567bb9-9a0e-4e24-8d8b-0c92c801cd9b",
                                    "perfil_tipo_id": 3,
                                    "observacao": null,
                                    "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                    "created_ip": "127.0.0.1",
                                    "created_at": "2024-10-26 10:01:25",
                                    "updated_user_id": null,
                                    "updated_ip": null,
                                    "updated_at": null,
                                    "deleted_user_id": null,
                                    "deleted_ip": null,
                                    "deleted_at": null,
                                    "perfil_tipo": {
                                        "id": 3,
                                        "nome": "Cliente",
                                        "descricao": "Perfil para clientes.",
                                        "tabela_ref": null,
                                        "tabela_model": null,
                                        "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                        "created_ip": "127.0.0.1",
                                        "created_at": "2024-10-26 10:01:22",
                                        "updated_user_id": null,
                                        "updated_ip": null,
                                        "updated_at": null,
                                        "deleted_user_id": null,
                                        "deleted_ip": null,
                                        "deleted_at": null
                                    }
                                }
                            ],
                            "pessoa_dados": {
                                "id": "9d567bb9-9733-4c3b-b72c-aff3ab976af0",
                                "tenant_id": "jsadvogados",
                                "nome": "Luana Chaves Santos Neto",
                                "mae": "Pérola Camacho",
                                "pai": "Sra. Vanessa Maldonado",
                                "nascimento_data": null,
                                "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                "created_ip": "127.0.0.1",
                                "created_at": "2024-10-26 10:01:25",
                                "updated_user_id": null,
                                "updated_ip": null,
                                "updated_at": null,
                                "deleted_user_id": null,
                                "deleted_ip": null,
                                "deleted_at": null
                            },
                            "idTr": "67a36113-c85e-4e29-afcb-67527380a65c",
                            "idTrSelecionado": "d6921605-8b8b-4aa3-8c5b-1f8b8bd68b1a",
                            "idsTrs": [
                                "67a36113-c85e-4e29-afcb-67527380a65c"
                            ]
                        }
                    },
                    "referencia_id": "9d567bb9-9ca5-43ee-af38-28ed88be646b",
                    "idCard": "e5280ea6-0533-4e9e-bec8-4649b68b9ef3"
                }
            );

            await self.#inserirIntegrante(item,
                {
                    "participacao_registro_tipo_id": 1,
                    "referencia": {
                        "id": "9d567bb9-a718-428a-893a-3d8365954958",
                        "tenant_id": "jsadvogados",
                        "pessoa_id": "9d567bb9-a44a-4de9-a60f-0475d902059e",
                        "perfil_tipo_id": 2,
                        "observacao": null,
                        "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                        "created_ip": "127.0.0.1",
                        "created_at": "2024-10-26 10:01:25",
                        "updated_user_id": null,
                        "updated_ip": null,
                        "updated_at": null,
                        "deleted_user_id": null,
                        "deleted_ip": null,
                        "deleted_at": null,
                        "perfil_tipo": {
                            "id": 2,
                            "nome": "Parceiro",
                            "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
                            "tabela_ref": null,
                            "tabela_model": null,
                            "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                            "created_ip": "127.0.0.1",
                            "created_at": "2024-10-26 10:01:22",
                            "updated_user_id": null,
                            "updated_ip": null,
                            "updated_at": null,
                            "deleted_user_id": null,
                            "deleted_ip": null,
                            "deleted_at": null
                        },
                        "pessoa": {
                            "id": "9d567bb9-a44a-4de9-a60f-0475d902059e",
                            "tenant_id": "jsadvogados",
                            "pessoa_dados_type": "App\\Models\\Pessoa\\PessoaFisica",
                            "pessoa_dados_id": "9d567bb9-a1f1-4b5f-ad0c-347ad82885d0",
                            "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                            "created_ip": "127.0.0.1",
                            "created_at": "2024-10-26 10:01:25",
                            "updated_user_id": null,
                            "updated_ip": null,
                            "updated_at": null,
                            "deleted_user_id": null,
                            "deleted_ip": null,
                            "deleted_at": null,
                            "pessoa_perfil": [
                                {
                                    "id": "9d567bb9-a718-428a-893a-3d8365954958",
                                    "tenant_id": "jsadvogados",
                                    "pessoa_id": "9d567bb9-a44a-4de9-a60f-0475d902059e",
                                    "perfil_tipo_id": 2,
                                    "observacao": null,
                                    "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                    "created_ip": "127.0.0.1",
                                    "created_at": "2024-10-26 10:01:25",
                                    "updated_user_id": null,
                                    "updated_ip": null,
                                    "updated_at": null,
                                    "deleted_user_id": null,
                                    "deleted_ip": null,
                                    "deleted_at": null,
                                    "perfil_tipo": {
                                        "id": 2,
                                        "nome": "Parceiro",
                                        "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
                                        "tabela_ref": null,
                                        "tabela_model": null,
                                        "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                        "created_ip": "127.0.0.1",
                                        "created_at": "2024-10-26 10:01:22",
                                        "updated_user_id": null,
                                        "updated_ip": null,
                                        "updated_at": null,
                                        "deleted_user_id": null,
                                        "deleted_ip": null,
                                        "deleted_at": null
                                    }
                                },
                                {
                                    "id": "9d567bb9-a99f-497a-aa1a-9e20e5dddf36",
                                    "tenant_id": "jsadvogados",
                                    "pessoa_id": "9d567bb9-a44a-4de9-a60f-0475d902059e",
                                    "perfil_tipo_id": 3,
                                    "observacao": null,
                                    "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                    "created_ip": "127.0.0.1",
                                    "created_at": "2024-10-26 10:01:25",
                                    "updated_user_id": null,
                                    "updated_ip": null,
                                    "updated_at": null,
                                    "deleted_user_id": null,
                                    "deleted_ip": null,
                                    "deleted_at": null,
                                    "perfil_tipo": {
                                        "id": 3,
                                        "nome": "Cliente",
                                        "descricao": "Perfil para clientes.",
                                        "tabela_ref": null,
                                        "tabela_model": null,
                                        "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                        "created_ip": "127.0.0.1",
                                        "created_at": "2024-10-26 10:01:22",
                                        "updated_user_id": null,
                                        "updated_ip": null,
                                        "updated_at": null,
                                        "deleted_user_id": null,
                                        "deleted_ip": null,
                                        "deleted_at": null
                                    }
                                }
                            ],
                            "pessoa_dados": {
                                "id": "9d567bb9-a1f1-4b5f-ad0c-347ad82885d0",
                                "tenant_id": "jsadvogados",
                                "nome": "Dr. Natal de Arruda Pereira",
                                "mae": "Srta. Camila Sheila Fidalgo Jr.",
                                "pai": "Nelson Aranda Valentin Sobrinho",
                                "nascimento_data": "1974-08-26",
                                "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                "created_ip": "127.0.0.1",
                                "created_at": "2024-10-26 10:01:25",
                                "updated_user_id": null,
                                "updated_ip": null,
                                "updated_at": null,
                                "deleted_user_id": null,
                                "deleted_ip": null,
                                "deleted_at": null
                            },
                            "idTr": "2a328cbb-2e54-4635-9fca-b4abba26d003",
                            "idTrSelecionado": "6a867c3e-9c1c-4d02-8bac-70a379223814",
                            "idsTrs": [
                                "2a328cbb-2e54-4635-9fca-b4abba26d003"
                            ]
                        }
                    },
                    "referencia_id": "9d567bb9-a718-428a-893a-3d8365954958",
                    "idCard": "9864a04d-c272-4069-a7fa-87b56ba49c1b"
                }
            );

            await self.#inserirIntegrante(item,
                {
                    "participacao_registro_tipo_id": 1,
                    "referencia": {
                        "id": "9d567bb9-b19b-4c4b-a53b-37ec3f438d01",
                        "tenant_id": "jsadvogados",
                        "pessoa_id": "9d567bb9-aed6-4fbe-b581-a3b3c8feed03",
                        "perfil_tipo_id": 2,
                        "observacao": null,
                        "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                        "created_ip": "127.0.0.1",
                        "created_at": "2024-10-26 10:01:25",
                        "updated_user_id": null,
                        "updated_ip": null,
                        "updated_at": null,
                        "deleted_user_id": null,
                        "deleted_ip": null,
                        "deleted_at": null,
                        "perfil_tipo": {
                            "id": 2,
                            "nome": "Parceiro",
                            "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
                            "tabela_ref": null,
                            "tabela_model": null,
                            "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                            "created_ip": "127.0.0.1",
                            "created_at": "2024-10-26 10:01:22",
                            "updated_user_id": null,
                            "updated_ip": null,
                            "updated_at": null,
                            "deleted_user_id": null,
                            "deleted_ip": null,
                            "deleted_at": null
                        },
                        "pessoa": {
                            "id": "9d567bb9-aed6-4fbe-b581-a3b3c8feed03",
                            "tenant_id": "jsadvogados",
                            "pessoa_dados_type": "App\\Models\\Pessoa\\PessoaFisica",
                            "pessoa_dados_id": "9d567bb9-ac54-4f0c-a8c5-a7d81a101374",
                            "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                            "created_ip": "127.0.0.1",
                            "created_at": "2024-10-26 10:01:25",
                            "updated_user_id": null,
                            "updated_ip": null,
                            "updated_at": null,
                            "deleted_user_id": null,
                            "deleted_ip": null,
                            "deleted_at": null,
                            "pessoa_perfil": [
                                {
                                    "id": "9d567bb9-b19b-4c4b-a53b-37ec3f438d01",
                                    "tenant_id": "jsadvogados",
                                    "pessoa_id": "9d567bb9-aed6-4fbe-b581-a3b3c8feed03",
                                    "perfil_tipo_id": 2,
                                    "observacao": null,
                                    "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                    "created_ip": "127.0.0.1",
                                    "created_at": "2024-10-26 10:01:25",
                                    "updated_user_id": null,
                                    "updated_ip": null,
                                    "updated_at": null,
                                    "deleted_user_id": null,
                                    "deleted_ip": null,
                                    "deleted_at": null,
                                    "perfil_tipo": {
                                        "id": 2,
                                        "nome": "Parceiro",
                                        "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
                                        "tabela_ref": null,
                                        "tabela_model": null,
                                        "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                        "created_ip": "127.0.0.1",
                                        "created_at": "2024-10-26 10:01:22",
                                        "updated_user_id": null,
                                        "updated_ip": null,
                                        "updated_at": null,
                                        "deleted_user_id": null,
                                        "deleted_ip": null,
                                        "deleted_at": null
                                    }
                                },
                                {
                                    "id": "9d567bb9-b460-442b-82ee-2804d365aa14",
                                    "tenant_id": "jsadvogados",
                                    "pessoa_id": "9d567bb9-aed6-4fbe-b581-a3b3c8feed03",
                                    "perfil_tipo_id": 3,
                                    "observacao": null,
                                    "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                    "created_ip": "127.0.0.1",
                                    "created_at": "2024-10-26 10:01:25",
                                    "updated_user_id": null,
                                    "updated_ip": null,
                                    "updated_at": null,
                                    "deleted_user_id": null,
                                    "deleted_ip": null,
                                    "deleted_at": null,
                                    "perfil_tipo": {
                                        "id": 3,
                                        "nome": "Cliente",
                                        "descricao": "Perfil para clientes.",
                                        "tabela_ref": null,
                                        "tabela_model": null,
                                        "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                        "created_ip": "127.0.0.1",
                                        "created_at": "2024-10-26 10:01:22",
                                        "updated_user_id": null,
                                        "updated_ip": null,
                                        "updated_at": null,
                                        "deleted_user_id": null,
                                        "deleted_ip": null,
                                        "deleted_at": null
                                    }
                                }
                            ],
                            "pessoa_dados": {
                                "id": "9d567bb9-ac54-4f0c-a8c5-a7d81a101374",
                                "tenant_id": "jsadvogados",
                                "nome": "Sr. Márcio Ortega Rios",
                                "mae": "Dr. Mauro Verdara Gomes",
                                "pai": "Lavínia Colaço",
                                "nascimento_data": null,
                                "created_user_id": "49e960e1-6a30-4a2e-8cc8-5200704ebc21",
                                "created_ip": "127.0.0.1",
                                "created_at": "2024-10-26 10:01:25",
                                "updated_user_id": null,
                                "updated_ip": null,
                                "updated_at": null,
                                "deleted_user_id": null,
                                "deleted_ip": null,
                                "deleted_at": null
                            },
                            "idTr": "80d0abc8-e92a-45a1-a9b7-8a429ba7e906",
                            "idTrSelecionado": "1bad0999-f024-479e-ac4c-228fef0a9664",
                            "idsTrs": [
                                "80d0abc8-e92a-45a1-a9b7-8a429ba7e906"
                            ]
                        }
                    },
                    "referencia_id": "9d567bb9-b19b-4c4b-a53b-37ec3f438d01",
                    "idCard": "4c9652c2-c652-4b87-89ad-824f1003cba6"
                }
            );
        }

        const openModalTest = async () => {
            const perfis_busca = window.Statics.PerfisPermitidoParticipacaoServico.map(item => item.id);
            const objCode = new modalSelecionarPerfil();
            objCode.setDataEnvModal = {
                perfis_permitidos: perfis_busca,
                perfis_opcoes: [
                    {
                        "id": "9d5426a5-bd48-4f8d-b278-f05b27f50d3c",
                        "tenant_id": "jsadvogados",
                        "pessoa_id": "9d5426a5-bbaa-4a88-8b99-fb021edf342c",
                        "perfil_tipo_id": 2,
                        "observacao": null,
                        "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                        "created_ip": "127.0.0.1",
                        "created_at": "2024-10-25 06:11:52",
                        "updated_user_id": null,
                        "updated_ip": null,
                        "updated_at": null,
                        "deleted_user_id": null,
                        "deleted_ip": null,
                        "deleted_at": null,
                        "perfil_tipo": {
                            "id": 2,
                            "nome": "Parceiro",
                            "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
                            "tabela_ref": null,
                            "tabela_model": null,
                            "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                            "created_ip": "127.0.0.1",
                            "created_at": "2024-10-24 20:17:22",
                            "updated_user_id": null,
                            "updated_ip": null,
                            "updated_at": null,
                            "deleted_user_id": null,
                            "deleted_ip": null,
                            "deleted_at": null
                        }
                    },
                    {
                        "id": "9d5426a5-bee9-465a-a68e-ba227c420984",
                        "tenant_id": "jsadvogados",
                        "pessoa_id": "9d5426a5-bbaa-4a88-8b99-fb021edf342c",
                        "perfil_tipo_id": 3,
                        "observacao": null,
                        "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                        "created_ip": "127.0.0.1",
                        "created_at": "2024-10-25 06:11:52",
                        "updated_user_id": null,
                        "updated_ip": null,
                        "updated_at": null,
                        "deleted_user_id": null,
                        "deleted_ip": null,
                        "deleted_at": null,
                        "perfil_tipo": {
                            "id": 3,
                            "nome": "Cliente",
                            "descricao": "Perfil para clientes.",
                            "tabela_ref": null,
                            "tabela_model": null,
                            "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                            "created_ip": "127.0.0.1",
                            "created_at": "2024-10-24 20:17:22",
                            "updated_user_id": null,
                            "updated_ip": null,
                            "updated_at": null,
                            "deleted_user_id": null,
                            "deleted_ip": null,
                            "deleted_at": null
                        }
                    }
                ],
            };
            const retorno = await objCode.modalOpen();
        }

        // openModalTest();
        // evento();
    }

    async #buscarParticipacaoTipo(id) {
        const self = this;
        return await self.#getRecurse({ idRegister: id, urlApi: self.#objConfigs.url.baseParticipacaoTipo });
    }

    async #inserirParticipanteNaTela(item) {
        const self = this;
        const divParticipantes = $(`#divParticipantes${self.#sufixo}`);
        item.idCard = UUIDHelper.generateUUID();

        let nome = '';
        let btnsAppend = '';
        let accordionIntegrantes = '';
        switch (item.participacao_registro_tipo_id) {
            case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:
                nome = item.referencia.pessoa.pessoa_dados.nome;
                break;
            case window.Enums.ParticipacaoRegistroTipoEnum.GRUPO:
                nome = item.nome_grupo;
                btnsAppend += `<li><button type="button" class="dropdown-item fs-6 btn-add-pessoa">Inserir Pessoa</button></li>`;
                btnsAppend += `<li><button type="button" class="dropdown-item fs-6 btn-edit-name">Editar Nome</button></li>`;
                accordionIntegrantes = self.#accordionIntegrantesGrupo(item);
                break;
            default:
                commonFunctions.generateNotification('Tipo de registro de participação não informado.', 'error');
                console.error('Tipo de registro de participação não informado.', item);
                return false;
        }

        let participacao_tipo = item?.participacao_tipo?.nome ?? null;
        if (!participacao_tipo) {
            if (item.participacao_tipo_id) {
                const response = await self.#buscarParticipacaoTipo(item.participacao_tipo_id);
                if (response) {
                    participacao_tipo = response.data;
                } else {
                    participacao_tipo = { nome: 'Erro de busca' }
                }
            } else {
                commonFunctions.generateNotification('Tipo de participação não informado.', 'error');
                console.error('Tipo de participação não informado.', item);
                return false;
            }
        }

        const naTela = self.#verificaRegistroNaTela(item);
        if (naTela) {
            commonFunctions.generateNotification(`Participante <b>${naTela.referencia.pessoa.pessoa_dados.nome}</b> já foi inserido(a) para este tipo de participação.`, 'error');
            return false;
        }

        let valor_tipo = ''
        let valor = commonFunctions.formatWithCurrencyCommasOrFraction(item.valor);
        switch (item.valor_tipo) {
            case 'porcentagem':
                valor_tipo = 'Porcentagem';
                valor += '%';
                break;
            case 'valor_fixo':
                valor_tipo = 'Valor Fixo';
                valor = `R$ ${valor}`;
                break;
            default:
                valor_tipo = 'Erro valor tipo';
                console.error('Erro no tipo de valor', item);
                break;
        }

        const strCard = `
            <div class="card-body">
                <h5 class="card-title d-flex align-items-center justify-content-between">
                    <span class="spanNome">${nome}</span>
                    <div>
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    ${btnsAppend}
                                    <li><button type="button" class="dropdown-item fs-6 btn-edit">Editar</button></li>
                                    <li><button type="button" class="dropdown-item fs-6 btn-delete">Excluir</button></li>
                                </ul>
                            </div>
                    </div>
                </h5>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3">
                    <div class="col">
                        <div class="form-text">Participação</div>
                        <label class="form-label lblParticipacao">${participacao_tipo.nome}</label>
                    </div>
                    <div class="col">
                        <div class="form-text">Método</div>
                        <label class="form-label lblValorTipo">${valor_tipo}</label>
                    </div>
                    <div class="col">
                        <div class="form-text">Valor</div>
                        <label class="form-label lblValor">${valor}</label>
                    </div>
                </div>
                ${accordionIntegrantes}
            </div>`;

        self.#objConfigs.data.participantesNaTela.push(item);

        divParticipantes.append(`<div id="${item.idCard}" class="card">${strCard}</div>`);

        self.#addEventoParticipante(item);
        await self.#atualizaPorcentagemLivre(item);
        return item;
    }

    async #atualizaParticipanteNaTela(item) {
        const self = this;

        let participacao_tipo = item?.participacao_tipo?.nome ?? null;
        if (!participacao_tipo && item.participacao_tipo_id) {
            const response = await self.#buscarParticipacaoTipo(item.participacao_tipo_id);
            if (response) {
                participacao_tipo = response.data;
            } else {
                participacao_tipo = { nome: 'Erro de busca' }
            }
        } else {
            commonFunctions.generateNotification('Tipo de participação não informado.', 'error');
            console.error('Tipo de participação não informado.', item);
            return false;
        }

        let valor_tipo = ''
        let valor = commonFunctions.formatWithCurrencyCommasOrFraction(item.valor);
        switch (item.valor_tipo) {
            case 'porcentagem':
                valor_tipo = 'Porcentagem';
                valor += '%';
                break;
            case 'valor_fixo':
                valor_tipo = 'Valor Fixo';
                valor = `R$ ${valor}`;
                break;
            default:
                valor_tipo = 'Erro valor tipo';
                console.error('Erro no tipo de valor', item);
                break;
        }

        for (const element of self.#objConfigs.data.participantesNaTela) {
            if (element.idCard == item.idCard) {
                element.participacao_tipo_id = item.participacao_tipo_id;
                element.valor_tipo = item.valor_tipo;
                element.valor = item.valor;
                break;
            }
        }

        $(`#${item.idCard} .lblParticipacao`).text(participacao_tipo.nome);
        $(`#${item.idCard} .lblValorTipo`).text(valor_tipo);
        $(`#${item.idCard} .lblValor`).text(valor);

        await self.#atualizaPorcentagemLivre();
    }

    async #addEventoParticipante(item) {
        const self = this;

        $(`#${item.idCard} .btn-edit`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                let porcentagem_ocupada = self.#objConfigs.data.porcentagem_ocupada;
                if (item.valor_tipo == 'porcentagem') {
                    porcentagem_ocupada -= item.valor;
                }
                const objModal = new modalServicoParticipacao();
                objModal.setDataEnvModal = {
                    dados_participacao: item,
                    porcentagem_ocupada: porcentagem_ocupada,
                }
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    await self.#atualizaParticipanteNaTela(Object.assign(item, response.register));
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        if (item.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.GRUPO) {

            $(`#${item.idCard} .btn-edit-name`).on('click', async function () {

                let registro = undefined;
                for (const element of self.#objConfigs.data.participantesNaTela) {
                    if (element.idCard == item.idCard) {
                        registro = element;
                        break;
                    }
                }
                const btn = $(this);
                commonFunctions.simulateLoading(btn);
                try {
                    const objModalNome = new modalNome();
                    objModalNome.setDataEnvModal = {
                        title: 'Novo grupo',
                        mensagem: 'Informe o nome do grupo',
                        nome: registro.nome_grupo,
                    }
                    const response = await objModalNome.modalOpen();
                    if (response.refresh) {
                        registro.nome_grupo = response.name;
                        $(`#${item.idCard} .spanNome`).text(registro.nome_grupo);
                    }
                } catch (error) {
                    commonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    commonFunctions.simulateLoading(btn, false);
                }
            });

            $(`#${item.idCard} .btn-add-pessoa`).on('click', async function () {
                const btn = $(this);
                commonFunctions.simulateLoading(btn);
                try {
                    const dataEnvModalAppend = {
                        perfis_busca: window.Statics.PerfisPermitidoParticipacaoServico,
                    };
                    const objModal = new modalPessoa({ dataEnvModal: dataEnvModalAppend });
                    const response = await objModal.modalOpen();
                    if (response.refresh && response.selected) {
                        await self.#inserirIntegrante(item, {
                            participacao_registro_tipo_id: window.Enums.ParticipacaoRegistroTipoEnum.PERFIL,
                            referencia: response.selected,
                            referencia_id: response.selected.id,
                        });

                    }
                } catch (error) {
                    commonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    commonFunctions.simulateLoading(btn, false);
                }
            });
        }
    }

    #verificaRegistroNaTela(item) {
        const self = this;

        if (item.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.PERFIL) {
            for (const element of self.#objConfigs.data.participantesNaTela) {
                if (element.participacao_registro_tipo_id != window.Enums.ParticipacaoRegistroTipoEnum.PERFIL) {
                    continue;
                }

                if (element.referencia.id == item.referencia.id &&
                    element.participacao_tipo_id == item.participacao_tipo_id
                ) {
                    return element;
                }
            }
        }
        return null;
    }

    async #atualizaPorcentagemLivre() {
        const self = this;
        let porcentagemOcupada = 0;
        let valorFixo = 0;

        for (const itemTela of self.#objConfigs.data.participantesNaTela) {
            if (itemTela.valor_tipo == 'porcentagem') {
                porcentagemOcupada += itemTela.valor;
            } else {
                valorFixo += itemTela.valor;
            }
        }
        self.#objConfigs.data.porcentagem_ocupada = porcentagemOcupada;
        self.#objConfigs.data.valor_fixo = valorFixo;

        let valorMinimo = 0;
        if (porcentagemOcupada > 0 && valorFixo > 0) {
            valorMinimo = valorFixo + 1;
        } else if (valorFixo > 0) {
            valorMinimo = valorFixo;
        }

        $(`#valor_fixo${self.#sufixo}`).text(`${commonFunctions.formatWithCurrencyCommasOrFraction(valorFixo)}`);
        $(`#porcentagem${self.#sufixo}`).text(`${commonFunctions.formatWithCurrencyCommasOrFraction(porcentagemOcupada)}`);
        $(`#valor_minimo${self.#sufixo}`).text(`${commonFunctions.formatWithCurrencyCommasOrFraction(valorMinimo)}`);

        commonFunctions.atualizarProgressBar($(`#progressBar${self.#sufixo}`), porcentagemOcupada);
    }

    async #inserirIntegrante(item, integrante) {
        const self = this;
        const rowIntegrantes = $(`#accordionIntegrantes${item.idCard} .rowIntegrantes`);
        integrante.idCard = UUIDHelper.generateUUID();
        console.log(integrante);

        let nome = '';
        let tipoReferencia = '';
        switch (integrante.participacao_registro_tipo_id) {
            case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:
                nome = integrante.referencia.pessoa.pessoa_dados.nome;
                tipoReferencia = `Perfil ${integrante.referencia.perfil_tipo.nome}`;
                break;
            default:
                commonFunctions.generateNotification('Tipo de registro de participação não informado.', 'error');
                console.error('Tipo de registro de participação não informado.', item);
                return false;
        }

        rowIntegrantes.append(`
            <div id="${integrante.idCard}" class="card">
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center justify-content-between">
                        <span>${nome}</span>
                        <div>
                            <div class="d-grid gap-2 d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-danger btn-sm btn-delete-integrante border-0">Excluir</button>
                            </div>
                        </div>
                    </h5>
                    <div class="row">
                        <div class="col">
                            <div class="form-text">Tipo Referência</div>
                            <label class="form-label">${tipoReferencia}</label>
                        </div>
                    </div>
                </div>
            </div>
            `);


        let element = self.#objConfigs.data.participantesNaTela.find(participante => participante.idCard == item.idCard);
        if (!element.integrantes) {
            element.integrantes = [];
        }
        element.integrantes.push(integrante);

        self.#atualizaQuantidadeIntegrantes(item.idCard);
        self.#addEventoPerfilIntegrante(item, integrante);
    }

    async #addEventoPerfilIntegrante(item, integrante) {
        const self = this;

        $(`#${integrante.idCard} .btn-delete-integrante`).on('click', async function () {
            $(`#${integrante.idCard}`).remove();

            const participantes = self.#objConfigs.data.participantesNaTela;
            const indexPart = participantes.findIndex(participante => participante.idCard === item.idCard);

            if (indexPart > -1) {
                const indexInt = participantes[indexPart].integrantes.findIndex(item => item.idCard === integrante.idCard);
                if (indexInt > -1) {
                    participantes[indexPart].integrantes.splice(indexInt, 1);
                }
            }

            self.#atualizaQuantidadeIntegrantes(item.idCard);
        });

    }

    #accordionIntegrantesGrupo(item) {
        return `
            <div class="accordion mt-2" id="accordionIntegrantes${item.idCard}">
                <div class="accordion-item">
                    <div class="accordion-header">
                        <button class="accordion-button py-1 collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseOne${item.idCard}" aria-expanded="true"
                            aria-controls="collapseOne${item.idCard}">
                            <span class="qtdIntegrantes">Nenhum integrante no grupo</span>
                        </button>
                    </div>
                    <div id="collapseOne${item.idCard}" class="accordion-collapse collapse"
                        data-bs-parent="#accordionIntegrantes${item.idCard}">
                        <div class="accordion-body">
                            <div class="row rowIntegrantes row-cols-1 g-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

    }

    #atualizaQuantidadeIntegrantes(idCard) {
        const self = this;
        let element = self.#objConfigs.data.participantesNaTela.find(item => item.idCard == idCard);
        const totalIntegrantes = element.integrantes.length;
        const qtdIntegrantes = $(`#accordionIntegrantes${idCard} .qtdIntegrantes`);
        let str = 'Nenhum integrante no grupo';

        if (totalIntegrantes === 1) {
            str = '1 integrante no grupo';
        } else if (totalIntegrantes > 1) {
            str = `${totalIntegrantes} integrantes no grupo`;
        }
        qtdIntegrantes.html(str);
    }

    #saveButtonAction() {
        const self = this;
        const formRegistration = $(`#form${self.#sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.participantes = self.#objConfigs.data.participantesNaTela;
        // if (self.#saveVerifications(data, formRegistration)) {
        self.#save(data, self.#objConfigs.url.base);
        // }
        return false;
    }

    #saveVerifications(data, formRegistration) {
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

            // if (response) {
            //     if (self.#action === enumAction.PUT) {
            //         commonFunctions.generateNotification('Dados do serviço alterados com sucesso!', 'success');
            //     } else {
            //         RedirectHelper.redirectWithUUIDMessage(`${window.frontRoutes.frontRedirectForm}/${response.data.id}`, 'Serviço iniciado com sucesso!', 'success');
            //     }
            // }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            commonFunctions.simulateLoading(btnSave, false);
            console.log(data);
        };
    }

    async #buscarDados() {
        const self = this;

        try {
            await commonFunctions.loadingModalDisplay();
            const response = await self.#getRecurse();
            const form = $(`#form${self.#sufixo}`);
            if (response?.data) {
                const responseData = response.data;
                form.find('input[name="nome"]').val(responseData.nome);
                form.find('input[name="descricao"]').val(responseData.descricao);

                await Promise.all(
                    responseData.participantes.map(async (participante) => {
                        const item = await self.#inserirParticipanteNaTela(participante);
                        await Promise.all(
                            participante.integrantes.map(async (integrante) => {
                                await self.#inserirIntegrante(item, integrante);
                            })
                        );
                    })
                );
                
            } else {
                form.find('input, textarea, select, button').prop('disabled', true);
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }

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

    async #delButtonAction(idDel, nameDel, options = {}) {
        const self = this;
        const { button = null,
            title = 'Exclusão de Registro',
            message = `Confirma a exclusão do registro < b > ${nameDel}</ >? `,
            success = `Registro excluído com sucesso!`,
        } = options;

        try {
            const obj = new modalMessage();
            obj.setDataEnvModal = {
                title: title,
                message: message,
            };
            obj.setFocusElementWhenClosingModal = button;
            const result = await obj.modalOpen();
            if (result.confirmResult) {
                if (await self.#delRecurse(idDel, options)) {
                    commonFunctions.generateNotification(success, 'success');
                    return true;
                }
            }
            return false;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #delRecurse(idDel, options = {}) {
        const self = this;
        const {
            urlApi = self.#objConfigs.url.base,
        } = options;

        try {
            const obj = new connectAjax(urlApi);
            obj.setParam(idDel);
            obj.setAction(enumAction.DELETE)
            await obj.deleteRequest();
            return true;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }
}

$(function () {
    new PageServicoParticipacaoPresetForm();
});