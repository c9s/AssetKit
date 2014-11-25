class ActionGrowler extends ActionPlugin
    init: (action) ->
        super(action)
        $(action).bind 'action.on_result', (ev,resp) =>
            if resp.success
                @growl resp.message , @config.success
            else
                @growl resp.message, $.extend( @config.error , theme: 'error' )
            return true
    growl: (text,opts) ->
        $.jGrowl(text,opts)
window.ActionGrowler = ActionGrowler
