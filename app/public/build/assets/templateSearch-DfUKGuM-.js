var v=Object.defineProperty;var P=s=>{throw TypeError(s)};var A=(s,e,t)=>e in s?v(s,e,{enumerable:!0,configurable:!0,writable:!0,value:t}):s[e]=t;var m=(s,e,t)=>A(s,typeof e!="symbol"?e+"":e,t),E=(s,e,t)=>e.has(s)||P("Cannot "+t);var S=(s,e,t)=>e.has(s)?P("Cannot add the same private member more than once"):e instanceof WeakSet?e.add(s):e.set(s,t);var d=(s,e,t)=>(E(s,e,"access private method"),t);import{m as w}from"./modalMessage-CWFhqvtB.js";import{a as C,U as O}from"./UUIDHelper-C7Qwh3zO.js";import{c as f,a as x,e as j}from"./commonFunctions-iccisCSl.js";var u,D,p,Q,k;class F{constructor(e){S(this,u);m(this,"_sufixo");m(this,"_objConfigs",{runningSearchBln:!1,typeCurrentSearch:void 0});this._sufixo=e.sufixo,this._objConfigs=Object.assign(this._objConfigs,e.objConfigs??{}),d(this,u,D).call(this)}get getSufixo(){return this._sufixo}set _setTypeCurrentSearch(e){this._objConfigs.typeCurrentSearch=e}async _generateQueryFilters(e={}){const t=this,{formDataSearch:a=e.formDataSearch??$(`#formDataSearch${t.getSufixo}`)}=e,n=a.find('input[name="texto"]').val();let i=[],r={texto:n,parametros_like:t._returnQueryParameters(a.find('select[name="selFormaBusca"]').val()),ordenacao:[{campo:a.find('select[name="selCampoOrdenacao"]').val()??"nome",direcao:a.find('input[name="direcaoConsulta"]:checked').val()}],texto_tratamento:{tratamento:a.find('select[name="selTratamentoTexto"]').val()},filtros:{campos_busca:[]},page:1};const o=f.getInputsValues(a.find(".searchFields"));if(Object.keys(o).forEach(l=>{o[l]===!0&&r.filtros.campos_busca.push(l)}),i.length>0)return f.generateNotification("Não foi possivel realizar a busca. Verifique as seguintes recomendações:","info",{itemsArray:i});await t._getData(r)}_returnQueryParameters(e){switch(e){case"iniciado_por":return{curinga_inicio_bln:!1,curinga_final_bln:!0};case"terminado_por":return{curinga_inicio_bln:!0,curinga_final_bln:!1};case"qualquer_incidencia":return{curinga_inicio_bln:!0,curinga_final_bln:!0};default:return{curinga_inicio_bln:!1,curinga_final_bln:!1}}}async _getData(e,t=1){var l,c;const a=this;if(a._objConfigs.runningSearchBln){f.generateNotification("Busca em andamento. Aguarde...","info");return}let n=d(l=a,u,p).call(l);if(!n)return;const i=$(n.btnSearch??`#btnBuscar${a.getSufixo}`),r=$(n.tbody??`#tableData${a.getSufixo} tbody`),o=$(n.footerPagination??`#footerPagination${a.getSufixo}`);a._objConfigs.runningSearchBln=!0;try{f.simulateLoading(i),a._paginationDefault({footerPagination:o}),r.html(""),a._refreshQueryQuantity("Consultando...",{footerPagination:o}),a._refreshQueryStatus("Efetuando busca. Aguarde...",{footerPagination:o});const g=await new x(n.urlSearch);e.page=t,g.setAction(j.POST),g.setData(e);const _=await g.envRequest();if(a._refreshQueryStatus("Busca concluída. Preenchendo os dados...",{footerPagination:o}),_.data){const h=_.data;let y=[];for(let b of h.data){const q=C.generateUUID();b=Object.assign(b,{idTr:q});const R=await a.insertTableData(b,{config:n,tbody:r});y.push(b)}a._refreshQueryQuantity(h.total,{footerPagination:o}),d(c=a,u,Q).call(c,h,{footerPagination:o,dataPost:e}),n.dataPost=e,n.recordsOnScreen=y}else a._refreshQueryQuantity(0,{footerPagination:o}),a._paginationDefault({footerPagination:o}),n.dataPost=e,n.recordsOnScreen=[]}catch(g){f.generateNotificationErrorCatch(g),o.find(".totalRegistros").html(0)}finally{f.simulateLoading(i,!1),a._refreshQueryStatus("Aguardando comando do usuário...",{footerPagination:o}),a._objConfigs.runningSearchBln=!1}}_paginationDefault(e={}){const t=this,{footerPagination:a=$(e.footerPagination??$(`#footerPagination${t.getSufixo}`)),pagination:n=$(e.pagination??$(a).find(".pagination"))}=e;n.html(`
            <li class="page-item disabled">
                <button type="button" class="page-link" aria-label="Anterior">
                    <span aria-hidden="true">&laquo; Anterior</span>
                </button>
            </li>
            <li class="page-item disabled">
                <button type="button" class="page-link" aria-label="Próximo">
                    <span aria-hidden="true">Pr&oacute;xima &raquo;</span>
                </button>
            </li>
        `)}_refreshQueryStatus(e,t={}){const a=this,{footerPagination:n=$(t.footerPagination??$(`#footerPagination${a.getSufixo}`)),selector:i=t.selector??n.find(".queryStatus")}=t;i&&i.html(e)}_refreshQueryQuantity(e,t={}){const a=this,{footerPagination:n=$(t.footerPagination??$(`#footerPagination${a.getSufixo}`)),selector:i=t.selector??n.find(".totalRegisters")}=t;$(i).html(e)}async _delButtonAction(e,t,a={}){const n=this,{button:i=null,title:r="Exclusão de Registro",message:o=`Confirma a exclusão do registro <b>${t}</b>?`,success:l="Registro excluído com sucesso!"}=a;try{const c=new w;c.setDataEnvModal={title:r,message:o},c.setFocusElementWhenClosingModal=i,(await c.modalOpen()).confirmResult&&await n._delRecurse(e,a)&&(f.generateNotification(l,"success"),n._generateQueryFilters())}catch(c){f.generateNotificationErrorCatch(c)}}async _delRecurse(e,t={}){var i;let n=d(i=this,u,p).call(i);if(n)try{const r=new x(n.url);return r.setParam(e),r.setAction(j.DELETE),await r.deleteRequest(),!0}catch(r){return f.generateNotificationErrorCatch(r),!1}}}u=new WeakSet,D=function(){},p=function(){const e=this;for(const t of Object.values(e._objConfigs.querys))if(t.name==e._objConfigs.typeCurrentSearch)return t;return f.generateNotification("O tipo de busca informado não foi encontrado.","error"),!1},Q=async function(e,t){var r;const a=this,{footerPagination:n=$(t.footerPagination??`#footerPagination${a.getSufixo}`),selector:i=$(t.pagination??$(n).find(".pagination"))}=t;i.html("");for(const o of e.links){let l="";const c=C.generateUUID();switch(o.label){case"&laquo; Previous":l=`
                    <li class="page-item ${o.url?"":"disabled"}">
                        <button id="${c}" type="button" class="page-link" aria-label="Anterior">
                            <span aria-hidden="true">&laquo; Anterior</span>
                        </button>
                    </li>`;break;case"Next &raquo;":l=`
                    <li class="page-item ${o.url?"":"disabled"}">
                        <button id="${c}" type="button" class="page-link" aria-label="Próximo">
                            <span aria-hidden="true">Pr&oacute;xima &raquo;</span>
                        </button>
                    </li>`;break;case"...":l=`
                    <li class="page-item disabled">
                        <button id="${c}" type="button" class="page-link">${o.label}</button>
                    </li>`;break;default:e.total&&(l=`
                    <li class="page-item ${o.active?"active":""}">
                        <button id="${c}" type="button" class="page-link">${o.label}</button>
                    </li>`);break}i.append(l),d(r=a,u,k).call(r,c,o,t)}},k=function(e,t,a){const n=this,{dataPost:i=a.dataPost}=a;if(t.url){const r=O.getParameterURL("page",t.url);if(r){const o=n._objConfigs.typeCurrentSearch;$(`#${e}`).on("click",function(){n._objConfigs.typeCurrentSearch=o,n._getData(i,r)})}}};export{F as t};