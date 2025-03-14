export class enumAction {
    static get GET() {
        return 'GET';
    }

    static get POST() {
        return 'POST';
    }

    static get PUT() {
        return 'PUT';
    }

    static get PATCH() {
        return 'PATCH';
    }

    static get DELETE() {
        return 'DELETE';
    }

    static isValid(value) {
        return value === enumAction.GET ||
            value === enumAction.POST ||
            value === enumAction.PUT ||
            value === enumAction.PATCH ||
            value === enumAction.DELETE;
    }

}
