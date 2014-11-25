# message box layout in action result box
#
#   <div class="message {{ result.type }}">
#       <span style="float: left; " class="ui-icon ui-icon-notice"> </span>
#       <div class="text"> {{ result.message }} </div>
#       <div class="desc">
#           {% for k,v in result.validations %}
#               {% if not v.valid %}
#                   <div class="error-message">{{ v.message }}</div>
#
class ActionMsgbox extends ActionPlugin
    init: (action) ->
        super(action)
        return if not @form

        # since we use Phifty::Action::...  ...
        actionId = action.actionName.replace /::/g , '-'

        self = this
        @cls    = 'action-' + actionId + '-result'
        @ccls   = 'action-result'
        if @config.container
            @container = $(@config.container)
        else
            # find result container from current form
            @container = @form.find( '.' + @cls )
            if not @container.get(0)
                @container = $('<div/>').addClass( @cls ).addClass( @ccls )
                @form.prepend @container

        if typeof @config.clear != "undefined"
            @container.empty().hide() if @config.clear
        else
            @container.empty().hide()

        # XXX: use backbone-js template to render action result.
        $(action).bind 'action.on_result', (ev,resp) ->
            # <div class="messgae">
            #    <div class="icon"> </div>
            #    <div class="text"> </div>
            #    <div class="desc"> </div>
            # </div>
            $box  = $('<div/>').addClass 'message'
            $text = $('<div/>').addClass 'text'
            $desc = $('<div/>').addClass 'desc'
            # <span style="float: left; " class="ui-icon ui-icon-notice"> </span>
            $icon = $('<i/>').css( float: 'left' ).addClass('icon')

            # <span onclick="$(this).parent().fadeOut();" style="position: absolute; top: 6px; right: 6px;" class="ui-icon ui-icon-circle-close"> </span>
            $close = $('<span/>').css( position: 'absolute', top: 6, right: 6 )
                        .addClass('icon-remove')
                        .click( -> $box.fadeOut('slow', -> $box.remove() ) )

            $box.append($icon).append($text).append($desc).append($close)

            if resp.success
                $box.addClass 'success'
                $icon.addClass 'icon-ok-sign'
                $text.text(resp.message)
                self.container.html($box).fadeIn('fast')
            else if resp.error
                self.container.empty()
                $box.addClass 'error'
                $icon.addClass 'icon-warning-sign'
                $text.text(resp.message)
                self.container.html($box).fadeIn('fast')

            if resp.validations
                for msg in self.extErrorMsgs(resp)
                    d = $('<div/>').addClass('error-message').html(msg)
                    $desc.append(d)
        $(action).bind 'action.before_submit', () -> self.wait()

    wait: ->
        $box  = $('<div/>').addClass 'message'
        $text = $('<div/>').addClass 'text'
        $desc = $('<div/>').addClass 'desc'
        # <span style="float: left; " class="ui-icon ui-icon-notice"> </span>
        $icon = $('<i/>').css( float: 'left' ).addClass('icon icon-spinner icon-spin')

        # <span onclick="$(this).parent().fadeOut();" style="position: absolute; top: 6px; right: 6px;" class="ui-icon ui-icon-circle-close"> </span>
        $close = $('<span/>').css( position: 'absolute', top: 6, right: 6 )
                    .addClass('ui-icon ui-icon-circle-close')
                    .click( -> $box.fadeOut('slow', -> $box.remove() ) )
        $box.append($icon).append($text).append($desc).append($close)
        $box.addClass 'waiting'
        $text.text("Progressing")
        @container.html($box).fadeIn('fast')

        if not @config.disableScroll and $.scrollTo and window.pageYOffset > 20
          scrollOffset = @config.scrollOffset or -20
          $.scrollTo($box.get(0), 200, { offset: scrollOffset })

        if @config.fadeOut
          setTimeout((=>
              @container.fadeOut('fast', (=> @container.empty()))
          ), 1200)

    extErrorMsgs: (resp) ->
        for field,v of resp.validations
            v.message if not v.valid or v.error
window.ActionMsgbox = ActionMsgbox
