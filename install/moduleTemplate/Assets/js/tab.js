$(document).ready(function(){
    var $body = $('body');
    var selectedId = null;

    /**
     * Open modal to update record
     */
    $body.on("click", ".symfonytpl-btn-update", function(){
        // Opening tab form for add
        selectedId = $(this).parents("tr").attr("id");
        melisHelper.tabOpen('tool_symfonytpl_title / ' + selectedId, 'fa fa-puzzle-piece', selectedId+'_id_symfonytpl_tool_form', 'symfonytpl_tool_form', {id: selectedId}, 'id_symfonytpl_tool');
    });

    /**
     * Open modal to add new record
     */
    $body.on("click", "#symfonytpl_btn_new", function(){
        selectedId = null;
        melisHelper.tabOpen('tool_symfonytpl_title', 'fa fa-puzzle-piece', '0_id_symfonytpl_tool_form', 'symfonytpl_tool_form', {id:0}, 'id_symfonytpl_tool');
    });

    /**
     * Save
     */
    $body.on("click", "#btn-save-symfonytpl", function(){
        if(selectedId == null)
            save("/melis/symfonytpl/save");
        else
            save("/melis/symfonytpl/save/"+selectedId);
    });

    /**
     * Delete
     */
    $body.on("click", ".symfonytpl-btn-delete", function(){
        var _this = $(this);
        melisCoreTool.confirm(
            translations.tool_symfony_tpl_confirm_modal_yes,
            translations.tool_symfony_tpl_confirm_modal_no,
            translations.tool_symfony_tpl_confirm_modal_title,
            translations.tool_symfony_tpl_confirm_modal_message,
            function() {
                $.ajax({
                    url: "/melis/symfonytpl/delete",
                    data: {"id" : _this.parents("tr").attr("id")},
                    method: "POST",
                    beforeSend: function(){

                    },
                    success: function(data){
                        // update flash messenger values
                        melisCore.flashMessenger();
                        data = $.parseJSON(data);

                        if(data.success){
                            melisHelper.melisOkNotification(data.title, data.message);
                            //refresh table
                            $("#symfonyTplTable").DataTable().ajax.reload();
                        }else{
                            melisHelper.melisKoNotification(data.title, data.message);
                        }
                    }
                });
            }
        );
    });

    /**
     * Save
     * @param url
     */
    function save(url){
        var form_data = new FormData($("#symfonytpl_prop_form")[0]);

        /**
         * If modal has language tab, we get all
         * of it's data
         */
        $("form.symfonytpl_lang_form").each(function(){
            var langId = $(this).data("lang-id");
            $.each($(this).serializeArray(), function(){
                if (!$(this).prop('disabled')){
                    form_data.append('language['+langId+']['+this.name+']', this.value);
                }
            });

            var formFiles = $(this).find("[type='file']");
            $.each(formFiles, function(){
                if (!$(this).prop('disabled')){
                    if (typeof $(this)[0].files[0] !== "undefined"){
                        form_data.append('language['+langId+']['+$(this).attr("name")+']', $(this)[0].files[0]);
                    }
                }
            });
        });

        $.ajax({
            url: url,
            data: form_data,
            method: "POST",
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function(){
                //disable button
                $("#btn-save-calendar").attr("disabled", true);
                //disable all fields
                $("#symfonytpl_prop_form :input").prop("disabled", true);
            },
            success: function(data){
                // update flash messenger values
                melisCore.flashMessenger();

                data = $.parseJSON(data);
                if(data.success) {
                    melisHelper.melisOkNotification(data.title, data.message);
                    //refresh table
                    $("#symfonyTplTable").DataTable().ajax.reload();
                    //assign null to selectedId id after saving/updating record
                    selectedId = null;
                }else{
                    melisHelper.melisKoNotification(data.title, data.message, data.errors);
                    melisCoreTool.highlightErrors(0, data.errors, "symfonytpl_prop_form");
                }
                //enable save button
                $("#btn-save-calendar").attr("disabled", false);
                //enable form fields
                $("#symfonytpl_prop_form :input").prop("disabled", false);
            }
        });
    }
});