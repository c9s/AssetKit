


/*
 *
 *
 * Usage:
 *
 *      <form id="searchForm" method="post" data-region-id="eventListRegion" data-region-path="/event/_list">
 *        {{ search.renderSignatureWidget|raw }}
 *      </form>
 *
 *      var $form = $('#searchForm');
 *      var search = new SearchAction($form, {}, function(args) { 
 *          setupWaypoint(this);
 *      });
 *      search.run({ page: 1 });
*/

var SearchAction = function($form, options, cb) {
    this.$form = $form;
    this.cb = cb;
    this.options = options;

    var regionEl = document.getElementById($form.data('region-id'));
    var regionPath = $form.data('region-path');
    this.region = $(regionEl).asRegion();
    this.region.path = regionPath;
    this.region.args = {};
    this.region.save();
    this.page = 1;
    this.bind();
};

SearchAction.prototype.run = function(args) {
    var self = this;
    this.region.refreshWith(args, function(html) {
        if ( self.cb ) {
            self.cb.apply(self,[args]);
        }
    });
};

SearchAction.prototype.nextPage = function() {
    this.page++;
};

SearchAction.prototype.getPage = function() {
    return this.page;
};

SearchAction.prototype.bind = function() {
    var self = this;
    this.$form.find('input[type=text], select, input[type=radio], input[type=checkbox]').change(function() {
        var name = $(this).attr('name');
        var args = { };
        args[name] = $(this).val();
        self.page = args["page"] = 1; // force reset page
        self.run(args);
    });
};
