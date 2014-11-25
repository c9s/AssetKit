###
vim:fdm=marker:sw=4:et:

Action.js: Javascript for submitting forms and validations.
Depends on: region.js, jQuery.scrollTo.js
Author: Yo-An Lin <cornelius.howl@gmail.com>
Date: 2/16 17:04:44 2011 

USAGE
-----

    Action.form('#action-form').setup({ 
        clear: true,
        onSuccess:
        onError: 
        onResult:
    })

###


window.FormUtils =
    findFields: (form) ->
        return $(form).find 'select, textarea,
             input[type=text],
             input[type=checkbox],
             input[type=radio],
             input[type=password],
             input[type=date],
             input[type=datetime],
             input[type=time],
             input[type=email],
             input[type=hidden]'

    findVisibleFields: (form) ->
        return $(form).find 'select, textarea,
                    input[type=text],
                    input[type=date],
                    input[type=datetime],
                    input[type=time],
                    input[type=checkbox],
                    input[type=radio],
                    input[type=email],
                    input[type=password]'

    findTextFields: (form) ->
        return $(form).find 'input[type="text"],
                      input[type="file"],
                      input[type="time"],
                      input[type="datetime"],
                      input[type="date"],
                      input[type="password"],
                      input[type="email"],
                      textarea'

    enableInputs: (form) -> @findVisibleFields(form).removeAttr('disabled')

    disableInputs: (form) -> @findVisibleFields(form).attr('disabled','disabled')

