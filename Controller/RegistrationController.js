const Registration = require('../Models/RegistrationModel');

class RegistrationController {
    constructor() {
        this.registrations = [];
    }

    createRegistration(data) {
        const newRegistration = new Registration(this.registrations.length + 1, ...data);
        this.registrations.push(newRegistration);
        return newRegistration;
    }

    getRegistrations() {
        return this.registrations;
    }
}

module.exports = RegistrationController;