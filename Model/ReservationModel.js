class Reservation {
    constructor(id, participantName, eventId, spots, comments) {
        this.id = id;
        this.participantName = participantName;
        this.eventId = eventId;
        this.spots = spots;
        this.comments = comments;
    }
}

module.exports = Reservation;