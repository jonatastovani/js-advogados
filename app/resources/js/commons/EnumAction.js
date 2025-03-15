export class EnumAction {
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
        return value === EnumAction.GET ||
            value === EnumAction.POST ||
            value === EnumAction.PUT ||
            value === EnumAction.PATCH ||
            value === EnumAction.DELETE;
    }

}
