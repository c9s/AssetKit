jQuery ->
  jQuery('form.ajax-action').each (i,f) ->
    $form = $(f)
    a = Action.form $form,
      clear: $form.data('clear')
      fadeOut: $form.data('fadeOut')
      onSuccess: (resp) ->
        cb = eval($form.data('onSuccess'))
        if cb
          cb.call(resp)

    resultContainerSel = $form.data('resultContainer')
    $result = undefined
    if resultContainerSel
      $result = $(resultContainerSel)
    else
      $result = $form.find(".action-result-container")

    # find global action result container
    unless $result.get(0)
      $result = $form.parent().find(".action-result-container")

    # create a custon action result container by default.
    unless $result.get(0)
      $result = $("<div/>").addClass("action-result-container")
      $form.before $result

    # setup the result container
    # dynamically create a result container before the form
    a.plug ActionMsgbox,
      container: $result
      scrollOffset: $form.data('scrollOffset')
      fadeOut: false
