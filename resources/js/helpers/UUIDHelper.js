export class UUIDHelper {
    
    static generateUUID() {
        return uuidv4();
    }

    static isValidUUID(uuid) {
        return uuidValidate(uuid);
    }

    static uuidVersion(uuid) {
        return uuidVersion(uuid);
    }
}