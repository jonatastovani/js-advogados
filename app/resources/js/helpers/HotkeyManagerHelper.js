export class HotkeyManagerHelper {
    constructor() {
        this._registrados = {}; // { scope: Set() }
        this._escoposAtivos = []; // Pilha de escopos
        this.debug = false;

        hotkeys.filter = () => true;

        // Atalho debug: exibe escopos ativos
        hotkeys('ctrl+alt+h', () => {
            if (this.debug) {
                console.warn('🧠 Escopos registrados:', JSON.parse(JSON.stringify(this.getEscoposRegistrados())));
                console.warn('🔙 Pilha escopos ativos:', JSON.parse(JSON.stringify(this._escoposAtivos)));
            }
        });
    }

    /**
     * Registra atalhos dentro de um escopo
     */
    registrar(scope, keyCombos, callback) {
        const keys = Array.isArray(keyCombos) ? keyCombos : [keyCombos];
        if (!this._registrados[scope]) this._registrados[scope] = new Set();

        keys.forEach(key => {
            const combo = key.toLowerCase();
            if (this._registrados[scope].has(combo)) return;

            hotkeys(combo, { scope }, (e) => {
                e.preventDefault();
                callback(e, combo);
            });

            this._registrados[scope].add(combo);
            if (this.debug) console.log(`🎯 Registrado: "${combo}" no escopo "${scope}"`);
        });
    }

    /**
     * Ativa um escopo específico e o adiciona à pilha
     */
    ativarEscopo(scope) {
        const last = this._escoposAtivos[this._escoposAtivos.length - 1];
        if (last !== scope) this._escoposAtivos.push(scope);

        hotkeys.setScope(scope);
        if (this.debug) console.log(`✅ Escopo ativado: "${scope}"`);
    }

    /**
     * Remove um escopo e reativa o anterior
     */
    removerEscopo(scope) {
        if (!this._registrados[scope]) return;

        // Remove atalhos do escopo
        this._registrados[scope].forEach(key => {
            hotkeys.unbind(key, scope);
        });

        delete this._registrados[scope];

        // Remove da pilha
        const index = this._escoposAtivos.lastIndexOf(scope);
        if (index !== -1) this._escoposAtivos.splice(index, 1);

        // Ativa escopo anterior (se houver)
        const escopoAnterior = this._escoposAtivos[this._escoposAtivos.length - 1];
        if (escopoAnterior) {
            hotkeys.setScope(escopoAnterior);
            if (this.debug) console.log(`🔄 Escopo revertido para: "${escopoAnterior}"`);
        } else {
            // Escopo global padrão
            hotkeys.setScope('all');
            if (this.debug) console.log('🔚 Nenhum escopo ativo — setado como "all"');
        }

        if (this.debug) {
            console.warn(`🧼 Escopo removido: "${scope}"`);
            console.warn('📋 Escopos restantes:', JSON.parse(JSON.stringify(this.getEscoposRegistrados())));
            console.warn('🪜 Pilha escopos:', this._escoposAtivos);
        }
    }

    /**
     * Limpa todos os escopos
     */
    limparTudo() {
        Object.keys(this._registrados).forEach(scope => this.removerEscopo(scope));
        this._escoposAtivos = [];
        if (this.debug) console.log('🧹 Todos os escopos foram limpos');
    }

    /**
     * Lista os escopos e atalhos registrados
     */
    getEscoposRegistrados() {
        const result = {};
        for (const scope in this._registrados) {
            result[scope] = Array.from(this._registrados[scope]);
        }
        return result;
    }

    setDebug(valor = true) {
        this.debug = valor;
        if (valor) console.info('🐞 Debug do HotkeyManager ativado');
    }
}
