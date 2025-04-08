/**
 * Gerenciador de instâncias nomeadas para reaproveitamento global.
 */
export default {

    // Define se os logs de debug devem ser exibidos
    verbose: false,

    // Ativa logs apenas uma vez (reset automático)
    verboseTrueAutoFalse: false,

    // Armazena todas as instâncias registradas
    instances: {},

    /**
     * Setter para ativar/desativar logs persistentes
     * @param {boolean} value
     */
    set setVerbose(value) {
        this.verbose = value;
    },

    /**
     * Setter para ativar logs apenas uma vez
     * @param {boolean} value
     */
    set setVerboseTrueAutoFalse(value) {
        this.verboseTrueAutoFalse = value;
    },

    /**
     * Verifica se uma instância já foi registrada
     * @param {string} name
     * @returns {Object|false}
     */
    instanceVerification(name) {
        this.verboseExecute('🔍 Verificando instância:', name);
        if (this.instances[name]) {
            this.verboseExecute('✅ Instância encontrada:', this.instances[name], '__END__');
            return this.instances[name];
        } else {
            this.verboseExecute('❌ Instância não encontrada', '__END__');
            return false;
        }
    },

    /**
     * Exibe logs conforme modo verbose
     * @param  {...any} args - Argumentos para o console.log
     */
    verboseExecute(...args) {
        const isEnd = args.includes('__END__');

        if (this.verbose || this.verboseTrueAutoFalse) {
            const filteredArgs = args.filter(arg => arg !== '__END__');
            console.warn(...filteredArgs);

            // Desliga o modo verbose automático se for marcado como fim
            if (this.verboseTrueAutoFalse && isEnd) {
                this.verboseTrueAutoFalse = false;
            }
        }
    },

    /**
     * Retorna ou cria e registra uma instância
     * @param {string} name
     * @param {Function} createFn
     * @returns {Object}
     */
    getOrCreateInstance(name, createFn) {
        const blockedValueVerboseAutoFalse = this.verboseTrueAutoFalse;
        const existing = this.instanceVerification(name);
        if (existing !== false) {
            return existing;
        }
        this.verboseTrueAutoFalse = blockedValueVerboseAutoFalse;

        const newInstance = createFn();
        this.instances[name] = newInstance;

        this.verboseExecute(`✨ Nova instância registrada: ${name}`, newInstance, '__END__');

        return newInstance;
    },

    /**
     * Remove uma instância específica
     * @param {string} name
     */
    resetInstance(name) {
        if (this.instances[name]) {
            delete this.instances[name];
            this.verboseExecute(`🗑️ Instância "${name}" foi resetada.`, '__END__');
        }
    },

    /**
     * Limpa todas as instâncias registradas
     */
    clearAllInstances() {
        this.instances = {};
        this.verboseExecute('🧹 Todas as instâncias foram limpas.', '__END__');
    }
};