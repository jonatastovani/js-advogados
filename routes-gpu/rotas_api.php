<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::prefix('api')->group(function () {

//     Route::controller(App\Http\Controllers\Auth\LoginController::class)->group(function () {
//         Route::post('login-front', 'loginFrontApi');
//         Route::post('login', 'session_start_post')->name('api.login.post');
//     });

//     // Rotas protegidas pelo sanctum e acrescentados dados da sessão no sanctum.verify em caso de acesso pelo front
//     Route::middleware(['auth:sanctum', 'api'])->group(function () {

//         Route::group([
//             'prefix' => 'admin',
//             'middleware' => [
//                 'tenant.rota.tipo:1,true,admin',
//                 'usuario.tenant',
//             ],
//         ], function () {

//             Route::prefix('permissoes')->group(function () {

//                 Route::controller(App\Http\Controllers\Auth\PermissionController::class)->group(function () {
//                     Route::get('', 'index')->name('api.admin.permissoes');
//                     Route::get('admin/modulo/{id}', 'getPermissoesPorModuloComAdmin');
//                     Route::get('admin/modulo/{modulo_id}/exceto-permissao/{permissao_id}', 'getPermissoesPorModuloComAdminExetoPermissao');
//                     Route::post('permissoes-com-filtros', 'buscarPermissoesComFiltros');
//                 });

//                 Route::controller(App\Http\Controllers\Auth\PermissionGroupController::class)->group(function () {
//                     Route::prefix('grupos')->group(function () {
//                         Route::get('', 'getGrupos')->name('api.admin.permissoes.grupos');
                        
//                         Route::get('modulo/{id}', 'getGruposPorModulo');
//                         Route::get('modulo/{modulo_id}/exceto-grupo/{grupo_id}', 'getGruposPorModuloExetoGrupo');
//                         Route::post('consulta-filtros', 'postConsultaFiltros');
                        
//                         Route::get('{id}', 'show');
//                         Route::post('', 'store');
//                         Route::put('{id}', 'update');
//                         Route::delete('{id}', 'destroy');
//                     });
//                 });
//             });

//             Route::controller(App\Http\Controllers\Auth\PermissionModuleController::class)->group(function () {

//                 Route::get('modulos', 'getModulos')->name('api.admin.modulos');
//             });
//         });

//         Route::controller(App\Http\Controllers\Auth\LoginController::class)->group(function () {
//             Route::post('/check-token', 'checkTokenApi');
//             Route::post('/logout', 'logoutApi');
//             Route::post('/logout-list', 'logoutListApi');
//         });

//         Route::prefix('pessoa')->group(function () {

//             Route::prefix('gepen')->group(function () {

//                 Route::controller(App\Http\Controllers\GEPEN\PessoaGEPENController::class)->group(function () {

//                     Route::get('{id}', 'show');

//                     Route::prefix('servidor')->group(function () {

//                         Route::get('{id}', 'showServidor');
//                     });
//                 });
//             });

//             Route::prefix('gpu')->group(function () {

//                 Route::controller(App\Http\Controllers\GPU\PessoaGPUController::class)->group(function () {
//                     Route::get('pessoa-por-id/{id}', 'show');
//                     Route::post('buscar-por-documento', 'buscarPorDocumento');
//                 });
//             });

//             Route::controller(App\Http\Controllers\Comum\BuscasDePessoasController::class)->group(function () {

//                 Route::post('pessoas-com-filtros', 'buscarPessoasComFiltros');
//                 Route::post('pessoas-com-criterios', 'buscarPessoasComCriterios');
//             });
//         });


//         Route::prefix('funcionario')->group(function () {

//             Route::prefix('gepen')->group(function () {

//                 Route::controller(App\Http\Controllers\GEPEN\ServidorPessoaGEPENController::class)->group(function () {

//                     Route::get('rh/{id}', 'show');
//                 });
//             });

//             Route::prefix('gpu')->group(function () {

//                 Route::controller(App\Http\Controllers\GPU\FuncionarioGPUController::class)->group(function () {

//                     Route::get('rh/{id}', 'show');
//                 });
//             });
//         });

//         Route::get('foto-preso/{idPreso}', function () {
//             $retorno = app(App\Helpers\FotoHelper::class)->buscarFotoPreso(request('idPreso'));
//             $response = App\Common\RestResponse::createSuccessResponse($retorno, 200);
//             return response()->json($response->toArray(), $response->getStatusCode());
//         });
//     });
// })->middleware(['api']);
