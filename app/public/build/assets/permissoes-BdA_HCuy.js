var N=l=>{throw TypeError(l)};var L=(l,c,t)=>c.has(l)||N("Cannot "+t);var f=(l,c,t)=>(L(l,c,"read from private field"),t?t.call(l):c.get(l)),p=(l,c,t)=>c.has(l)?N("Cannot add the same private member more than once"):c instanceof WeakSet?c.add(l):c.set(l,t);var r=(l,c,t)=>(L(l,c,"access private method"),t);import{c as s,e as F}from"./commonFunctions-iccisCSl.js";import{t as Z}from"./templateSearch-DfUKGuM-.js";import{m as A,a as tt}from"./modalSearchAndFormRegistration-3_gFT7xi.js";import"./modalMessage-CWFhqvtB.js";import"./UUIDHelper-C7Qwh3zO.js";var M,R,g,q,I;class H extends A{constructor(){super({idModal:"#modalCode"});p(this,g);p(this,M,{url:{base:void 0}});p(this,R,{idRegister:void 0});this._objConfigs=Object.assign(this._objConfigs,f(this,M)),this._dataEnvModal=Object.assign(this._dataEnvModal,f(this,R))}async modalOpen(){var e;const t=this;return r(this,g,q).call(this),await r(e=t,g,I).call(e)?(await t._modalHideShow(),await t._modalOpen()):await t._modalOpen()}_modalReset(){const t=this;$(t.getIdModal).find(".codeBlock, .class, .path").html("")}}M=new WeakMap,R=new WeakMap,g=new WeakSet,q=function(){const t=this;t._dataEnvModal.url&&(t._objConfigs.url.base=t._dataEnvModal.url),$(t.getIdModal).find(".btn-refresh").on("click",function(){var e;r(e=t,g,I).call(e),s.generateNotification("Registro atualizado com sucesso","success")})},I=async function(){const t=this,e=t._dataEnvModal;let o=new Set;if(t._objConfigs.url.base||o.add("URL não informada"),e.idRegister||o.add("ID não informado"),o.size)return s.generateNotification("Não foi possível carregar o registro. Verifique os seguintes erros:","warning",{itemsArray:o.values()}),!1;try{const a=await t._getRecurse();a&&($(t.getIdModal).find(".codeBlock").html(a.data.code),$(t.getIdModal).find(".class").html(a.data.class),$(t.getIdModal).find(".path").html(a.data.path))}catch(a){s.generateNotificationErrorCatch(a)}return!0};var x,C,m,B,O,D,z,Q;class et extends tt{constructor(){super({idModal:"#modalPermissaoGrupo"});p(this,m);p(this,x,{formRegistros:!0,querys:{consultaFiltros:{name:"consulta-filtros",url:window.apiRoutes.basePermissoesGrupos,urlSearch:`${window.apiRoutes.basePermissoesGrupos}/consulta-filtros`}}});p(this,C,{});this._objConfigs=Object.assign(this._objConfigs,f(this,x)),this._promisseReturnValue=Object.assign(this._promisseReturnValue,f(this,C)),r(this,m,B).call(this)}async modalOpen(){const t=this;return await t._modalHideShow(),await t._modalOpen()}async insertTableData(t,e={}){var u,h,_,w,v;const o=this,{tbody:a}=e,i=t.ativo?"Sim":"Não",n=t.individuais?"Sim":"Não";return $(a).append(`
            <tr id=${t.idTr}>
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-edit" title="Editar registro"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-php" title="Renderizar dados para backend PHP"><i class="bi bi-filetype-php"></i></button>
                        <button type="button" class="btn btn-outline-info btn-sm btn-view" title="Visualizar detalhes"><i class="fa-solid fa-circle-info"></i></button>
                    </div>
                </td>
                <td class="text-center text-nowrap">${t.id}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${t.nome}">${t.nome}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${t.descricao??""}">${t.descricao??"**"}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${((u=t.modulo)==null?void 0:u.nome)??""}">${((h=t.modulo)==null?void 0:h.nome)??"**"}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${((_=t.grupo_pai)==null?void 0:_.nome)??""}">${((w=t.grupo_pai)==null?void 0:w.nome)??"**"}</td>
                <td class="text-nowrap">${n}</td>
                <td class="text-nowrap">${i}</td>
            </tr>
        `),r(v=o,m,z).call(v,t),!0}saveButtonAction(){var a;const t=this,e=$(t.getIdModal).find(".formRegistration");let o=s.getInputsValues(e[0]);r(a=t,m,Q).call(a,o,e)&&t._save(o,window.apiRoutes.basePermissoesGrupos)}}x=new WeakMap,C=new WeakMap,m=new WeakSet,B=function(){var a;const t=this,e=$(t._idModal),o=$(e).find("#formDataSearchModalPermissaoGrupo");t._objConfigs.typeCurrentSearch=t._objConfigs.querys.consultaFiltros.name,t._generateQueryFilters(),r(a=t,m,O).call(a),e.find(".btn-new-register").on("click",async()=>{t._updateTitleRegistration("Novo Grupo de Permissões")}),o.find(".btnBuscar").on("click",async i=>{i.preventDefault(),t._setTypeCurrentSearch=t._objConfigs.querys.consultaFiltros.name,t._generateQueryFilters()}),e.find('select[name="modulo_id"]').on("change",async function(){var i;r(i=t,m,D).call(i,$(this).val())})},O=async function(t=null){var a;const e=this;let o=t?{selectedIdOption:t}:{};await s.fillSelect($(e.getIdModal).find('select[name="modulo_id"]'),window.apiRoutes.baseModulos,o),await r(a=e,m,D).call(a)},D=async function(t=null){const e=this,o=$(e.getIdModal).find('select[name="grupo_pai_id"]'),a=$(e.getIdModal).find('select[name="modulo_id"]').val();if(!a){o.html('<option value="0">Selecione o módulo</option>');return}let i=t?{selectedIdOption:t}:{};const n=e._idRegister?`${window.apiRoutes.baseGrupos}/modulo/${a}/exceto-grupo/${e._idRegister}`:`${window.apiRoutes.baseGrupos}/modulo/${a}`;await s.fillSelect(o,n,i)},z=function(t){const e=this;$(`#${t.idTr}`).find(".btn-edit").on("click",async function(){var o;s.simulateLoading($(this));try{e._clearForm(),e._idRegister=t.id,e._action=F.PUT;const a=await e._getRecurse();if(a!=null&&a.data){const i=a.data;e._updateTitleRegistration(`Alterar: <b>${i.nome}</b>`);const n=$(e.getIdModal).find(".formRegistration");n.find('input[name="nome"]').val(i.nome),n.find('textarea[name="descricao"]').val(i.descricao),n.find('input[name="individuais"]').prop("checked",i.individuais),n.find('input[name="ativo"]').prop("checked",i.ativo),await r(o=e,m,O).call(o,i.modulo_id),n.find('select[name="grupo_pai_id"]').val(i.grupo_pai_id),e._actionsHideShowRegistrationFields(!0),e._executeFocusElementOnModal(n.find('input[name="nome"]'))}}catch(a){s.generateNotificationErrorCatch(a)}finally{s.simulateLoading($(this),!1)}}),$(`#${t.idTr}`).find(".btn-php").on("click",async function(){s.simulateLoading($(this));try{const o=new H;o.setDataEnvModal={idRegister:t.id,url:`${window.apiRoutes.baseGrupos}/php`},await e._modalHideShow(!1);const a=await o.modalOpen()}catch(o){s.generateNotificationErrorCatch(o)}finally{await e._modalHideShow(),s.simulateLoading($(this),!1)}})},Q=function(t,e){let o=s.verificationData(t.nome,{field:e.find('input[name="nome"]'),messageInvalid:"O nome do grupo deve ser informado.",setFocus:!0});return o=s.verificationData(t.modulo_id,{field:e.find('select[name="modulo_id"]'),messageInvalid:"O módulo deve ser selecionado.",setFocus:o==!0,returnForcedFalse:o==!1}),o};var E,P,j,d,U,J,k,y,T,K,W;class V extends A{constructor(){super({idModal:"#modalPermissao"});p(this,d);p(this,E,{idRegister:void 0});p(this,P,{url:{base:window.apiRoutes.basePermissoes}});p(this,j,{refresh:!1});this._objConfigs=Object.assign(this._objConfigs,f(this,P)),this._promisseReturnValue=Object.assign(this._promisseReturnValue,f(this,j)),this._dataEnvModal=Object.assign(this._dataEnvModal,f(this,E)),this._action=F.POST,r(this,d,U).call(this)}async modalOpen(){var e;const t=this;return t._dataEnvModal.idRegister&&await r(e=t,d,K).call(e),await t._modalHideShow(),await t._modalOpen()}_modalReset(){super._modalReset();const t=this;$(t.getIdModal).find("#dadosModalPermissao-tab").trigger("click")}saveButtonAction(){var a;const t=this,e=$(t.getIdModal).find(".formRegistration");let o=s.getInputsValues(e[0]);r(a=t,d,W).call(a,o,e)&&t._save(o,t._objConfigs.url.base)}}E=new WeakMap,P=new WeakMap,j=new WeakMap,d=new WeakSet,U=function(){r(this,d,J).call(this),r(this,d,k).call(this)},J=function(){const t=this,e=$(t._idModal);e.find(".openModalPermissaoGrupo").on("click",async function(){var a;const o=$(this);try{s.simulateLoading(o);const i=new et;await t._modalHideShow(!1),(await i.modalOpen()).refresh&&r(a=t,d,y).call(a,e.find('select[name="modulo_id"]').val())}catch(i){s.generateNotificationErrorCatch(i)}finally{await t._modalHideShow(),s.simulateLoading(o,!1)}}),e.find('select[name="modulo_id"]').on("change",async function(){var o,a;r(o=t,d,y).call(o,$(this).val()),r(a=t,d,T).call(a,$(this).val())})},k=async function(t=null){var i,n;const e=this;let o=t?{selectedIdOption:t}:{};const a=$(e.getIdModal).find('select[name="modulo_id"]');await s.fillSelect(a,window.apiRoutes.baseModulos,o),await r(i=e,d,y).call(i,a.val()),await r(n=e,d,T).call(n,a.val())},y=async function(t,e=null){const o=this,a=$(o.getIdModal).find('select[name="grupo_id"]');if(!t){a.html('<option value="0">Selecione o módulo</option>');return}let i=e?{selectedIdOption:e}:{};await s.fillSelect(a,`${window.apiRoutes.baseGrupos}/modulo/${t}`,i)},T=async function(t,e=null){const o=this,a=$(o.getIdModal).find('select[name="permissao_pai_id"]');if(!t){a.html('<option value="0">Selecione o módulo</option>');return}let i=e?{selectedIdOption:e}:{};const n=o._dataEnvModal.idRegister?`${o._objConfigs.url.base}/modulo/${t}/admin/exceto-permissao/${o._dataEnvModal.idRegister}`:`${o._objConfigs.url.base}/modulo/${t}/admin`;await s.fillSelect(a,n,i)},K=async function(){var e,o,a;const t=this;await s.loadingModalDisplay();try{t._clearForm(),t._action=F.PUT;const i=await t._getRecurse();if(i!=null&&i.data){const n=i.data;t._updateModalTitle(`Alterar: <b>${n.nome}</b>`);const u=$(t.getIdModal).find(".formRegistration");u.find('input[name="nome"]').val(n.nome),u.find('input[name="nome_completo"]').val(n.nome_completo),u.find('textarea[name="descricao"]').val(n.descricao),u.find('input[name="ativo"]').prop("checked",n.ativo),u.find('input[name="permite_subst_bln"]').prop("checked",n.permite_subst_bln),u.find('input[name="gerencia_perm_bln"]').prop("checked",n.gerencia_perm_bln),(o=(e=n.config)==null?void 0:e.grupo)!=null&&o.modulo_id?(await r(a=t,d,k).call(a,n.config.grupo.modulo_id),u.find('select[name="grupo_id"]').val(n.config.grupo_id),u.find('select[name="permissao_pai_id"]').val(n.config.permissao_pai_id)):(s.generateNotification("Esta permissão não possui configuração cadastrada. Favor completar o cadastro.","warning"),$(t.getIdModal).find("#configuracoesModalPermissao-tab").trigger("click")),t._executeFocusElementOnModal(u.find('input[name="nome"]'))}}catch(i){s.generateNotificationErrorCatch(i)}finally{await s.loadingModalDisplay(!1)}},W=function(t,e){let o=s.verificationData(t.nome,{field:e.find('input[name="nome"]'),messageInvalid:"O nome do grupo deve ser informado.",setFocus:!0});return o=s.verificationData(t.descricao,{field:e.find('input[name="descricao"]'),messageInvalid:"Uma descrição deve ser adicionada.",setFocus:o==!0,returnForcedFalse:o==!1}),o=s.verificationData(t.grupo_id,{field:e.find('select[name="grupo_id"]'),messageInvalid:"A permissão deve pertencer a um grupo, selecione um grupo.",setFocus:o==!0,returnForcedFalse:o==!1}),o};var S,b,X,Y;class ot extends Z{constructor(){super({sufixo:"PagePermissoes"});p(this,b);p(this,S,{querys:{consultaFiltros:{name:"consulta-filtros",url:window.apiRoutes.basePermissoes,urlSearch:`${window.apiRoutes.basePermissoes}/consulta-filtros`}}});this._objConfigs=Object.assign(this._objConfigs,f(this,S)),this.initEvents()}initEvents(){var e;const t=this;r(e=t,b,X).call(e),t._setTypeCurrentSearch=t._objConfigs.querys.consultaFiltros.name,t._generateQueryFilters()}async insertTableData(t,e={}){var n,u,h,_,w,v,G;const o=this,{tbody:a}=e,i=t.ativo?"Sim":"Não";return $(a).append(`
            <tr id=${t.idTr}>
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-edit" title="Editar registro"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-php" title="Renderizar dados para backend PHP"><i class="bi bi-filetype-php"></i></button>
                        <button type="button" class="btn btn-outline-info btn-sm btn-view" title="Visualizar detalhes"><i class="fa-solid fa-circle-info"></i></button>
                    </div>
                </td>
                <td class="text-center text-nowrap">${t.id}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${t.nome??""}">${t.nome}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${t.nome_completo??""}">${t.nome_completo??""}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${t.descricao??""}">${t.descricao??""}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${((n=t.grupo)==null?void 0:n.nome)??""}">${((u=t.grupo)==null?void 0:u.nome)??""}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${((_=(h=t.grupo)==null?void 0:h.modulo)==null?void 0:_.nome)??""}">${((v=(w=t.grupo)==null?void 0:w.modulo)==null?void 0:v.nome)??""}</td>
                <td class="text-nowrap">${i}</td>
            </tr>
        `),r(G=o,b,Y).call(G,t),!0}}S=new WeakMap,b=new WeakSet,X=function(){const t=this;$(`#formDataSearch${t.getSufixo}`).find(".btnBuscar").on("click",async function(e){e.preventDefault(),t._setTypeCurrentSearch=t._objConfigs.querys.consultaFiltros.name,t._generateQueryFilters()}),$("#btnInserirPermissao").on("click",async function(){const e=$(this);s.simulateLoading(e);try{(await new V().modalOpen()).refresh&&t._generateQueryFilters()}catch(o){s.generateNotificationErrorCatch(o)}finally{s.simulateLoading(e,!1)}})},Y=function(t){const e=this;$(`#${t.idTr}`).find(".btn-edit").on("click",async function(){s.simulateLoading($(this));try{const o=new V;o.setDataEnvModal={idRegister:t.id},(await o.modalOpen()).refresh&&(e._setTypeCurrentSearch=e._objConfigs.querys.consultaFiltros.name,e._generateQueryFilters())}catch(o){s.generateNotificationErrorCatch(o)}finally{s.simulateLoading($(this),!1)}}),$(`#${t.idTr}`).find(".btn-php").on("click",async function(){s.simulateLoading($(this));try{const o=new H;o.setDataEnvModal={idRegister:t.id,url:`${window.apiRoutes.basePermissoes}/php`};const a=await o.modalOpen()}catch(o){s.generateNotificationErrorCatch(o)}finally{s.simulateLoading($(this),!1)}})};$(function(){new ot});