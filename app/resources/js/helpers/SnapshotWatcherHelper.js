export class SnapshotWatcherHelper {
    #elements = [];
    #snapshot = new Map();

    constructor(selectors = []) {
        this.#setElements(selectors);
        this.#takeSnapshot();
    }

    /**
     * Atualiza os elementos a serem observados
     */
    #setElements(selectors) {
        this.#elements = [];

        selectors.forEach(selector => {
            const found = $(selector);
            if (found.length) {
                this.#elements.push(...found.toArray());
            }
        });
    }

    /**
     * Cria um novo snapshot baseado no valor atual dos inputs
     */
    #takeSnapshot() {
        this.#snapshot.clear();

        this.#elements.forEach(el => {
            const key = this.#getElementIdentifier(el);
            this.#snapshot.set(key, this.#getElementValue(el));
        });
    }

    /**
     * Verifica se houve alguma alteração desde o último snapshot
     */
    hasChanged() {
        for (const el of this.#elements) {
            const key = this.#getElementIdentifier(el);
            const oldValue = this.#snapshot.get(key);
            const currentValue = this.#getElementValue(el);
            if (oldValue !== currentValue) {
                return true;
            }
        }
        return false;
    }

    /**
     * Recria o snapshot com os valores atuais
     */
    updateSnapshot() {
        this.#takeSnapshot();
    }

    /**
     * Gera um identificador único baseado no name + id (fallback para tag+index)
     */
    #getElementIdentifier(el) {
        return el.name || el.id || `tag:${el.tagName.toLowerCase()}-idx:${this.#elements.indexOf(el)}`;
    }

    /**
     * Obtém o valor real do elemento (suporta input, select, textarea, checkbox)
     */
    #getElementValue(el) {
        const $el = $(el);

        if (el.type === 'checkbox') {
            return el.checked;
        }

        if (el.type === 'radio') {
            return $(`input[name="${el.name}"]:checked`).val() || '';
        }

        return $el.val();
    }
}
