const Event = require('../Models/EventModel');

class EventController {
    constructor() {
        this.events = [];
    }

    createEvent(data) {
        const newEvent = new Event(this.events.length + 1, ...data);
        this.events.push(newEvent);
        return newEvent;
    }

    getEvents() {
        return this.events;
    }
}

module.exports = EventController;