export class QueueManager {
    constructor() {
        this.queue = [];
        this.isReady = false;
    }

    /**
     * Define que está pronto e processa a fila.
     */
    setReady() {
        this.isReady = true;
        this._processQueue();
    }

    /**
     * Adiciona uma ação à fila.
     * 
     * @param {Function} action - Função a ser executada quando estiver pronto.
     */
    enqueue(action) {
        if (this.isReady) {
            action();  // Se já estiver pronto, executa imediatamente
        } else {
            this.queue.push(action);  // Adiciona à fila
        }
    }

    /**
     * Processa todas as ações na fila.
     * 
     * @private
     */
    _processQueue() {
        while (this.queue.length > 0) {
            const action = this.queue.shift();  // Remove e executa a ação
            action();
        }
    }
}
