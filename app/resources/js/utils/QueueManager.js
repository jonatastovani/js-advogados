export class QueueManager {
    constructor() {
        this.queue = [];
        this.isReady = false;
        this.isProcessing = false; // Evita múltiplas execuções simultâneas
        this.deduplicationMode = null; // 'first' | 'last' | null (executa todas)
        this.preserveQueueAfterExecution = false; // Define se os itens serão removidos após a execução
    }

    /**
     * Define que está pronto e processa a fila.
     */
    setReady() {
        this.isReady = true;
        this._processQueue();
    }

    /**
     * Coloca a fila em um estado de "não pronto", impedindo execuções imediatas.
     */
    setNoReady() {
        this.isReady = false;
    }

    /**
     * Define o modo de deduplicação de funções idênticas na fila.
     * @param {'first' | 'last' | null} mode - 'first' mantém a primeira ocorrência, 'last' mantém a última, null executa todas.
     */
    set setDeduplicationMode(mode) {
        this.deduplicationMode = mode;
    }

    /**
     * Define se os itens da fila devem ser preservados após a execução.
     * @param {boolean} preserve - `true` para manter os itens na fila após execução.
     */
    set setPreserveQueue(preserve) {
        this.preserveQueueAfterExecution = preserve;
    }

    /**
     * Adiciona uma ação à fila, de acordo com o modo de deduplicação.
     * 
     * @param {Function} action - Função a ser executada quando estiver pronto.
     */
    enqueue(action) {
        if (typeof action !== "function") {
            throw new Error("A ação enfileirada deve ser uma função.");
        }

        const actionString = action.toString();
        const existingIndex = this.queue.findIndex(item => item.toString() === actionString);

        if (this.deduplicationMode === "first" && existingIndex !== -1) {
            // Se já existe e o modo é "first", não adiciona novamente
            return;
        }

        if (this.deduplicationMode === "last" && existingIndex !== -1) {
            // Se já existe e o modo é "last", remove a versão antiga e adiciona a nova no final
            this.queue.splice(existingIndex, 1);
        }

        // Adiciona a nova ação no final da fila
        this.queue.push(action);

        // Se já estiver pronto, inicia o processamento
        if (this.isReady) {
            this._processQueue();
        }
    }

    /**
     * Processa todas as ações na fila de forma assíncrona, garantindo que cada uma
     * seja executada somente após a outra terminar.
     * 
     * @private
     */
    async _processQueue() {
        if (this.isProcessing || !this.isReady) return;

        this.isProcessing = true;

        const queueSnapshot = [...this.queue]; // Mantém uma cópia da fila se for preservada

        while (this.queue.length > 0) {
            const action = this.queue.shift(); // Remove e pega a primeira ação da fila

            if (typeof action === "function") {
                try {
                    // Se for uma Promise, aguarda a conclusão antes de continuar
                    const result = action();
                    if (result instanceof Promise) {
                        await result;
                    }
                } catch (error) {
                    console.error("Erro ao processar ação na fila:", error);
                }
            }
        }

        // Se `preserveQueueAfterExecution` for verdadeiro, restaura os itens para a fila
        if (this.preserveQueueAfterExecution) {
            this.queue = queueSnapshot;
        }

        this.isProcessing = false;
    }
}
