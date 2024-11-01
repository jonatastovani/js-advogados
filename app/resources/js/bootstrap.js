// Importa jQuery e atribui ao window.$ e window.jQuery
import jQuery from 'jquery';
window.$ = jQuery;
window.jQuery = jQuery; // Garantir que o jQuery esteja disponível globalmente

// Importa Bootstrap e outros recursos
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Importa ícones do Bootstrap
import 'bootstrap-icons/font/bootstrap-icons.css';

// Importa Axios
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Importa o CSS e JavaScript do SimpleBar
import SimpleBar from 'simplebar';
import 'simplebar/dist/simplebar.css';
window.SimpleBar = SimpleBar;

import { v4 as uuidv4, validate as uuidValidate, version as uuidVersion } from 'uuid';

window.uuidv4 = uuidv4;
window.uuidValidate = uuidValidate;
window.uuidVersion = uuidVersion;

import moment from 'moment';
window.moment = moment;

import 'select2';

import 'jquery-mask-plugin';
