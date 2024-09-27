import { commonFunctions } from "../../../commons/commonFunctions";
import { connectAjax } from "../../../commons/connectAjax";
import { enumAction } from "../../../commons/enumAction";
import { modalBuscaPessoas } from "../../../components/comum/modalBuscaPessoas";
import { modalInformacaoSubjetivaCategoria } from "../../../components/inteligencia/modalInformacaoSubjetivaCategoria";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { funcoesPresos } from "../../../helpers/funcoesPresos";
import { RedirectHelper } from "../../../helpers/RedirectHelper";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class PageFormInformacaoSubjetivaForm {

    #sufixo = 'PageFormInformacaoSubjetivaForm';
    #objConfigs = {
        url: {
            foto: window.apiRoutes.baseFotos,
            base: window.apiRoutes.baseInfoSubj,
            baseInfoSubjCategorias: window.apiRoutes.baseInfoSubjCategorias,
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
        commonFunctions.addEventsSelect2Api($(`#categoria_id${self.#sufixo}`), `${self.#objConfigs.url.baseInfoSubjCategorias}/select2`);
        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self.#idRegister = uuid;
            this.#action = enumAction.PUT;
            self.#buscarDados();
        } else {
            this.#action = enumAction.POST;
        }
    }

    #addEventosBotoes() {
        const self = this;

        // $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
        //     e.preventDefault();
        //     self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
        //     self._generateQueryFilters()
        // });

        $('#btnAdicionarEnvolvidos').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalBuscaPessoas();
                const response = await objModal.modalOpen();
                if (response.refresh && response.selecteds.length > 0) {
                    console.log(response);
                    for (let pessoa of response.selecteds) {
                        self.#inserirPessoasEnvolvidas(pessoa);
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $('#btnOpenCategoria').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalInformacaoSubjetivaCategoria();
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    const categoria_id = $(`#categoria_id${self.#sufixo}`).val();
                    if (categoria_id) {
                        const update = await commonFunctions.getRecurseWithTrashed(`${self.#objConfigs.url.baseInfoSubjCategorias}/${categoria_id}`);
                        if (update?.data) {
                            commonFunctions.updateSelect2Value($(`#categoria_id${self.#sufixo}`), update.data.nome, update.data.id);
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
            console.log('btnSave');
            self.#saveButtonAction();
        });

        const openModalTest = () => {
            const objCode = new modalBuscaPessoas();
            objCode.modalOpen();
        }
        // openModalTest();

        const insertPessoaTest = () => {
            const pessoas = [
                // {
                //     "tabela": "preso.tb_preso_sincronizacao",
                //     "pessoa_tipo_tabela_id": 1,
                //     "pess_id_gpu": null,
                //     "pess_id_gepen": "1191138",
                //     "pess_id_rh": null,
                //     "matricula": "1040401",
                //     "nome": "LUIZ CARLOS PERONDI ZAPAROLLI",
                //     "nome_social": null,
                //     "pai": "GUILHERME ZAPAROLLI",
                //     "mae": "ADILES MARIA PERONDI ZAPAROLLI",
                //     "rg": "71759057",
                //     "cpf": "86602730959",
                //     "data_nascimento": "21/01/1970",
                //     "perfis": "PRESO",
                //     "aliases": "",
                //     "rs": null,
                //     "idTr": "19340c06-6a05-4a38-9f17-01537b7b40d0",
                //     "idTrSelecionado": "4f9582c3-eabd-4a52-8f38-0d31ae4d1881",
                //     "idsTrs": [
                //         "19340c06-6a05-4a38-9f17-01537b7b40d0"
                //     ]
                // },
                // {
                //     "tabela": "preso.tb_preso_sincronizacao",
                //     "pessoa_tipo_tabela_id": 1,
                //     "pess_id_gpu": null,
                //     "pess_id_gepen": "1088672",
                //     "pess_id_rh": null,
                //     "matricula": "939780",
                //     "nome": "JEFFERSON HENRIQUE ISRAEL SIMEAO",
                //     "nome_social": null,
                //     "pai": "ROSIMEIRE ISRAEL",
                //     "mae": "SIDNEY EURIPEDES SIMEAO",
                //     "rg": "71.311.813-1",
                //     "cpf": "42991652845",
                //     "data_nascimento": "01/05/1995",
                //     "perfis": "PRESO",
                //     "aliases": "",
                //     "rs": null,
                //     "idTr": "2c424b6a-75eb-495f-937f-d1ae0cb45cf4",
                //     "idTrSelecionado": "00259de8-72f7-4991-94ae-29eccd6c376c",
                //     "idsTrs": [
                //         "2c424b6a-75eb-495f-937f-d1ae0cb45cf4"
                //     ],
                //     "idCol": "840c5151-27c2-4a2c-8dbd-76fc5eb27131"
                // },
                // {
                //     "tabela": "funcionario.tb_funcionario",
                //     "pessoa_tipo_tabela_id": 3,
                //     "pess_id_gpu": null,
                //     "pess_id_gepen": null,
                //     "pess_id_rh": "135946",
                //     "matricula": null,
                //     "nome": "ALESSANDRO ANTONIO RODRIGUES RIBEIRO",
                //     "nome_social": null,
                //     "pai": null,
                //     "mae": null,
                //     "rg": "329232861           ",
                //     "cpf": "27573505876",
                //     "data_nascimento": null,
                //     "perfis": "FUNCIONARIO",
                //     "aliases": null,
                //     "rs": "16799331",
                //     "idTr": "2877cc7a-ad18-4145-99bd-708c910cd441",
                //     "idTrSelecionado": "010ba824-a3ea-4149-912b-2dd59157ed6d",
                //     "idsTrs": [
                //         "2877cc7a-ad18-4145-99bd-708c910cd441"
                //     ],
                //     "idCol": "57b4ea3e-26dd-403d-a083-5cd0660bb7dc"
                // },
                {
                    "tabela": "funcionario.tb_funcionario",
                    "pessoa_tipo_tabela_id": 3,
                    "pess_id_gpu": null,
                    "pess_id_gepen": null,
                    "pess_id_rh": "135956",
                    "matricula": null,
                    "nome": "ANA KARINA DE LABIO",
                    "nome_social": null,
                    "pai": null,
                    "mae": null,
                    "rg": "292258161           ",
                    "cpf": "29899596884",
                    "data_nascimento": null,
                    "perfis": "FUNCIONARIO",
                    "aliases": null,
                    "rs": "16799630",
                    "idTr": "203631a9-18a1-448b-b802-1503371b6365",
                    "idTrSelecionado": "e343bdbe-9e3c-4903-a21d-ce36561c2ddb",
                    "idsTrs": [
                        "203631a9-18a1-448b-b802-1503371b6365"
                    ],
                    "idCol": "e0630981-e6c7-4dcb-bc11-3f225ee535b0"
                },
                // {
                //     "tabela": "funcionario.tb_funcionario",
                //     "pessoa_tipo_tabela_id": 3,
                //     "pess_id_gpu": null,
                //     "pess_id_gepen": null,
                //     "pess_id_rh": "134640",
                //     "matricula": null,
                //     "nome": "BRUNO RAMOS PEREIRA",
                //     "nome_social": null,
                //     "pai": null,
                //     "mae": null,
                //     "rg": "402046018           ",
                //     "cpf": "34838935803",
                //     "data_nascimento": null,
                //     "perfis": "FUNCIONARIO",
                //     "aliases": null,
                //     "rs": "16799987",
                //     "idTr": "5e544826-e8a9-4293-a3a3-183ebfa56347",
                //     "idTrSelecionado": "10856bef-70c4-4b3b-9823-cd5af54e8caa",
                //     "idsTrs": [
                //         "5e544826-e8a9-4293-a3a3-183ebfa56347"
                //     ],
                //     "idCol": "122059c3-b799-49ee-8c6c-629a2018d314"
                // },
                // {
                //     "tabela": "funcionario.tb_funcionario",
                //     "pessoa_tipo_tabela_id": 3,
                //     "pess_id_gpu": null,
                //     "pess_id_gepen": null,
                //     "pess_id_rh": "135958",
                //     "matricula": null,
                //     "nome": "DANIEL DE OLIVEIRA PADILHA",
                //     "nome_social": null,
                //     "pai": null,
                //     "mae": null,
                //     "rg": "423601921           ",
                //     "cpf": "33946814808",
                //     "data_nascimento": null,
                //     "perfis": "FUNCIONARIO",
                //     "aliases": null,
                //     "rs": "16799938",
                //     "idTr": "b4bfe5c8-4681-490f-8422-78d76da76500",
                //     "idTrSelecionado": "b50bec54-5482-4b40-a67b-a23fa4a35519",
                //     "idsTrs": [
                //         "b4bfe5c8-4681-490f-8422-78d76da76500"
                //     ],
                //     "idCol": "a07cd4a0-27f5-4848-b22c-4fa6b3271b4c"
                // },
                // {
                //     "tabela": "funcionario.tb_funcionario",
                //     "pessoa_tipo_tabela_id": 3,
                //     "pess_id_gpu": null,
                //     "pess_id_gepen": null,
                //     "pess_id_rh": "135940",
                //     "matricula": null,
                //     "nome": "ANDERSON CLEITON LIRA PEREIRA",
                //     "nome_social": null,
                //     "pai": null,
                //     "mae": null,
                //     "rg": "272945249           ",
                //     "cpf": "07873652700",
                //     "data_nascimento": null,
                //     "perfis": "FUNCIONARIO",
                //     "aliases": null,
                //     "rs": "16799574",
                //     "idTr": "25c83e63-6d32-4021-8663-fe1b9696a1b4",
                //     "idTrSelecionado": "8225b257-4d2e-492e-b1aa-4250c1f6ac2f",
                //     "idsTrs": [
                //         "25c83e63-6d32-4021-8663-fe1b9696a1b4"
                //     ],
                //     "idCol": "2c5f1718-7a99-4d98-880c-58e7ec82f22a"
                // },
                // {
                //     "tabela": "funcionario.tb_funcionario",
                //     "pessoa_tipo_tabela_id": 3,
                //     "pess_id_gpu": null,
                //     "pess_id_gepen": null,
                //     "pess_id_rh": "136012",
                //     "matricula": null,
                //     "nome": "DANILO APARECIDO LUZIANO DA SILVA",
                //     "nome_social": null,
                //     "pai": null,
                //     "mae": null,
                //     "rg": "33703493X           ",
                //     "cpf": "33457193800",
                //     "data_nascimento": null,
                //     "perfis": "FUNCIONARIO",
                //     "aliases": null,
                //     "rs": "16799410",
                //     "idTr": "40e054a6-e84e-4ad4-9d80-f5c151b2a386",
                //     "idTrSelecionado": "417e4d77-be98-4ce6-a726-8174cbf4de2e",
                //     "idsTrs": [
                //         "40e054a6-e84e-4ad4-9d80-f5c151b2a386"
                //     ],
                //     "idCol": "44f4b283-68a5-40eb-a4e1-38c6512b0048"
                // },
                // {
                //     "tabela": "funcionario.tb_funcionario",
                //     "pessoa_tipo_tabela_id": 3,
                //     "pess_id_gpu": null,
                //     "pess_id_gepen": null,
                //     "pess_id_rh": "135968",
                //     "matricula": null,
                //     "nome": "DIEGO CHAGAS RIBEIRO NASCIMENTO",
                //     "nome_social": null,
                //     "pai": null,
                //     "mae": null,
                //     "rg": "418212958           ",
                //     "cpf": "36921427811",
                //     "data_nascimento": null,
                //     "perfis": "FUNCIONARIO",
                //     "aliases": null,
                //     "rs": "16799689",
                //     "idTr": "497ce1ee-cc9f-4be7-921f-6b9c3cb95f0f",
                //     "idTrSelecionado": "72a54e57-32c7-4f68-af7b-58a6a8dc52a6",
                //     "idsTrs": [
                //         "497ce1ee-cc9f-4be7-921f-6b9c3cb95f0f"
                //     ],
                //     "idCol": "bfa7db91-1e4a-4c6f-99b4-1d68728bcde3"
                // },
                // {
                //     "tabela": "funcionario.tb_funcionario",
                //     "pessoa_tipo_tabela_id": 3,
                //     "pess_id_gpu": null,
                //     "pess_id_gepen": null,
                //     "pess_id_rh": "135939",
                //     "matricula": null,
                //     "nome": "JOAO DONIZETI RIBEIRO JUNIOR",
                //     "nome_social": null,
                //     "pai": null,
                //     "mae": null,
                //     "rg": "426548838           ",
                //     "cpf": "35724361841",
                //     "data_nascimento": null,
                //     "perfis": "FUNCIONARIO",
                //     "aliases": null,
                //     "rs": "16799550",
                //     "idTr": "09d0c112-ec37-46dc-aa31-347b680dfd1a",
                //     "idTrSelecionado": "983931a7-cf20-4498-a4d6-68f8e9febe46",
                //     "idsTrs": [
                //         "09d0c112-ec37-46dc-aa31-347b680dfd1a"
                //     ],
                //     "idCol": "98273f7b-d772-4588-8fea-e7c714450b64"
                // },
                // {
                //     "tabela": "funcionario.tb_funcionario",
                //     "pessoa_tipo_tabela_id": 3,
                //     "pess_id_gpu": null,
                //     "pess_id_gepen": null,
                //     "pess_id_rh": "135956",
                //     "matricula": null,
                //     "nome": "JONATAS RAVEL FERREIRA TOVANI",
                //     "nome_social": null,
                //     "pai": null,
                //     "mae": null,
                //     "rg": "454964717           ",
                //     "cpf": "42971211827",
                //     "data_nascimento": null,
                //     "perfis": "FUNCIONARIO",
                //     "aliases": null,
                //     "rs": "16799999",
                //     "idTr": "4b93cba3-f6c0-42c8-a73d-759888c97419",
                //     "idTrSelecionado": "ed4a4649-34e5-4414-aae9-0fd10f458765",
                //     "idsTrs": [
                //         "4b93cba3-f6c0-42c8-a73d-759888c97419"
                //     ],
                //     "idCol": "7b95c418-a359-4ae4-a8c8-837cab60ff8e"
                // },
                // {
                //     "tabela": "pessoa.tb_pessoa",
                //     "pessoa_tipo_tabela_id": 2,
                //     "pess_id_gpu": "1147208",
                //     "pess_id_gepen": null,
                //     "pess_id_rh": null,
                //     "matricula": null,
                //     "nome": "ALFREDO BELLUSCI",
                //     "nome_social": null,
                //     "pai": null,
                //     "mae": null,
                //     "rg": null,
                //     "cpf": "24865625836",
                //     "data_nascimento": "09/12/1975",
                //     "perfis": "ADVOGADO",
                //     "aliases": "",
                //     "rs": null,
                //     "idTr": "5de9d971-07ed-4d29-9089-df277e02358d",
                //     "idTrSelecionado": "70b66b73-4d88-4d4d-9241-2732140a6ae8",
                //     "idsTrs": [
                //         "5de9d971-07ed-4d29-9089-df277e02358d"
                //     ],
                //     "idCol": "427ad671-9ced-4767-b238-f66e59598a93"
                // },
                // {
                //     "tabela": "pessoa.tb_pessoa",
                //     "pessoa_tipo_tabela_id": 2,
                //     "pess_id_gpu": "1328941",
                //     "pess_id_gepen": null,
                //     "pess_id_rh": null,
                //     "matricula": null,
                //     "nome": "ALVARO FERREIRA EGEA",
                //     "nome_social": null,
                //     "pai": null,
                //     "mae": null,
                //     "rg": "60274757",
                //     "cpf": "70318921804",
                //     "data_nascimento": "17/12/1954",
                //     "perfis": "ADVOGADO",
                //     "aliases": "",
                //     "rs": null,
                //     "idTr": "9a70b6f8-5a8b-41b6-821e-9724e2eed86b",
                //     "idTrSelecionado": "c0b91109-3a5c-4902-93fd-e16819af3e14",
                //     "idsTrs": [
                //         "9a70b6f8-5a8b-41b6-821e-9724e2eed86b"
                //     ],
                //     "idCol": "82ee6603-ead3-46f5-b039-3bde8fb346ba"
                // },
                // {
                //     "tabela": "pessoa.tb_pessoa",
                //     "pessoa_tipo_tabela_id": 2,
                //     "pess_id_gpu": "1490131",
                //     "pess_id_gepen": null,
                //     "pess_id_rh": null,
                //     "matricula": null,
                //     "nome": "ANDERSON SOUZA ALENCAR",
                //     "nome_social": null,
                //     "pai": null,
                //     "mae": null,
                //     "rg": null,
                //     "cpf": "11702985830",
                //     "data_nascimento": "03/09/1972",
                //     "perfis": "ADVOGADO",
                //     "aliases": "",
                //     "rs": null,
                //     "idTr": "df35743a-6253-4d82-b453-cdb767f93d0d",
                //     "idTrSelecionado": "a7610a31-434c-49eb-8a42-be019712cad6",
                //     "idsTrs": [
                //         "df35743a-6253-4d82-b453-cdb767f93d0d"
                //     ],
                //     "idCol": "9eba32e8-85ec-4a04-b74d-4156565e0247"
                // },
                // {
                //     "tabela": "pessoa.tb_pessoa",
                //     "pessoa_tipo_tabela_id": 2,
                //     "pess_id_gpu": "1352970",
                //     "pess_id_gepen": null,
                //     "pess_id_rh": null,
                //     "matricula": null,
                //     "nome": "ANDRE LUIZ MARCONDES DE ARAUJO",
                //     "nome_social": null,
                //     "pai": null,
                //     "mae": null,
                //     "rg": null,
                //     "cpf": null,
                //     "data_nascimento": "16/09/1973",
                //     "perfis": "ADVOGADO",
                //     "aliases": "",
                //     "rs": null,
                //     "idTr": "420fb07e-5623-426b-9dd2-522dc02337f2",
                //     "idTrSelecionado": "586c8fa9-2dee-4a04-bd84-9080cbfbbb57",
                //     "idsTrs": [
                //         "420fb07e-5623-426b-9dd2-522dc02337f2"
                //     ],
                //     "idCol": "b7cb2645-fd0c-490f-8e5f-985ee61f1773"
                // }
            ]

            pessoas.forEach(element => {
                self.#inserirPessoasEnvolvidas(element);
            });
        }
        // insertPessoaTest();
    }

    async  #inserirPessoasEnvolvidas(pessoa) {
        const self = this;
        const divEnvolvidos = $(`#divEnvolvidos${self.#sufixo}Ligacoes`);

        const verifica = self.#verificaPessoaEnvolvidaNaTela(pessoa);
        if (verifica) {
            commonFunctions.generateNotification(`A pessoa <b>${pessoa.nome}</b> já consta como envolvida.`, 'warning');
            $(`#${verifica.idCol}`).focus();
            return
        }

        pessoa.idCol = UUIDHelper.generateUUID();
        let campoEspecifico = '';
        switch (pessoa.pessoa_tipo_tabela_id) {
            case 1:
                let matricula = pessoa.matricula ? funcoesPresos.retornaMatriculaFormatada(`${pessoa.matricula}${funcoesPresos.retornaDigitoMatricula(pessoa.matricula)}`, 1) : 'Sem Matrícula';
                campoEspecifico = `<b>ID Preso:</b> ${pessoa.referencia_id}<br>`;
                campoEspecifico += `<b>Matricula:</b> ${matricula}`;
                break;

            case 2:
                campoEspecifico = `<b>ID Pessoa:</b> ${pessoa.referencia_id}<br>`;
                campoEspecifico += `<b>CPF: </b> ${commonFunctions.formatCPF(pessoa.cpf)}`;
                break;

            case 3:
                campoEspecifico = `<b>ID Funcionário RH:</b> ${pessoa.referencia_id}<br>`;
                campoEspecifico += `<b>CPF:</b> ${commonFunctions.formatCPF(pessoa.cpf)}<br>`;
                campoEspecifico += `<b>RS:</b> ${pessoa.rs}`;
                break;

            default:
                commonFunctions.generateNotification(`Tipo de pessoa não reconhecido.`, 'error', { itemsArray: [`Tipo informado: ${pessoa.pessoa.tipo_tabela_id}.`] });
                return false;
        }

        const cpf = pessoa.cpf ? commonFunctions.formatCPF(pessoa.cpf) : '';
        let removerPessoa = {};
        // Verifica se a pessoa foi criada há menos de 1 hora
        if (DateTimeHelper.ehMenosDeXHoras(pessoa.created_at, 1)) {
            removerPessoa.btn = `
        <button class="btn btn-outline-danger btnRemoverEnvolvido">
            <i class="bi bi-trash"></i>
            Remover
        </button>`;
            const novaData = DateTimeHelper.retornaSomaDataEHora(pessoa.created_at, 1, 4);
            removerPessoa.message = `<div class="form-text fst-italic mb-0">A associação desta pessoa pode ser removida até ${DateTimeHelper.retornaDadosDataHora(novaData, 12)}.</div>`;
        }

        let nomeSocial = pessoa.nome_social ? `<b>Nome social:</b> ${pessoa.nome_social}` : '';
        let strCard = `
            <div id="${pessoa.idCol}" class="col">
                <div class="card h-100" style="max-width: 20rem;">
                    <div class="card-img-top d-flex justify-content-center p-3 divFotoFrontal">
                        <i class="bi bi-image-fill" style="font-size: 12rem; line-height: 2rem"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">${campoEspecifico}</h6>
                        <h5 class="card-title">${pessoa.nome}</h5>
                        ${nomeSocial}
                        <div class="accordion mt-2" id="accordion${pessoa.idCol}">
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button py-1 collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseOne${pessoa.idCol}" aria-expanded="false"
                                        aria-controls="collapseOne${pessoa.idCol}">
                                        Dados da pessoa
                                    </button>
                                </div>
                                <div id="collapseOne${pessoa.idCol}" class="accordion-collapse collapse"
                                    data-bs-parent="#accordion${pessoa.idCol}">
                                    <div class="accordion-body">
                                        <p class="card-text"><b>Perfil(is): </b>${pessoa.perfis ?? ''}</p>
                                        <p class="card-text"><b>Data nasc.: </b>${pessoa.data_nascimento ?? ''}</p>
                                        <p class="card-text"><b>Mãe: </b>${pessoa.mae ?? ''}</p>
                                        <p class="card-text"><b>Pai: </b>${pessoa.pai ?? ''}</p>
                                        <p class="card-text"><b>RG: </b>${pessoa.rg ?? ''}</p>
                                        <p class="card-text"><b>CPF: </b>${cpf}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row flex-fill">
                            <div class="col mt-2 justify-content-end d-flex gap-2">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Sobre o preso
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item"
                                                href="http://10.14.5.121/gpu/web/qualificativa/pdf/imprimirQualificativaCompletaPDF.php?idPreso=1191138"
                                                target="_blank" rel="noopener noreferrer">
                                                Qualificativa Completa
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="http://10.14.5.121/gpu/web/qualificativa/pdf/imprimirQualificativaPDF.php?idPreso=1191138"
                                                target="_blank" rel="noopener noreferrer">
                                                Qualificativa Simples
                                            </a>
                                        </li>
                                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                                    </ul>
                                </div>
                                <div>${removerPessoa.btn ?? ''}</div>
                            </div>
                        </div>
                        ${removerPessoa.message ?? ''}
                    </div>
                </div>
            </div>
        `;

        if (self.#inserirDadosPessoaEnvolvidaNaTela(pessoa)) {
            divEnvolvidos.append(strCard);
            self.#buscaFotoPessoa(pessoa);
            return true;
        }
        return false;
    }

    async #buscaFotoPessoa(pessoa) {
        const self = this;

        try {
            let objDataBusca = {};
            switch (pessoa.pessoa_tipo_tabela_id) {
                case 1:
                    objDataBusca.endpoint = `foto-preso/id/${pessoa.referencia_id}`;
                    objDataBusca.alt = "Foto preso";

                    break;
                case 2:
                    objDataBusca.endpoint = `foto-pessoa/id/${pessoa.referencia_id}`;
                    objDataBusca.alt = "Foto pessoa";
                    // commonFunctions.generateNotification('Rota de busca de foto de pessoas em geral em desenvolvimento', 'warning');
                    return false;
                    break;
                case 3:
                    objDataBusca.endpoint = `foto-funcionario/id/${pessoa.referencia_id}`;
                    objDataBusca.alt = "Foto funcionário";
                    // commonFunctions.generateNotification('Rota de busca de foto de funcionário em desenvolvimento', 'warning');
                    return false;
                default:
                    console.error('Tipo tabela pessoa inválido.', pessoa);
                    return false;
                    break;
            }

            const objConnFoto = new connectAjax(`${self.#objConfigs.url.foto}/${objDataBusca.endpoint}`);
            const response = await objConnFoto.getRequest();
            let strFoto = '';
            if (response?.data?.caminhoFoto?.length > 0) {
                strFoto = `<img src="${response.data.caminhoFoto[0]}" class="img-thumbnail" alt="${objDataBusca.alt} ${pessoa.nome}" style="max-height: 15rem;">`;
                $(`#${pessoa.idCol}`).find('.divFotoFrontal').html(strFoto);
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }

    }

    /**
     * Função para inserir uma pessoa na lista de pessoas envolvidas.
     * 
     * @param {Object} pessoa - Objeto contendo os dados da pessoa.
     */
    #inserirDadosPessoaEnvolvidaNaTela(pessoa) {
        const self = this;

        // Insere a pessoa no array `pessoasEnvolvidasNaTela`
        self.#objConfigs.data.pessoasEnvolvidasNaTela.push({
            id: pessoa.id ?? null,
            pessoa_tipo_tabela_id: pessoa.pessoa_tipo_tabela_id,
            nome: pessoa.nome,
            referencia_id: pessoa.referencia_id,
            idCol: pessoa.idCol
        });

        return true;
    }

    /**
     * Verifica se a pessoa envolvida já está na tela.
     * 
     * @param {Object} item - O objeto da pessoa que será verificado.
     * @returns {Object|null} - Retorna o elemento correspondente se a pessoa já estiver envolvida, ou null caso contrário.
     */
    #verificaPessoaEnvolvidaNaTela(item) {
        const self = this;

        for (const element of self.#objConfigs.data.pessoasEnvolvidasNaTela) {
            if (element.pessoa_tipo_tabela_id == item.pessoa_tipo_tabela_id && element.referencia_id == item.referencia_id) {
                return element; // Pessoa já está envolvida
            }
        }

        return null; // Pessoa não encontrada
    }

    #saveButtonAction() {
        const self = this;
        const formRegistration = $(`#formInfoSubj${self.#sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.pessoas_envolvidas = self.#objConfigs.data.pessoasEnvolvidasNaTela;

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
            blnSave = commonFunctions.verificationData(data.categoria_id, { field: formRegistration.find('select[name="categoria_id"]'), messageInvalid: 'Uma categoria deve ser selecionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
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
            if (response) {
                RedirectHelper.redirectWithUUIDMessage(window.frontRoutes.frontRedirect, 'Dados enviados com sucesso!', 'success');
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            commonFunctions.simulateLoading(btnSave, false);
        };
    }

    async #buscarDados() {
        const self = this;

        try {
            await commonFunctions.loadingModalDisplay();
            const response = await self.#getRecurse();
            if (response?.data) {
                const responseData = response.data;
                const form = $(`#formInfoSubj${self.#sufixo}`);
                form.find('input[name="titulo"]').val(responseData.titulo).attr('readonly', true);
                commonFunctions.updateSelect2Value($(`#categoria_id${self.#sufixo}`), responseData.categoria.nome, responseData.categoria_id);
                $(`#categoria_id${self.#sufixo}`).attr('disabled', true);
                form.find('textarea[name="descricao"]').val(responseData.descricao).attr('readonly', true);

                responseData.pessoas_envolvidas.forEach(envolvido => {
                    self.#inserirPessoasEnvolvidas({
                        id: envolvido.id,
                        referencia_id: envolvido.referencia_id,
                        pessoa_tipo_tabela_id: envolvido.pessoa_tipo_tabela_id,
                        nome: envolvido.pessoa.nome,
                        pai: envolvido.pessoa.pai ?? null,
                        mae: envolvido.pessoa.mae ?? null,
                        nome_social: envolvido.pessoa.nome_social ?? null,
                        matricula: envolvido.pessoa.matricula ?? null,
                        cpf: envolvido.pessoa.cpf ?? null,
                        rs: envolvido.pessoa.rs ?? null,
                        created_at: envolvido.created_at,
                    });
                });
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
}

$(function () {
    new PageFormInformacaoSubjetivaForm();
});