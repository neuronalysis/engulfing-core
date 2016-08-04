var UriCell = Backgrid.UriCell.extend({
	render: function () {
        this.$el.empty();
        var rawValue = this.model.get(this.column.get("name"));
        var formattedValue = this.formatter.fromRaw(rawValue, this.model);
        var href = _.isFunction(this.column.get("href")) ? this.column.get('href')(rawValue, formattedValue, this.model) : this.column.get('href');
        this.$el.append($("<a>", {
          tabIndex: -1,
          href: href || rawValue,
          title: this.title || formattedValue,
          target: this.target
        }).text(formattedValue));
        this.delegateEvents();
        return this;
    }
});