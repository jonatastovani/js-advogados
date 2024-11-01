<?php

namespace App\Http\Controllers\View\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('modulos.admin.index');
    }
   
    public function permissoes()
    {
        return view('modulos.admin.permissoes');
    }
   
    public function usuariosPermissoes()
    {
        return view('modulos.admin.usuarios.permissoes');
    }
   
    public function permissoesPermissoes()
    {
        return view('modulos.admin.permissoes.permissoes');
    }
   
    public function permissoesGrupos()
    {
        return view('modulos.admin.permissoes.grupos');
    }

}
