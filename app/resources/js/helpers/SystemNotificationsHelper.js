import { BootstrapFunctionsHelper } from "./BootstrapFunctionsHelper";

export class SystemNotificationsHelper {

    #message;
    #title;
    #type;
    #traceId;

    constructor(message = undefined, type = 'info', render = false) {
        this.#message = message;
        this.#title = null;
        this.#type = type;
        this.#traceId = undefined;

        if (render) {
            return this.render();
        }
    }

    /**
     * @param {string} message
     */
    set setMessage(message) {
        this.#message = message;
    }

    /**
     * @param {string} title
     */
    set setTitle(title) {
        this.#title = title
    }

    /**
     * @param {string} type
     */
    set typeNotification(type) {
        this.#type = type;
    }

    set setTraceId(traceId) {
        this.#traceId = traceId
    }

    render() {
        const self = this;

        return new Promise(async function (resolve) {
            resolve(await BootstrapFunctionsHelper.createNotification(self.#message, { type: self.#type, title: self.#title, traceId: self.#traceId }));
        })
    }
}