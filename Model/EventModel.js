class Event {
    constructor(id, name, categories, startDate, endDate, location, description, capacity, status, promotion) {
        this.id = id;
        this.name = name;
        this.categories = categories;
        this.startDate = startDate;
        this.endDate = endDate;
        this.location = location;
        this.description = description;
        this.capacity = capacity;
        this.status = status;
        this.promotion = promotion;
    }
}

module.exports = Event;