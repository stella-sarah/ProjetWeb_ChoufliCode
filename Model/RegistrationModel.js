class Registration {
    constructor(id, participantName, eventId, spots, status) {
        this.id = id;
        this.participantName = participantName;
        this.eventId = eventId;
        this.spots = spots;
        this.status = status;
    }
}

module.exports = Registration;