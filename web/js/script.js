$( document ).ready(function() {
    $.ajaxSetup({ cache: false });

    $("#flashMessageClose").click(function(){
        $("#flashMessage").toggle("slow");
    });


    $("body").on("click", "#effectiveConfigurationManage", function() {
        if($("#effectiveConfiguration").is(":visible")) {
            $("#effectiveConfiguration").toggle("slow");
            $("#effectiveConfigurationManage").html("Show effective configuration");
        } else {
            $("#effectiveConfiguration").show("slow");
            $("#effectiveConfigurationManage").html("Hide effective configuration");
        }

    });

    $("body").on("click", "#flashMessagePopinClose", function() {
        $("#flashMessagePopin").toggle("slow");
    });


    $(".confirm").confirm();
    $.confirm.options = {
        text: "Are you sure you want to delete it ?",
        title: "Danger zone !!!",
        confirmButton: "Yes",
        cancelButton: "Cancel"
    }
});



var oSettingsBPopupDefault = {
    opacity: 0.3
};
/**
 * ajaxBPopup()
 * popin par default avec load ajax
 * @param srt string
 * ========================================================================== */

function ajaxBPopup(url, param){
    var oParam = param || {};
    var displayMessage = function(data){
        //console.log("~~~~~~~~ success ~~~~~~~~");
        $('#popup').bPopup($.extend({},oSettingsBPopupDefault,{
            closeClass: 'btnClosePopup',
            onOpen: function() {
                $(this).html('<i title="Fermer" class="btnClose btnClosePopup"></i>'+ (data || ''));
            },
            onClose: function() {
                $(this).empty();
            }
        })
        ).addClass('popDefault');
    };
    loadData(url,"html",displayMessage,oParam);
} // ajaxBPopup

/**
 * AJAX request
 * generic function to load data.
 *
 * @method loadData
 * @param {String} url (default: The current page): A string containing the URL to which the request is sent (path relative to the 'ajax/' directory ).
 * @param {String} returned (default: Intelligent Guess (xml, json, script, or html)): The type of data that you're expecting back from the server.
 * @param {Function} success: A function to be called if the request succeeds.
 * @param {Object or String} param: Data to be sent to the server. It is converted to a query string, if not already a string.
 * @param {String} request (default: 'GET'): The type of request to make ("POST" or "GET").
 * @param {Boolean} [loader=false]: Active or not the pre-request callback function beforeSend.
 * @param {Boolean} [fail=false]: Active or not the callback function error (if the request fails).
 */
function loadData(url,returned,callback,param,request,loader,fail){
    loader = loader || false;
    fail = fail || false;
    request = request || 'GET';
    var loading,
        failed;


    if ( $.isFunction(loader) ) {
        loading = loader;
    }
    else {
        loading = function(jqXHR,settings){
            //console.log("~~~~~~~~ beforeSend ~~~~~~~~");
        }
    }

    if ( $.isFunction(fail) ) {
        failed = fail;
    }
    else {
        failed = function(jqXHR,textStatus,errorThrown){
            //console.log("~~~~~~~~ error ~~~~~~~~");
        }
    }


    $.ajax({
        type:       request ,
        url:        url,
        data:       param,
        dataType:   returned,
        error:      failed,
        beforeSend: loading,
        cache:      false,
        async:      false,
        success:    callback,
        xhrFields: {
            withCredentials: true
        }
    });
}

function checkJobLog(url,callback,request,loader,fail){
    loader = loader || false;
    fail = fail || false;
    request = request || 'GET';
    var loading,
        param,
        failed;


    if ( $.isFunction(loader) ) {
        loading = loader;
    }
    else {
        loading = function(jqXHR,settings){
            //console.log("~~~~~~~~ beforeSend ~~~~~~~~");
        }
    }

    if ( $.isFunction(fail) ) {
        failed = fail;
    }
    else {
        failed = function(jqXHR,textStatus,errorThrown){
            //console.log("~~~~~~~~ error ~~~~~~~~");
        }
    }


    $.ajax({
        type:       request ,
        url:        url,
        data:       param,
        dataType:   "json",
        error:      failed,
        beforeSend: loading,
        cache:      false,
        async:      false,
        success:    callback,
        xhrFields: {
            withCredentials: true
        }
    });
}