class Action
    ajaxOptions:
        dataType: 'json'
        type: 'post'
        timeout: 8000
    plugins: []
    actionPath: null
    options:
        disableInput: true
    constructor: (arg1,arg2) ->
        formsel = null
        opts = {}
        if arg1 and ( arg1 instanceof jQuery or arg1.nodeType == 1 or typeof arg1 is 'string')
            formsel = arg1
            opts = arg2 || {}
        else if typeof arg1 == "object"
            opts = arg1 || {}

        @form(formsel) if formsel
        @options = $.extend { }, opts

        if @options.plugins
            @plug plugin for plugin in @options.plugins

        # init plugins
        $(Action._globalPlugins).each (i,e) => @plug e.plugin, e.options

    form: (f) ->
        if f
            @formEl = $(f)
            @formEl.attr('method','post')
            # auto setup enctype for uploading file.
            @formEl.attr("enctype", "multipart/form-data")
            @formEl.data("actionObject", this)
            @actionName = @formEl.find('input[name=action]').val()

            alert "Action form element not found" if not @formEl.get(0)
            alert "Action name is undefined." if not @actionName

            # pass __ajax_request param for ajax action request.
            if not @formEl.find('input[name="__ajax_request"]').get(0)
                @formEl.append $('<input>').attr
                    type:"hidden"
                    name:"__ajax_request"
                    value: 1
            @formEl.submit =>
                # run Action.submit method()
                try
                    # dispatch toAction.submit method
                    ret = @submit()
                    return ret if ret
                catch e
                    console.error e.message,e if window.console
                return false
        return @formEl

    log: -> console.log.apply(console, arguments) if window.console and window.console.log and console.log.apply

    plug: (plugin,options) ->
        if typeof plugin is 'function'
            p = new plugin(this, options )
            @plugins.push(p)
            return p
        else if plugin instanceof ActionPlugin
            plugin.init(this)
            @plugins.push(plugin)
            return plugin

    setPath: (path) -> @actionPath = path

    # get data from form input elements
    getData: (f) ->
        that = this
        data = { }
        
        isIndexed = (n) -> n.indexOf('[]') > 0
        # get data from text fields

        # If tinyMCE is enabled, we should update contents from hidden values
        if typeof tinyMCE isnt 'undefined'
            tinyMCE.EditorManager.triggerSave()

        FormUtils.findFields(f).each (i,n) ->
            el = $(n)
            val = $(n).val()
            name = el.attr('name')
            return if not name

            if val and ( typeof val == "object" or typeof val == "array" )
                data[name] = val
                return

            if isIndexed( name )
                data[name] ||= []

                # for checkbox(s), get their values.
            if el.attr('type') == "checkbox"
                if el.is(':checked')
                    if isIndexed( name )
                        data[name].push( val )
                    else
                        data[name] = val
            else if el.attr('type') == "radio"
                if el.is(':checked')
                    if isIndexed( name )
                        data[name].push( val )
                    else
                        data[name] = val
                else if not data[name]
                    data[ name ] = null
            else
                # if it's name is an array
                if isIndexed( name )
                    data[name].push( val )
                else
                    data[name] = val
        return data

    # hook handler on form element.
    setup: (options) ->
        @options = $.extend @options, options
        return this

    _processElementOptions: (options) ->
        # remove table tr element of the event source
        if options.removeTr
            el = $( $(options.removeTr).parents('tr').get(0) )
            el.fadeOut 'fast', -> $(@).remove()

        # remove elements of the event source
        if options.remove
            $(options.remove).remove()

    _processFormOptions: (options,resp) ->
        if options.clear
            # it's not action name field, clear it.
            FormUtils.findTextFields(@form()).each (i,e) ->
                $(this).val("") if $(this).attr('name') != "action"
        if resp.success and options.fadeOut
            @form().fadeOut('slow')

    _processLocationOptions: (options,resp) ->
        # reload page
        if options.reload
            setTimeout (-> window.location.reload()) , options.delay || 0
        else if options.redirect
            setTimeout (-> window.location = options.redirect), options.delay || 0
        else if resp.redirect
            setTimeout (-> window.location = resp.redirect), resp.delay * 1000 || options.delay || 0

    _processRegionOptions: (options,resp) ->
        throw "Region is undefined." unless Region

        # if form exists, region options should based on the region of form.
        form = @form()
        if form
            # pre-process region of form element
            reg = Region.of form
            regionKeys = [
                'refreshSelf'
                'refresh'
                'refreshParent'
                'refreshWithId'
                'removeRegion'
                'emptyRegion'
            ]
            $(regionKeys).each (i,e) -> options[e] = reg if options[e] is true

        Region.of(options.refreshSelf).refresh()            if options.refreshSelf
        Region.of(options.refresh).refresh()                if options.refresh
        Region.of(options.refreshParent).parent().refresh() if options.refreshParent
        Region.of(options.refreshWithId).refreshWith(id: resp.data.id) if options.refreshWithId
        Region.of(options.removeRegion).remove()            if options.removeRegion
        Region.of(options.emptyRegion).fadeEmpty()          if options.emptyRegion


    # return a success result handler:
    #     (resp) -> code
    _createSuccessHandler: (formEl,options,cb ) ->
        # which is an Action object.
        self = this
        $self = $(self)

        return (resp) ->
            # trigger event for plugins
            # self.log 'action.on_result',[resp]
            $self.trigger 'action.on_result',[resp]

            FormUtils.enableInputs(formEl) if formEl and options.disableInput

            if cb
                ret = cb.call(self, resp )
                return ret if ret

            if resp.success
                options.onSuccess.apply(self,[resp]) if options.onSuccess

                self._processFormOptions options , resp
                self._processRegionOptions options, resp
                self._processElementOptions options,resp
                self._processLocationOptions options, resp
            else if resp.error
                options.onError.apply(self,[resp]) if options.onError

                if window.console then console.error( resp.message )
                else alert resp.message
            else
                throw "Unknown error:" + resp
            return true

    _createErrorHandler: (formEl, options) ->
      return (error, t, m) =>
        if error.responseText
          if window.console then console.error error.responseText
          else alert error.responseText
        else
          console.error error
        FormUtils.enableInputs(formEl) if formEl and options.disableInput

    ### 

    run method

    .run() or runAction()
        run specific action

    .run( 'Delete' , { table: 'products' , id: id } , function() { ... });

    
    .run( [action name] , [arguments] , [options] or [callback] );
    .run( [action name] , [arguments] , [options] , [callback] );


    Event callbacks:

            * onSubmit:    [callback]
                            callback before sending request

            * onSuccess:   [callback]
                            success callback.

    options:
            * confirm:      [text]    
                            should confirm 

            * removeRegion: [element] 
                            the element in the region. to remove region.

            * emptyRegion:  [element] 
                            the element in the region. to empty region.


            * removeTr:     [element] 
                            the element in the tr.

            * remove:       [element] 
                            the element to be removed.

            * clear:        [bool]
                            clear text fields

            * fadeOut:      [hide]
                            hide the form if success
    ###
    run: (actionName,args,arg1,arg2) ->
        try
            if typeof arg1 == "function"
                cb = arg1
            else if typeof arg1 == "object"
                @options = $.extend @options,arg1
                cb = arg2 if typeof arg2 == "function"

            if @options.confirm
                return false if not confirm @options.confirm

            # inject __ajax_request: 1 if there is no form element.
            data = $.extend({ action: actionName, __ajax_request: 1 }, args )
            # @log( "Running action: " , actionName , 'Args' , args , 'Options' , @options )

            @options.onSubmit() if @options.onSubmit


            formEl = @form()

            if formEl
                # if we have form, disable these inputs
                FormUtils.disableInputs(formEl) if @options.disableInput

            postUrl = window.location.pathname

            if formEl and formEl.attr('action')
                postUrl = formEl.attr('action')
            else if @actionPath
                postUrl = @actionPath

            errorHandler = @_createErrorHandler( formEl, @options )
            successHandler = @_createSuccessHandler( formEl, @options, cb )

            # @log( 'Sending Ajax Request: ', postUrl , data )

            jQuery.ajax $.extend @ajaxOptions,
                url: postUrl
                data: data
                error: errorHandler
                success: successHandler


            return false
        catch e
            console.error(e.message,e) if window.console
            alert(e.message)

    ###
     * submit:
     * submit( option , callback )
     * submit( callback )
    ###
    submit: (arg1,arg2) ->
        that = this

        # detect arguments
        if typeof arg1 == "object"
            @options = $.extend @options,arg1
            if arg2 and typeof arg2 == "function"
                cb = arg2
        else if typeof arg1 == "function"
            cb = arg1

        # get form element
        $form = @form()
        data = @getData( $form )

        if @options.beforeSubmit
            ret = @options.beforeSubmit.call($form, data)
            return false if ret is false

        $(this).trigger('action.before_submit',[data])

        # If any file field is found in current form.
        # We should use AIM instead of normal ajax request.
        if $form.find("input[type=file]").get(0) and $form.find('input[type=file]').parents('form').get(0) == $form.get(0)
            return @submitWithAIM(data,cb)
        else
            # call run method, and pass our submit handler
            return @run(data.action, data)
        return true

    # Submit form with AIM (ajax iframe method)
    #
    # @param array data Form data.
    # @param closure callback
    submitWithAIM: (data,cb) ->
        $form = @form()
        # use $form, data, options, cb
        successHandler = @_createSuccessHandler( $form, @options, cb)
        errorHandler = @_createErrorHandler( $form, @options )
        @options.beforeUpload.call( this, $form, data ) if @options.beforeUpload

        throw "form element not found." if not $form or not $form.get(0)
        if typeof AIM is "undefined"
            alert "AIM is required for uploading file in ajax mode."

        actionName = $form.find('input[name="action"]').val()
        throw "action name field is required" unless actionName

        # @log("submitting action #{ actionName } with AIM")
        that = this

        # AIM bridge
        return AIM.submit $form.get(0),
            onStart: ->
                that.options.beforeUpload.call that, $form, json if that.options.beforeUpload
                return true
            onComplete: (responseText) ->
                try
                    # console.log "AIM ResponseText:", responseText if window.console
                    json = JSON.parse responseText

                    # callback is optional, the successHandler is a callback wrapper
                    successHandler(json, that.options.onUpload)
                    that.options.afterUpload.call that, $form, json if that.options.afterUpload
                catch e
                    errorHandler(e)
                return true

    ###
    (Action object).submitWith( args, ... )
    ###
    submitWith: (extendData,arg1,arg2) ->
        options = { }

        # arg2 is option
        if typeof arg1 == "object"
            options = arg1
            cb = arg2 if typeof arg2 == "function"
        else if typeof arg1 == "function"
            cb = arg1

        data = $.extend @getData( @form() ), extendData
        @run(data.action , data , options , cb)


Action._globalPlugins = [ ]

Action.form = (formsel,opts) -> new Action(formsel,opts || {})

Action.plug = (plugin , opts) ->
    Action._globalPlugins.push plugin: plugin, options: opts

Action.reset = -> Action._globalPlugins = []

# action helper functions
window.submitActionWith = (f , extendData , arg1 , arg2 ) ->
    Action.form(f).submitWith extendData, arg1, arg2

window.submitAction = (f,arg1,arg2) ->
    Action.form(f).submit arg1,arg2

window.runAction = (actionName,args,arg1,arg2) ->
    a = new Action
    a.run actionName,args,arg1,arg2

# Export Action to jQuery.
window.Action = $.Action = Action


###

    a = new ActionPlugin(action,{ ...options...  })
    a = new ActionPlugin(action)
    a = new ActionPlugin({ ... })

###
class ActionPlugin
    formEl: null
    action: null
    config: {}
    constructor: (a1,a2) ->
        if(a1 and a2)
            @config = a2 || {}
            @init(a1)
        else if a1 instanceof Action
            @init(a1)
        else if typeof a1 is 'object'
            @config = a1
    init: (action) ->
        @action = action
        @form = @action.form()
window.ActionPlugin = ActionPlugin
