// Generated by CoffeeScript 1.7.1
(function() {
  var ActionHighlight,
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  ActionHighlight = (function(_super) {
    __extends(ActionHighlight, _super);

    function ActionHighlight() {
      return ActionHighlight.__super__.constructor.apply(this, arguments);
    }

    ActionHighlight.prototype.init = function(action) {
      var that;
      this.action = action;
      this.form = action.form();
      FormUtils.findVisibleFields(this.form).each(function() {
        var $field, m, name, w;
        $field = $(this);
        name = $(this).attr("name");
        if (name === "action") {
          return;
        }
        if (!name) {
          return;
        }
        name = name.replace(/\[\]/, '_');
        m = $(".field-" + name + "-message").hide();
        w = $(".field-" + name);
        if (!w.length) {
          $field.wrap("<div class=\"action-field field-" + name + "\"/>");
        }
        if (!m.length) {
          $field.after("<div class=\"action-field-message field-" + name + "-message\"/>");
        }
        return $(".action-field-message").hide();
      });
      that = this;
      return $(action).bind('action.on_result', function(ev, resp) {
        var msg, n, v, w, _results;
        that.clear();
        _results = [];
        for (n in resp.validations) {
          v = resp.validations[n];
          w = that.form.find(".field-" + n);
          msg = that.form.find(".field-" + n + "-message");
          if (!v.valid) {
            w.addClass("invalid");
            _results.push(msg.addClass("invalid").html(v.message).fadeIn("slow"));
          } else {
            w.addClass("valid");
            _results.push(msg.addClass("valid").html(v.message).fadeIn("slow"));
          }
        }
        return _results;
      });
    };

    ActionHighlight.prototype.clear = function() {
      var that;
      that = this;
      return FormUtils.findVisibleFields(this.form).each(function() {
        var el, n;
        el = $(this);
        n = el.attr("name");
        that.form.find(".field-" + n).removeClass("invalid valid");
        return that.form.find(".field-" + n + "-message").removeClass("invalid valid").html("").hide();
      });
    };

    return ActionHighlight;

  })(ActionPlugin);

  window.ActionHighlight = ActionHighlight;

}).call(this);
