/**
 * Module for managing instances.
 */
export default {

    instances: {},

    /**
     * Verifies the existence of an instance with the given name.
     *
     * @param {string} name - The name of the instance to verify.
     * @returns {Object|boolean} - The instance if it exists, or `false` if it doesn't.
     */
    instanceVerification(name) {
        if (this.instances[name]) {
            return this.instances[name];
        } else {
            return false;
        }
    },

    setInstance(name, instance) {
        let obj = this.instanceVerification(name);
        if (obj === false) {
            obj = instance;
            this.instances[name] = obj;
        }
        return obj;
    }

};
