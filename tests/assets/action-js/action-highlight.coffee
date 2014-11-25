

class ActionHighlight extends ActionPlugin
  init: (action) ->
    @action = action
    @form = action.form()
    # create highlight div wrappers 
    # and message divs for fields
    FormUtils.findVisibleFields(@form).each ->
      $field = $(this)
      name = $(this).attr("name")
      return if name is "action"
      return unless name
      name = name.replace(/\[\]/,'_')

      # append message div
      m = $(".field-#{name}-message").hide()

      # wrap with highlight div
      w = $(".field-#{name}")
      $field.wrap "<div class=\"action-field field-#{name}\"/>" unless w.length
      $field.after "<div class=\"action-field-message field-#{name}-message\"/>"  unless m.length
      $(".action-field-message").hide()

    that = this
    $(action).bind 'action.on_result', (ev,resp) ->
      # clear previous messages first
      that.clear()

      # valid || invalid
      for n of resp.validations
        v = resp.validations[n]
        w = that.form.find(".field-" + n)
        msg = that.form.find(".field-" + n + "-message")
        if not v.valid
          w.addClass "invalid"
          msg.addClass("invalid").html(v.message).fadeIn "slow"
        else
          w.addClass "valid"
          msg.addClass("valid").html(v.message).fadeIn "slow"
  clear: ->
    that = this
    FormUtils.findVisibleFields(@form).each ->
      el = $(this)
      n = el.attr("name")
      that.form.find(".field-" + n).removeClass "invalid valid"
      that.form.find(".field-" + n + "-message").removeClass("invalid valid").html("").hide()

window.ActionHighlight = ActionHighlight
